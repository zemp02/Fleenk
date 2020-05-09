<?php

namespace App\Model;

use Nette;
use Nette\Mail\Message;

/**
 * Class ExcelManager
 * Model for working with inserted excel spreadsheet
 * @author Petr Zeman
 * @package App\Model
 */
final class FormManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Context */
    private $database;

    /** @var array */
    private $mailer;

    /**
     * FormManager constructor.
     * @param Nette\Database\Context $database
     */
    public function __construct($mailer, Nette\Database\Context $database)
    {
        $this->database = $database;
        $this->mailer = $mailer;
    }

    /** Gets list of existing user roles.
     * @return array - array of user roles.
     */
    public function getRoles()
    {
        $roleList = [];
        $roles = $this->database->table('userRole')->select('name')->fetchAll();
        foreach ($roles as $role) {
            array_push($roleList, $role->name);
        }
        return $roleList;
    }

    /** Sets technician as a member of a team.
     * @param $teamId - Id of team to have teachnician assigned.
     * @param $userEmail - Email of technician to add.
     * @throws AssignedTechnicianException
     * @throws IncorrectRoleException
     * @throws MissingUserException
     */
    public function setMember($teamId, $userEmail)
    {
        $user = $this->database->table('user')->where('email=?', $userEmail)->fetch();
        /** Checks if user exists */
        if ($user == null) {
            throw new MissingUserException;
        }

        /** Checks if user is technician */
        if ($user->fk_Technician_Id == null) {
            throw new IncorrectRoleException;
        }

        /** Checks if user is not already in team */
        if ($user->technician->team_Id != null) {
            throw new AssignedTechnicianException;
        }

        $this->database->table('technician')->where('id=?', $user->technician->id)->update((['fk_Team_Id' => $teamId]));

    }

    /** Assigns branch to a team.
     * @param $teamId - Id of team to have branch assigned.
     * @param $branchCode - Code of a branch to be assigned
     * @throws AssignedBranchException
     * @throws MissingBranchException
     */
    public function setBranch($teamId, $branchCode)
    {
        $branch = $this->database->table('clientOffice')->where('id=?', $branchCode)->fetch();
        /** checks if branch exists */
        if ($branch == null) {
            throw new MissingBranchException;
        }

        /** checks if branch is not already assigned to different team*/
        if ($branch->fk_Team_Id != null) {
            throw new AssignedBranchException;
        }

        $this->database->table('clientOffice')->where('id=?', $branchCode)->update((['fk_Team_Id' => $teamId]));

    }

    /** Sets a new state to a branch.
     * @param $code - Code of a branch to have state changed.
     * @param $state - State to set to a branch.
     * @param $userId - Id of user changing state.
     * @return mixed|Nette\Database\Table\ActiveRow
     */
    public function setState($code, $state,$userId)
    {
        $stateId = $this->database->table('officeState')
            ->where('name = ?', $state)
            ->fetch();
        $this->database->table('clientOffice')
            ->where('id = ?', $code)
            ->update(['fk_officeState_id' => $stateId->id]);
        $this->noticeRelated($code,$state);
        $this->archiveChange($code,$state,$userId);

        $newerState = $this->database->table('officeState')
            ->where('id=?', $stateId->id + 1)
            ->fetch();
        if ($newerState == null) {
            $newerState = $stateId;
        }
        return $newerState->name;
    }

    /** Informs related users upon change of state of location.
     * @param $code - Code of branch that got changed.
     * @param $state - State that branch got changed into.
     */
    private function noticeRelated($code, $state){
        $smtp = new Nette\Mail\SmtpMailer($this->mailer);
        $clientOffice = $this->database->table('clientOffice')->where('id=?',$code)->fetch();
        $technicians = $this->database->table('technician')
            ->where('fk_Team_Id=?',$clientOffice->fk_Team_Id)->fetchAll();
        $mail = new Message;
        $mail->setFrom('Fleenk <Fleenk@smtp.mailtrap.io>')
            ->setSubject('Location '.$code.' changed.')
            ->setBody("Hello, Your assigned branch number: ".$code .
                ' had changed state.\r\n It\'s state is currently: '.$state);
        foreach ($technicians as $technician){
            $user = $this->database->table('user')->where('fk_Technician_Id=?',$technician->id)->fetch();
            $mail->addTo($user->email);
        }
        $client = $this->database->table('user')
            ->where('fk_ClientSupervisor_Id=?',$clientOffice->fk_ClientSupervisor_Id)->fetch();
        $mail->addTo($client->email);
        try {
            $smtp->send($mail);
        }catch(\Exception $e){

        }
    }

    /** Function that archives any change of state that happened.
     * @param $branchCode - Code of branch that got changed
     * @param $state - State that branch got changed to
     * @param $id - Id of user that changed state.
     * @throws \Exception
     */
    private function archiveChange($branchCode, $state, $id){
        $time = new Nette\Utils\DateTime("now");
        $state = $this->database->table('officeState')->where('name=?',$state)->fetch();
        $this->database->table('changes')->insert([
           'time'=> $time,
            'fk_ClientOffice_Id' => $branchCode,
            'officeState_id' => $state->id,
            'user_id'=>$id

        ]);

    }

    /** Creates a new team of technicians.
     * @param $teamName - Name of a new team.
     * @param $teamLeaderEmail - Email of technician that will lead the new team.
     * @throws DuplicateTeamException
     * @throws IncorrectRoleException
     * @throws MissingUserException
     */
    public function createTeam($teamName, $teamLeaderEmail)
    {
        $teamCheck = $this->database->table('team')->where('name=?', $teamName)->fetchAll();
        if ($teamCheck) {
            throw new DuplicateTeamException;
        }

        $teamLeaderCheck = $this->database->table('user')->where('email=?', $teamLeaderEmail)->fetch();
        if ($teamLeaderCheck == null) {
            throw new MissingUserException;
        }

        if ($teamLeaderCheck->fk_Technician_Id == null) {
            throw new IncorrectRoleException;
        }

        $this->database->table('team')->insert([
            'name' => $teamName,
            'fk_TeamLeaderTechnician_Id' => $teamLeaderCheck->fk_Technician_Id
        ]);
    }

    /** Removes technician from a team.
     * @param $teamId - Id of team to have technician removed
     * @param $id - Id of technician to be removed from team.
     * @return bool
     */
    public function removeMember($teamId, $id)
    {
        $user = $this->database->table('user')->where('id=?', $id)->fetch();
        $team = $this->database->table('team')->where('id=?', $teamId)->fetch();

        /** checks if user exists */
        if ($user == null) {
            return false;
        }

        /** checks if user is technician */
        if ($user->fk_Technician_Id == null) {
            return false;
        }

        /** Checks if user is in defined team. */
        if ($user->technician->Fk_Team_Id != $teamId) {
            return false;
        }

        /** checks if team exists */
        if ($team == null) {
            return false;
        }
        $this->database->table('technician')->where('id=?', $user->technician->id)->update((['fk_Team_Id' => null]));
        return true;
    }

    /** Removes branch from team.
     * @param $id - Code of branch to have team unassigned.
     * @return bool
     */
    public function removeBranch($id)
    {
        $branch = $this->database->table('clientOffice')->where('id=?', $id)->fetch();

        /** checks if branch exists */
        if ($branch == null) {
            return false;
        }

        $this->database->table('clientOffice')->where('id=?', $id)->update((['fk_Team_Id' => null]));
        return true;
    }


}


/**
 * Class DuplicateTeamException - Exception warning about duplicate name (team)
 * @package App\Model
 */
class DuplicateTeamException extends \Exception
{
}

/**
 * Class MissingTechnicianException - Exception warning about nonexistence of inserted technician
 * that should act as leader.
 * @package App\Model
 */
class MissingUserException extends \Exception
{
}

/**
 * Class MissingBranchException - Exception warning about nonexistence of inserted branch
 * that should act as leader.
 * @package App\Model
 */
class MissingBranchException extends \Exception
{
}

/**
 * Class AssignedTechnicianException - Exception warning about technician being already assigned to team.
 * that should act as leader.
 * @package App\Model
 */
class AssignedTechnicianException extends \Exception
{
}

/**
 * Class AssignedBranchException - Exception warning about branch being already assigned to different team.
 * that should act as leader.
 * @package App\Model
 */
class AssignedBranchException extends \Exception
{
}

/**
 * Class IncorrectRoleException - Exception warning about incorrect role of requested user.
 * @package App\Model
 */
class IncorrectRoleException extends \Exception
{
}