<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Class ExcelManager
 * Model for working with inserted excel spreadsheet
 * @author Petr Zeman
 * @package App\Model
 */
final class ExcelManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Context */
    private $database;

    /** @var Passwords */
    private $passwords;

    /** @var UserManager */
    private $userManager;


    public function __construct(Nette\Database\Context $database, Passwords $passwords, UserManager $userManager)
    {
        $this->database = $database;
        $this->passwords = $passwords;
        $this->userManager = $userManager;
    }


    /**
     * Function that receives spreadsheet as file from form and inserts data into database.
     * @param $file
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function importExcel($file)
    {
        $spreadsheet = IOFactory::load($file);

        $this->clearDatabase();
        $this->database->table('user')->insert([
            'id'=>1,
            'firstName' => 'Admin',
            'lastName' => 'Admin',
            'email' => 'Adm@adm.cz',
            'password' => $this->passwords->hash('Admin'),
            'tempPassword' => false,
            'fk_UserRole_Id' => 1,
        ]);

        $startCoord = 2;
        $rowValue = $this->createTechnicianArray($spreadsheet, $startCoord);

        while (!is_null($rowValue['email'])) {
            $startCoord++;
            $this->insertTechnician($rowValue);
            $rowValue = $this->createTechnicianArray($spreadsheet, $startCoord);

        }

        $startCoord = 2;
        $rowValue = $this->createTeamArray($spreadsheet, $startCoord);
        while (!is_null($rowValue['name'])) {
            $startCoord++;
            $this->insertTeam($rowValue);
            $rowValue = $this->createTeamArray($spreadsheet, $startCoord);
        }

        $startCoord = 2;
        $rowValue = $this->createClientArray($spreadsheet, $startCoord);
        while (!is_null($rowValue['email'])) {
            $startCoord++;
            $this->insertClient($rowValue);
            $rowValue = $this->createClientArray($spreadsheet, $startCoord);
        }

        $startCoord = 2;
        $rowValue = $this->createLocationArray($spreadsheet, $startCoord);
        while (!is_null($rowValue['id'])) {
            $startCoord++;
            $this->insertLocation($rowValue);
            $rowValue = $this->createLocationArray($spreadsheet, $startCoord);
        }

        return $rowValue;
    }

    /** Exports data from database into excel.
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportExcel()
    {
        $spreadsheet = IOFactory::load('assets/Template.xlsx');
        $spreadsheet = $this->createTechnicianSheet($spreadsheet);
        $spreadsheet = $this->createClientSheet($spreadsheet);
        $spreadsheet = $this->createTeamSheet($spreadsheet);
        $spreadsheet = $this->createLocationSheet($spreadsheet);
        $spreadsheet = $this->createChangeSheet($spreadsheet);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="output.xls"');
        $writer->save("php://output");

    }

    /** Inserts data into Technician sheet of spreadsheet
     * @param Spreadsheet $spreadsheet - spreadsheet to insert data into
     * @return Spreadsheet - spreadsheet with inserted data
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function createTechnicianSheet(Spreadsheet $spreadsheet)
    {
        $technicians = $this->database->table('user')->where('NOT (fk_Technician_Id ?)', null)->fetchAll();
        $row = 2;
        foreach ($technicians as $technician) {
            $technicianSheet = $spreadsheet->getSheetByName('Technici');
            $technicianSheet->getCell('A' . $row)->setValue($technician->firstName);
            $technicianSheet->getCell('B' . $row)->setValue($technician->lastName);
            $technicianSheet->getCell('C' . $row)->setValue($technician->email);
            $technicianSheet->getCell('D' . $row)->setValue($technician->technician->phone);
            $row++;
        }
        return $spreadsheet;

    }

    /** Inserts data into Client sheet of spreadsheet
     * @param Spreadsheet $spreadsheet - spreadsheet to insert data into
     * @return Spreadsheet - spreadsheet with inserted data
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function createClientSheet(Spreadsheet $spreadsheet)
    {
        $clients = $this->database->table('user')->where('NOT (fk_ClientSupervisor_Id ?)', null)->fetchAll();
        $row = 2;
        foreach ($clients as $client) {
            $technicianSheet = $spreadsheet->getSheetByName('Klienti');
            $technicianSheet->getCell('A' . $row)->setValue($client->firstName);
            $technicianSheet->getCell('B' . $row)->setValue($client->lastName);
            $technicianSheet->getCell('C' . $row)->setValue($client->email);
            $technicianSheet->getCell('D' . $row)->setValue($client->clientSupervisor->locationDescription);
            $technicianSheet->getCell('E' . $row)->setValue($client->clientSupervisor->phone);
            $row++;
        }
        return $spreadsheet;

    }

    /** Inserts data into Team sheet of spreadsheet
     * @param Spreadsheet $spreadsheet - spreadsheet to insert data into
     * @return Spreadsheet - spreadsheet with inserted data
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function createTeamSheet(Spreadsheet $spreadsheet)
    {
        $teams = $this->database->table('team')->fetchAll();
        $row = 2;
        foreach ($teams as $team) {
            $technicianSheet = $spreadsheet->getSheetByName('Týmy');
            $teamLeader = $this->database->table('user')->where('fk_Technician_Id=?', $team->technician->id)->fetch();

            $teamMembers = $this->database->table('technician')->where('fk_Team_Id=?', $team->id)->fetchAll();
            foreach ($teamMembers as $teamMember) {
                $teamMemberUser = $this->database->table('user')->where('fk_Technician_Id=?', $teamMember->id)->fetch();
                $technicianSheet->getCell('A' . $row)->setValue($team->name);
                $technicianSheet->getCell('B' . $row)->setValue($teamLeader->email);
                $technicianSheet->getCell('C' . $row)->setValue($teamMemberUser->email);
                $row++;
            }
        }
        return $spreadsheet;

    }

    /** Inserts data into Location sheet of spreadsheet
     * @param Spreadsheet $spreadsheet - spreadsheet to insert data into
     * @return Spreadsheet - spreadsheet with inserted data
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function createLocationSheet(Spreadsheet $spreadsheet)
    {
        $locationSheet = $spreadsheet->getSheetByName('Lokace');
        $clientOffices = $this->database->table('clientOffice')->fetchAll();
        $row = 2;
        foreach ($clientOffices as $clientOffice) {
            $officeContact = $this->database->table('officeContact')
                ->where('fk_ClientOffice_Id=?',$clientOffice->id)
                ->fetch();
            $locationSheet->getCell('A' . $row)->setValue($clientOffice->id);
            $locationSheet->getCell('B' . $row)->setValue($clientOffice->team->name);
            $locationSheet->getCell('C' . $row)->setValue($clientOffice->clientSupervisor->locationDescription);
            $locationSheet->getCell('D' . $row)->setValue($clientOffice->description);
            $locationSheet->getCell('E' . $row)->setValue($clientOffice->officeState->name);
            $locationSheet->getCell('F' . $row)->setValue($clientOffice->note);
            $locationSheet->getCell('G' . $row)->setValue($officeContact->firstName);
            $locationSheet->getCell('H' . $row)->setValue($officeContact->lastName);
            $locationSheet->getCell('I' . $row)->setValue($officeContact->phone);
            $locationSheet->getCell('J' . $row)->setValue($clientOffice->address->streetName);
            $locationSheet->getCell('K' . $row)->setValue($clientOffice->address->streetNumber);
            $locationSheet->getCell('L' . $row)->setValue($clientOffice->address->janitor->phone);
            $row++;

        }
        return $spreadsheet;

    }

    /** Inserts data into changes sheet of spreadsheet
     * @param Spreadsheet $spreadsheet - spreadsheet to insert data into
     * @return Spreadsheet - spreadsheet with inserted data
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function createChangeSheet(Spreadsheet $spreadsheet)
    {
        $changes = $this->database->table('changes')->fetchAll();
        $row = 2;
        foreach ($changes as $change) {
            $technicianSheet = $spreadsheet->getSheetByName('Změny');
            $technicianSheet->getCell('A' . $row)->setValue($change->officeState->name);
            $technicianSheet->getCell('B' . $row)->setValue($change->time);
            $technicianSheet->getCell('C' . $row)->setValue($change->user->email);
            $technicianSheet->getCell('D' . $row)->setValue($change->fk_ClientOffice_Id);
            $row++;
        }
        return $spreadsheet;

    }

    /**
     * Creates Array from inserted excel - Sheet Technici
     * @param Spreadsheet $spreadsheet Inserted excel with data
     * @param int $startCoord row that should be read.
     * @return array - array of data read from requested row
     */
    private function createTechnicianArray(Spreadsheet $spreadsheet, int $startCoord)
    {
        $userSheet = $spreadsheet->getSheetByName('Technici');
        $rowValue = array(
            "firstName" => ($userSheet->getCellByColumnAndRow(1, $startCoord))->getValue(),
            "lastName" => ($userSheet->getCellByColumnAndRow(2, $startCoord))->getValue(),
            "email" => ($userSheet->getCellByColumnAndRow(3, $startCoord))->getValue(),
            "phone" => $userSheet->getCellByColumnAndRow(4, $startCoord)->getValue(),
            "userRole" => 2
        );
        return $rowValue;
    }

    /**
     * Creates Array from inserted excel - Sheet Týmy
     * @param Spreadsheet $spreadsheet Inserted excel with data
     * @param int $startCoord row that should be read.
     * @return array - array of data read from requested row
     */
    private function createTeamArray(Spreadsheet $spreadsheet, int $startCoord)
    {
        $teamSheet = $spreadsheet->getSheetByName('Týmy');
        $rowValue = array(
            "name" => ($teamSheet->getCellByColumnAndRow(1, $startCoord))->getValue(),
            "teamLeaderEmail" => ($teamSheet->getCellByColumnAndRow(2, $startCoord))->getValue(),
            "technicianEmail" => ($teamSheet->getCellByColumnAndRow(3, $startCoord))->getValue()
        );

        return $rowValue;
    }

    /**
     * Creates Array from inserted excel - Sheet Klienti
     * @param Spreadsheet $spreadsheet Inserted excel with data
     * @param int $startCoord row that should be read.
     * @return array - array of data read from requested row
     */
    private function createClientArray(Spreadsheet $spreadsheet, int $startCoord)
    {
        $clientSheet = $spreadsheet->getSheetByName('Klienti');
        $rowValue = array(
            "firstName" => ($clientSheet->getCellByColumnAndRow(1, $startCoord))->getValue(),
            "lastName" => ($clientSheet->getCellByColumnAndRow(2, $startCoord))->getValue(),
            "email" => ($clientSheet->getCellByColumnAndRow(3, $startCoord))->getValue(),
            "description" => $clientSheet->getCellByColumnAndRow(4, $startCoord)->getValue(),
            "phone" => $clientSheet->getCellByColumnAndRow(5, $startCoord)->getValue(),
            "userRole" => 3
        );

        return $rowValue;
    }

    /**
     * Creates Array from inserted excel - Sheet Lokace
     * @param Spreadsheet $spreadsheet Inserted excel with data
     * @param int $startCoord row that should be read.
     * @return array - array of data read from requested row
     */
    private function createLocationArray(Spreadsheet $spreadsheet, int $startCoord)
    {
        $teamSheet = $spreadsheet->getSheetByName('Lokace');
        $rowValue = array(
            "id" => ($teamSheet->getCellByColumnAndRow(1, $startCoord))->getValue(),
            "teamName" => ($teamSheet->getCellByColumnAndRow(2, $startCoord))->getValue(),
            "supervisorDescription" => ($teamSheet->getCellByColumnAndRow(3, $startCoord))->getValue(),
            "description" => ($teamSheet->getCellByColumnAndRow(4, $startCoord))->getValue(),
            "state" => ($teamSheet->getCellByColumnAndRow(5, $startCoord))->getValue(),
            "note" => ($teamSheet->getCellByColumnAndRow(6, $startCoord))->getValue(),
            "contactFirstName" => ($teamSheet->getCellByColumnAndRow(7, $startCoord))->getValue(),
            "contactLastName" => ($teamSheet->getCellByColumnAndRow(8, $startCoord))->getValue(),
            "contactPhone" => ($teamSheet->getCellByColumnAndRow(9, $startCoord))->getValue(),
            "streetName" => ($teamSheet->getCellByColumnAndRow(10, $startCoord))->getValue(),
            "streetNumber" => ($teamSheet->getCellByColumnAndRow(11, $startCoord))->getValue(),
            "janitorPhone" => ($teamSheet->getCellByColumnAndRow(12, $startCoord))->getValue()
        );

        return $rowValue;
    }

    /**
     * Function that goes through excel sheet and inserts data from array created by @function{createClientArray}
     * into database
     * @param Spreadsheet $spreadsheet - Received excel sheet with data to input
     */
    private function insertLocation(array $rowValue)
    {
        /**
         * Janitor check/insert
         */
        $janitorCheck = $this->database->table('Janitor')
            ->select('id')
            ->where('phone = ?', $rowValue['janitorPhone'])
            ->fetch();

        if ($janitorCheck['id'] == null) {

            $log = $this->database->table('janitor')->insert([
                'phone' => $rowValue['janitorPhone'],
            ]);
            $janitorId = $log->id;
        } else {
            $janitorId = $janitorCheck['id'];
        }


        /**
         * address check/insert
         */
        $addressCheck = $this->database->table('address')
            ->select('id')
            ->where('streetName = ?', $rowValue['streetName'])
            ->where('streetNumber = ?', $rowValue['streetNumber'])
            ->fetch();

        if ($addressCheck['id'] == null) {
            $log = $this->database->table('address')->insert([
                'streetName' => $rowValue['streetName'],
                'streetNumber' => $rowValue['streetNumber'],
                'Janitor_Id' => $janitorId
            ]);
            $addressId = $log->id;
        } else {
            $addressId = $addressCheck['id'];
        }

        /**
         * getting required id's for client office and inserting it into database
         */
        $teamCheck = $this->database->table('team')
            ->select('id')
            ->where('name = ?', $rowValue['teamName'])
            ->fetch();
        $teamId = $teamCheck['id'];
        $supervisorCheck = $this->database->table('clientSupervisor')
            ->select('id')
            ->where('locationDescription = ?', $rowValue['supervisorDescription'])
            ->fetch();
        $supervisorId = $supervisorCheck['id'];
        $stateCheck = $this->database->table('officeState')
            ->select('id')
            ->where('name = ?', $rowValue['state'])
            ->fetch();
        $stateID = $stateCheck->id;

        $log = $this->database->table('clientOffice')->insert([
            'id' => $rowValue['id'],
            'description' => $rowValue['description'],
            'note' => $rowValue['note'],
            'fk_Team_Id' => $teamId,
            'fk_ClientSupervisor_Id' => $supervisorId,
            'fk_Address_Id' => $addressId,
            'fk_OfficeState_Id' => $stateID
        ]);
        $clientOfficeId = $log->id;

        /**
         * Inserts a contact for this client office
         */
        $this->database->table('officeContact')
            ->insert([
                'firstName' => $rowValue['contactFirstName'],
                'lastName' => $rowValue['contactLastName'],
                'phone' => $rowValue['contactPhone'],
                'fk_clientOffice_Id' => $clientOfficeId

            ]);

    }

    /**
     * Function that goes through excel sheet and inserts data from array created by @function{createTechnicianArray}
     * into database
     * @param Spreadsheet $spreadsheet - Received excel sheet with data to input
     */
    private function insertTechnician(array $rowValue)
    {
        $log = $this->database->table('technician')->insert([
            'phone' => $rowValue['phone']
        ]);

        Nette\Utils\Validators::assert($rowValue['email'], 'email');
        $this->database->table('user')->insert([
            'firstName' => $rowValue['firstName'],
            'lastName' => $rowValue['lastName'],
            'email' => $rowValue['email'],
            'password' => $this->userManager->generateNewPassword($rowValue['email']),
            'tempPassword' => true,
            'fk_UserRole_Id' => $rowValue['userRole'],
            'fk_Technician_Id' => $log->id
        ]);
    }

    /**
     * Function that goes through excel sheet and inserts data from array created by @function{createTeamArray}
     * into database
     * @param Spreadsheet $spreadsheet - Received excel sheet with data to input
     */
    private function insertTeam(array $rowValue)
    {

        /**
         * Checks for existence of team by checking database for its name. If it doesn't exist the teams get created.
         */
        $teamId = $this->database->table('team')->select('id')->where('name = ?', $rowValue['name'])->fetch();
        if ($teamId['id'] == null) {
            $teamLeaderId = $this->database->table('user')->select('fk_Technician_Id')->where('email = ?', $rowValue['teamLeaderEmail'])->fetch();
            $log = $this->database->table('team')->insert([
                'name' => $rowValue['name'],
                'fk_TeamLeaderTechnician_Id' => $teamLeaderId['fk_Technician_Id']
            ]);
            $teamId['id'] = $log->id;
            $this->database->table('technician')
                ->where('id = ?', $teamLeaderId['fk_Technician_Id'])
                ->update(['fk_Team_Id' => $teamId['id']
                ]);
        }

        /**
         * Updates Technician's foreign key.
         */
        if($rowValue['technicianEmail']) {
            $userId = $this->database->table('user')->select('fk_Technician_Id')->where('email = ?', $rowValue['technicianEmail'])->fetch();
            $this->database->table('technician')
                ->where('id = ?', $userId['fk_Technician_Id'])
                ->update(['fk_Team_Id' => $teamId['id']
                ]);
        }
    }

    /**
     * Function that goes through excel sheet and inserts data from array created by @function{createTeamArray}
     * into database
     * @param Spreadsheet $spreadsheet - Received excel sheet with data to input
     */
    private function insertClient(array $rowValue)
    {

        $log = $this->database->table('clientSupervisor')->insert([
            'phone' => $rowValue['phone'],
            'locationDescription' => $rowValue['description']
        ]);

        Nette\Utils\Validators::assert($rowValue['email'], 'email');
        $this->database->table('user')->insert([
            'firstName' => $rowValue['firstName'],
            'lastName' => $rowValue['lastName'],
            'email' => $rowValue['email'],
            'password' => $this->userManager->generateNewPassword($rowValue['email']),
            'tempPassword' => true,
            'fk_UserRole_Id' => $rowValue['userRole'],
            'fk_ClientSupervisor_Id' => $log->id
        ]);
    }

    /**
     * Function that clears database of data.
     */
    private function clearDatabase()
    {
        $this->database->query('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;');
        $this->database->table('changes')->delete();
        $this->database->table('user')->delete();
        $this->database->table('team')->delete();
        $this->database->table('technician')->delete();
        $this->database->table('clientSupervisor')->delete();
        $this->database->table('janitor')->delete();
        $this->database->table('address')->delete();
        $this->database->table('clientOffice')->delete();
        $this->database->table('officeContact')->delete();
        $this->database->query('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;');
    }


}