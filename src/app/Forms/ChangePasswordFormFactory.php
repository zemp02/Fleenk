<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model\UserManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;

/**
 * Class ChangePasswordFormFactory
 * Factory creating form that changes requested user's password on success.
 * @package App\Forms
 * @author Petr Zeman
 * @version Summer 2020
 */
final class ChangePasswordFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;

	/** @var UserManager */
	private $userManager;

    /**
     * ChangePasswordFormFactory constructor.
     * @param FormFactory $factory
     * @param User $user
     * @param UserManager $userManager
     */
	public function __construct(FormFactory $factory, User $user, UserManager $userManager)
	{
		$this->factory = $factory;
		$this->user = $user;
		$this->userManager = $userManager;
	}

    /** Function that creates form for changing password
     * @param callable $onSuccess
     * @return Form - Form for changing password
     */
	public function create(callable $onSuccess): Form
	{
        $form = $this->factory->create();
        $form->addPassword('current', 'Current password:')
            ->setRequired('Please insert your current password.');
        $form->addPassword('password', 'New password:')
            ->setRequired('Please choose your new password.')
            ->addRule(Form::MIN_LENGTH, 'Your new password must be atleast %d characters long.', 10);
        $form->addPassword('passwordCheck', 'Confirm password:')
            ->setRequired('Please confirm your new password.')
            ->addRule(Form::EQUAL, 'Passwords are not equal', $form['password']);
        $form->addHidden('id')
            ->setValue($this->user->getId());
        $form->addSubmit('submit', 'Change Password');
        $form->addProtection();

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void {
				$this->userManager->changePassword($values->id, $values->password);
				$this->user->getIdentity()->tempPassword = false;
			$onSuccess();
		};

		return $form;
	}
}
