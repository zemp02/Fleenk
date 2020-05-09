<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


/**
 * Class SignInFormFactory
 * Factory creating form that logins and authorizes users on success.
 * @package App\Forms
 * @author Petr Zeman
 * @version Summer 2020
 */
final class SignInFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;

    /**
     * SignInFormFactory constructor.
     * @param FormFactory $factory
     * @param User $user
     */
	public function __construct(FormFactory $factory, User $user)
	{
		$this->factory = $factory;
		$this->user = $user;
	}

    /** Function that creates form for user authentication(login).
     * @param callable $onSuccess
     * @return Form - Form for user authentication.
     */
	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->addText('email', 'Email:')
			->setRequired('Please enter your email.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Keep me signed in');

		$form->addSubmit('send', 'Sign in');
        $form->addProtection();

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void {
			try {
				$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
				$this->user->login($values->email, $values->password);
			} catch (Nette\Security\AuthenticationException $e) {
				$form->addError('The email or password you entered is incorrect.');
				return;
			}
			$onSuccess();
		};

		return $form;
	}
}
