<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;


/**
 * Class SignUpFormFactory
 * Factory creating form that adds users on success.
 * @package App\Forms
 * @author Petr Zeman
 * @version Summer 2020
 */
final class SignUpFormFactory
{
    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var Model\UserManager */
    private $userManager;


    /**
     * SignUpFormFactory constructor.
     * @param FormFactory $factory
     * @param Model\UserManager $userManager
     */
    public function __construct(FormFactory $factory, Model\UserManager $userManager)
    {
        $this->factory = $factory;
        $this->userManager = $userManager;
    }


    /** Function that creates form for creating new user(technician)
     * @param callable $onSuccess
     * @return Form - Form for creating new user
     */
    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();
        $form->addText('firstName', 'First name:')
            ->setRequired('Please pick a first name.');

        $form->addText('lastName', 'Last name:')
            ->setRequired('Please pick a last name.');

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Please enter e-mail.');

        $form->addText('phone', 'Phone:')
            ->setRequired('Please enter phone number.')
            ->addRule(Form::INTEGER, 'Phone must be a number');
        $form->addHidden('role')
            ->setValue(2);

        $form->addSubmit('create', 'Create');

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void {
            try {
                $this->userManager->add($values->firstName, $values->lastName, $values->email, $values->phone, $values->role);
            } catch (Model\DuplicateUserException $e) {
                $form['email']->addError('Email is already taken.');
                return;
            } catch (Model\DuplicateTechnicianException $e) {
                $form['phone']->addError('Phone is already taken by different technician.');
                return;
            }
            $onSuccess();
        };

        return $form;
    }
}
