<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;

/**
 * Class GridManager
 * Model for providing presenters with data for Bootstrap-tables.
 * @author Petr Zeman
 * @package App\Model
 * @version Summer 2020
 */
final class GridManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Context */
    private $database;

    /** @var Passwords */
    private $passwords;

    /** @var Nette\Application\LinkGenerator */
    private $linkGenerator;

    /**
     * GridManager constructor.
     * @param Nette\Database\Context $database
     * @param Passwords $passwords
     */
    public function __construct(Nette\Database\Context $database, Passwords $passwords,
                                Nette\Application\LinkGenerator $linkGenerator)
    {
        $this->database = $database;
        $this->passwords = $passwords;
        $this->linkGenerator = $linkGenerator;
    }

    /** Provides presenter with data for the Branch table.
     * @param $role - Role of logged in user.
     * @param $id - Id of logged in user.
     * @return array - array of table data encoded in JSON.
     */
    public function getBranchData($role, $id)
    {

        $rows = [];
        if ($role == 'Klient') {
            $user = $this->database->table('user')->where('id=?', $id)->fetch();
            $branchOffice = $this->database->table('clientOffice')
                ->where('fk_ClientSupervisor_Id=?', $user->fk_ClientSupervisor_Id)
                ->fetchAll();


            foreach ($branchOffice as $branch) {

                $newState = $this->database->table('officeState')->where('id=?', $branch->officeState->id + 1)->fetch();
                if ($newState == null) {
                    $newState = $branch->officeState;
                }

                $officeContact = $this->database->table('officeContact')
                    ->where('fk_ClientOffice_Id = ?', $branch->id)->fetch();
                array_push($rows, json_encode([
                    'BranchCode' => $branch->id,
                    'BranchAddress' => $branch->address->streetName . ' ' . $branch->address->streetNumber,
                    'BranchLeader' => $officeContact->firstName . ' ' . $officeContact->lastName,
                    'BranchLeaderPhone' => $officeContact->phone,
                    'State' => $branch->officeState->name,
                    'button' => '<button data-parent="' . $branch->id . '" data-new-state="' . $newState->name . '"
 class="btn btn-primary btn-change-state">Change State</button>'
                ]));

            }

            return $rows;

        } else if ($role == 'Technik') {
            $user = $this->database->table('user')->where('id=?', $id)->fetch();
            if ($user->technician->fk_Team_Id) {
                $branchOffice = $this->database->table('clientOffice')
                    ->where('fk_Team_Id=?', $user->technician->fk_Team_Id)
                    ->fetchAll();


                foreach ($branchOffice as $branch) {
                    $supervisor = $this->database->table('user')
                        ->where('fk_ClientSupervisor_ID = ?', $branch->clientSupervisor->id)->fetch();
                    $officeContact = $this->database->table('officeContact')
                        ->where('fk_ClientOffice_Id = ?', $branch->id)->fetch();

                    $newState = $this->database->table('officeState')->where('id=?', $branch->officeState->id + 1)->fetch();
                    if ($newState == null) {
                        $newState = $branch->officeState;
                    }


                    array_push($rows, json_encode([
                        'BranchCode' => $branch->id,
                        'Supervisor' => $supervisor->clientSupervisor->locationDescription,
                        'BranchAddress' => $branch->address->streetName . ' ' . $branch->address->streetNumber,
                        'BranchLeader' => $officeContact->firstName . ' ' . $officeContact->lastName,
                        'BranchLeaderPhone' => $officeContact->phone,
                        'State' => $branch->officeState->name,

                        'JanitorPhone' => $branch->address->janitor->phone,
                        'BranchDescription' => $branch->description,
                        'BranchNote' => $branch->note,

                        'button' => '<button data-parent="' . $branch->id . '" data-new-state="' . $newState->name . '" class="btn btn-primary btn-change-state">Change State</button>'
                    ]));

                }
            }
            return $rows;
        } else if ($role == 'Admin') {
            $branchOffice = $this->database->table('clientOffice')->fetchAll();
            foreach ($branchOffice as $branch) {
                $supervisor = $this->database->table('user')
                    ->where('fk_ClientSupervisor_ID = ?', $branch->clientSupervisor->id)->fetch();
                $officeContact = $this->database->table('officeContact')
                    ->where('fk_ClientOffice_Id = ?', $branch->id)->fetch();

                $states = $this->database->table('officeState')->order('id DESC')->fetchAll();
                $selectButton = '<div class="form-group">
  <label for="roleList">Select list:</label>
  <select class="form-control opt-select-state" id="roleList">';
                foreach ($states as $state) {
                    $branchStateId =$branch->fk_OfficeState_Id->id + 1;
                    $stateId =$state->id;
                    if( $branchStateId == $stateId ){
                        $select = 'selected';
                    }else{
                        $select = '';
                    }
                    $option = '<option ' . $select. ' >' . $state->name . '</option >';
                    $selectButton .= $option;

                }
                $selectButton .= '</select></div>';
                if ($branch->team == null) {
                    $teamName = null;
                    $teamLeaderPhone = null;
                } else {
                    $teamName = $branch->team->name;
                    $teamLeaderPhone = $branch->team->technician->phone;
                }

                array_push($rows, json_encode([
                    'BranchCode' => $branch->id,
                    'Supervisor' => $supervisor->clientSupervisor->locationDescription,
                    'BranchAddress' => $branch->address->streetName . ' ' . $branch->address->streetNumber,
                    'BranchLeader' => $officeContact->firstName . ' ' . $officeContact->lastName,
                    'BranchLeaderPhone' => $officeContact->phone,
                    'State' => $branch->officeState->name,

                    'JanitorPhone' => $branch->address->janitor->phone,
                    'BranchDescription' => $branch->description,
                    'BranchNote' => $branch->note,
                    'TeamName' => $teamName,
                    'TeamLeaderPhone' => $teamLeaderPhone,
                    'select' => $selectButton,

                    'button' => '<button data-parent="' . $branch->id . '" class="btn btn-primary btn-change-state">Change State</button>'
                ]));

            }
            return $rows;
        }


    }

    /** Provides presenter with data for the Teams table.
     * @return array array of table data encoded in JSON.
     */
    public function getTeamsData()
    {
        $rows = [];

        $teams = $this->database->table('team')->fetchAll();
        foreach ($teams as $team) {
            $assignedBranches = [];
            $branchesSubrow = [];
            $membersSubrow = [];
            $assignedBranches = $this->database->table('clientOffice')->where('fk_Team_Id = ?', $team->id)->fetchAll();
            foreach ($assignedBranches as $assignedBranch) {
                $officeContact = $this->database->table('officeContact')
                    ->where('fk_ClientOffice_Id = ?', $assignedBranch->id)->fetch();
                array_push($branchesSubrow, json_encode([
                    'BranchCode' => $assignedBranch->id,
                    'BranchAddress' => $assignedBranch->address->streetName . ' ' . $assignedBranch->address->streetNumber,
                    'BranchDescription' => $assignedBranch->description,
                    'BranchLeader' => $officeContact->firstName . ' ' . $officeContact->lastName,
                    'BranchLeaderPhone' => $officeContact->phone,
                    'State' => $assignedBranch->officeState->name,

                ]));
            }

            $assignedTechnicians = $this->database->table('technician')->where('fk_Team_Id = ?', $team->id)->fetchAll();
            foreach ($assignedTechnicians as $assignedTechnician) {
                $user = $this->database->table('user')
                    ->where('fk_Technician_Id = ?', $assignedTechnician->id)->fetch();
                if ($assignedTechnician) {
                    array_push($membersSubrow, json_encode([
                        'MemberFirstName' => $user->firstName,
                        'MemberLastName' => $user->lastName,
                        'MemberEmail' => $user->email,
                        'MemberPhone' => $assignedTechnician->phone
                    ]));
                }
            }

            $teamLeader = $this->database->table('user')
                ->where('fk_Technician_Id = ?', $team->fk_TeamLeaderTechnician_Id)
                ->fetch();
            array_push($rows, json_encode([
                'TeamName' => $team->name,
                'Branches' => '<button data-parent="' . $team->id . '" class="btn btn-secondary btn-branches">Branches</button>',
                'BranchesSubrow' => $branchesSubrow,
                'LeaderName' => $teamLeader->firstName . ' ' . $teamLeader->lastName,
                'LeaderPhone' => $teamLeader->technician->phone,
                'Members' => '<button data-parent="' . $team->id . '" class="btn btn-secondary btn-members">Members</button>',
                'MembersSubrow' => $membersSubrow,

                'teamPage' => '<a class="btn btn-primary btn-team-page" href="' . $this->linkGenerator->link('TeamPage:default', [$team->id]) . '">Team Page</a>'
            ]));
        }
        return $rows;
    }

    /** Provides presenter with data for the Technician table on User page.
     * @return array array of table data encoded in JSON.
     */
    public function getTechnicianData()
    {
        $rows = [];
        $technicians = $this->database->table('user')->where(' NOT (fk_Technician_Id ?)', null)->fetchAll();
        foreach ($technicians as $technician) {

            array_push($rows, json_encode([
                'technicianFirstName' => $technician->firstName,
                'technicianLastName' => $technician->lastName,
                'technicianEmail' => $technician->email,
                'technicianPhone' => $technician->technician->phone,

                'password' => '<button data-id=' . $technician->id . ' class="btn btn-primary m-2 btn-change-password">Password</button>',
                'remove' => '<button data-id=' . $technician->id . ' class="btn btn-primary m-2 btn-remove-technician">Remove</button>',
            ]));
        }
        return $rows;
    }

    /** Provides presenter with data for the Client table on User page.
     * @return array array of table data encoded in JSON.
     */
    public function getClientData()
    {
        $rows = [];
        $clients = $this->database->table('user')->where(' NOT (fk_ClientSupervisor_Id ?)', null)->fetchAll();
        foreach ($clients as $client) {

            array_push($rows, json_encode([
                'clientFirstName' => $client->firstName,
                'clientLastName' => $client->lastName,
                'clientDescription' => $client->clientSupervisor->locationDescription,
                'clientEmail' => $client->email,
                'clientPhone' => $client->clientSupervisor->phone,


                'password' => '<button data-id=' . $client->id . ' class="btn btn-primary btn-change-password">Password</button>',
            ]));
        }
        return $rows;
    }

    /** Provides presenter with data for the Member table on Teampage.
     * @param $id - Id of chosen team.
     * @return array array of table data encoded in JSON.
     */
    public function getTeamPageMemberData($id)
    {
        $rows = [];
        $team = $this->database->table('team')
            ->where('id=?', $id)
            ->fetch();


        $teamLeader = $this->database->table('user')
            ->where('fk_Technician_Id = ?', $team->fk_TeamLeaderTechnician_Id)
            ->fetch();
        $rows[0] = [
            'TeamName' => $team->name,
            'TeamLeader' => $teamLeader->firstName . ' ' . $teamLeader->lastName,
            'TeamLeaderPhone' => $teamLeader->technician->phone
        ];

        $members = $this->database->table('technician')->where('fk_Team_Id=?', $id)->fetchAll();
        foreach ($members as $member) {

            $user = $this->database->table('user')
                ->where('fk_Technician_Id = ?', $member->id)->fetch();

            array_push($rows, json_encode([
                'MemberEmail' => $user->email,
                'MemberName' => $user->firstName . ' ' . $user->lastName,
                'MemberPhone' => $user->technician->phone,

                'Remove' => '<button class="btn btn-primary btn-remove-member" data-id ="' . $user->id . '">Remove User</button>'
            ]));
        }
        return $rows;
    }

    /** Provides presenter with data for the Branch table on Teampage.
     * @param $id - Id of chosen team.
     * @return array array of table data encoded in JSON.
     */
    public function getTeamPageBranchData($id)
    {
        $rows = [];
        $team = $this->database->table('team')
            ->where('id=?', $id)
            ->fetch();


        $assignedBranches = $this->database->table('clientOffice')->where('fk_Team_Id = ?', $team->id)->fetchAll();
        foreach ($assignedBranches as $assignedBranch) {
            $officeContact = $this->database->table('officeContact')
                ->where('fk_ClientOffice_Id = ?', $assignedBranch->id)->fetch();
            array_push($rows, json_encode([
                'BranchCode' => $assignedBranch->id,
                'BranchAddress' => $assignedBranch->address->streetName . ' ' . $assignedBranch->address->streetNumber,
                'BranchDescription' => $assignedBranch->description,
                'BranchLeader' => $officeContact->firstName . ' ' . $officeContact->lastName,
                'BranchLeaderPhone' => $officeContact->phone,
                'State' => $assignedBranch->officeState->name,

                'Remove' => '<button class="btn btn-primary btn-remove-branch" data-id="' . $assignedBranch->id . '">Unassign</button>'

            ]));
        }


        return $rows;
    }

}