<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Mail\Message;
use Nette\Security\Passwords;
use Nette\Utils\Random;

/**
 * Users management.
 * Class made from original sandbox user manager and expanded.
 * @author Nette team + Petr Zeman
 * @version Summer 2020
 */
final class UserManager implements Nette\Security\IAuthenticator
{
    use Nette\SmartObject;

    private const
        TABLE_NAME = 'user',
        COLUMN_ID = 'id',
        COLUMN_FIRST_NAME = 'firstName',
        COLUMN_LAST_NAME = 'lastName',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_EMAIL = 'email',
        COLUMN_ROLE = 'fk_UserRole_Id',
        COLUMN_TEMP_PASSWORD = 'tempPassword';


    /** @var Nette\Database\Context */
    private $database;

    /** @var Passwords */
    private $passwords;

    /** @var Nette\Mail\Mailer */
    private $mailer;


    /**
     * UserManager constructor.
     * @param Nette\Database\Context $database
     * @param Passwords $passwords
     */
    public function __construct($mailer,Nette\Database\Context $database, Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
        $this->mailer = $mailer;

    }


    /**
     * Performs an authentication.
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials): Nette\Security\IIdentity
    {
        [$email, $password] = $credentials;

        $row = $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_EMAIL, $email)
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('The email is incorrect.', self::IDENTITY_NOT_FOUND);

        } elseif (!$this->passwords->verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

        } elseif ($this->passwords->needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
            $row->update([
                self::COLUMN_PASSWORD_HASH => $this->passwords->hash($password),
            ]);
        }

        $name = $row->ref('userRole', 'fk_UserRole_Id')->name;

        $arr = $row->toArray();
        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($row[self::COLUMN_ID], $name, $arr);
    }

    /**
     * @param $email - email of new user (Later for sending password to email)
     * @return string - returns new password
     */
    public function generateNewPassword($email){
        $newPassword = Random::generate(10);
        //file_put_contents('TestFile.txt', $email . ' ' . $newPassword . " \r\n", FILE_APPEND);
        $mail = new Message;
        $mail->setFrom('Fleenk <Fleenk@smtp.mailtrap.io>')
            ->addTo($email)
            ->setSubject('Application password.')
            ->setBody("Hello, Your new password is: ".$newPassword);

        $smtp = new Nette\Mail\SmtpMailer($this->mailer);
        try {
            $smtp->send($mail);
        }catch(\Exception $e){

        }
        return $this->passwords->hash($newPassword);
    }

    /** Function for reseting user's password.
     * @param $id - Id of user that should get his password reset.
     */
    public function resetPassword($id){
        $user = $this->database->table('user')->where('id=?',$id)->fetch();
        $newPassword = $this->generateNewPassword($user->email);
        $user->update([
            'password'=>$newPassword,
            'tempPassword' => true
        ]);
    }

    /**
     * Adds new user(technician).
     * @param $firstName - First name of user
     * @param $lastName - Last name of user
     * @param $email - email of user
     * @param $phone - phone of user
     * @param $role - role(counting on technician)
     * @throws DuplicateUserException
     * @throws DuplicateTechnicianException
     */
    public function add(string $firstName, string $lastName, string $email, $phone, $role): void
    {

        /**Checks for duplicity of users*/
        $emailCheck = $this->database->table('user')->where('email=?', $email)->fetchAll();
        if ($emailCheck) {
            throw new DuplicateUserException;
        }

        $technicianCheck = $this->database->table('technician')->where('phone=?', $phone)->fetchAll();
        if ($technicianCheck) {
            throw new DuplicateTechnicianException;
        }


        $technicianId = $this->database->table('technician')->insert([
            'phone' => $phone
        ]);
        $newPassword= $this->generateNewPassword($email);

        Nette\Utils\Validators::assert($email, 'email');

        $this->database->table(self::TABLE_NAME)->insert([
            self::COLUMN_FIRST_NAME => $firstName,
            self::COLUMN_LAST_NAME => $lastName,
            self::COLUMN_PASSWORD_HASH => $newPassword,
            self::COLUMN_EMAIL => $email,
            self::COLUMN_ROLE => $role,
            self::COLUMN_TEMP_PASSWORD => true,

            'fk_Technician_Id' => $technicianId['id']
        ]);

    }

    /** removes a user.
     * @param $id - Id of user to be removed.
     */
    public function removeTechnician($id){
        $user = $this->database->table('user')->where('id=?',$id)->fetch();
        $this->database->table('user')->where('id=?',$id)->delete();
        $this->database->table('technician')->where('id=?',$user->fk_Technician_Id)->delete();
    }

    public function changePassword($id,$password){
        $this->database->table('user')->where('id=?',$id)->update([
           'password'=>$this->passwords->hash($password),
            'tempPassword' => false
        ]);
    }

}

/**
 * Class DuplicateUserException - Exception warning about duplicate email (user)
 * @package App\Model
 */
class DuplicateUserException extends \Exception
{
}

/**
 * Class DuplicateTechnicianException - exception warning about duplicate phone (technician).
 * @package App\Model
 */
class DuplicateTechnicianException extends \Exception
{
}
