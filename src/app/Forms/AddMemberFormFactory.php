<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

/**
 * Class AddMemberFormFactory
 * Factory creating form that assigns technicians to teams on success.
 * @package App\Forms
 * @author Petr Zeman
 * @version Summer 2020
 */
final class AddMemberFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\FormManager */
	private $formManager;


    /**
     * AddMemberFormFactory constructor.
     * @param FormFactory $factory
     * @param Model\FormManager $formManager
     */
	public function __construct(FormFactory $factory, Model\FormManager $formManager)
	{
		$this->factory = $factory;
		$this->formManager = $formManager;
	}

    /** Function that creates form for assigning technicians to team.
     * @param $id - Id of team that is having technician assigned.
     * @param callable $onSuccess
     * @return Form - Form for assigning technicians to team.
     */
	public function create($id,callable $onSuccess): Form
	{
        $form = $this->factory->create();
        $form->addText('member', 'Member: ')
            ->setRequired('Please enter email of member to add to team.');
        $form->addHidden('id')->setValue($id);
        $form->addSubmit('add', 'Add');

		$form->onSuccess[] = function (Form $form, \stdClass $values)
        use ($onSuccess): void {
			try {
                $this->formManager->setMember($values->id, $values->member);
            } catch (Model\MissingUserException $e) {
                $form['member']
                    ->addError('No such technician exists.');
                return;
            } catch (Model\IncorrectRoleException $e) {
                $form['member']
                    ->addError('This user is not a technician.');
                return;
            } catch (Model\AssignedTechnicianException $e){
                $form['member']
                    ->addError('User with this email is already in different team.');
            }
			$onSuccess();
		};

		return $form;
	}
}
