<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

/**
 * Class NewTeamFormFactory
 * Factory creating form that creates new team on success.
 * @package App\Forms
 * @author Petr Zeman
 * @version Summer 2020
 */
final class NewTeamFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\FormManager */
	private $formManager;

    /**
     * NewTeamFormFactory constructor.
     * @param FormFactory $factory
     * @param Model\FormManager $formManager
     */
	public function __construct(FormFactory $factory, Model\FormManager $formManager)
	{
		$this->factory = $factory;
		$this->formManager = $formManager;
	}

    /** Function that creates form for creating new team.
     * @param callable $onSuccess
     * @return Form - Form for creating new team.
     */
	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->addText('teamName', 'Team name:')
			->setRequired('Please pick a team name.');

        $form->addText('teamLeaderEmail', 'Team leader:')
            ->setRequired('Please insert email of teamLeader.');
		$form->addSubmit('create', 'Create');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void {
			try {
				$this->formManager->createTeam($values->teamName,$values->teamLeaderEmail);
			} catch (Model\DuplicateTeamException $e) {
				$form['teamName']->addError('Name is already in use.');
				return;
			} catch (Model\MissingUserException $e) {
                $form['teamLeaderEmail']->addError('No such technician exists.');
                return;
            } catch (Model\IncorrectRoleException $e) {
                $form['teamLeaderEmail']->addError('This user is not a technician.');
                return;
            }
			$onSuccess();
		};

		return $form;
	}
}
