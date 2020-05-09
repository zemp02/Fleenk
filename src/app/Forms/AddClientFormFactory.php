<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

/**
 * Class AddClientFormFactory
 * Factory creating form that assigns branch to team on success.
 * @package App\Forms
 * @author Petr Zeman
 * @version Summer 2020
 */
final class AddClientFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\FormManager */
	private $formManager;


    /**
     * AddClientFormFactory constructor.
     * @param FormFactory $factory
     * @param Model\FormManager $formManager
     */
	public function __construct(FormFactory $factory, Model\FormManager $formManager)
	{
		$this->factory = $factory;
		$this->formManager = $formManager;
	}

    /** Function that creates form for assigning branches to team.
     * @param callable $onSuccess
     * @return Form - Form for assigning branch to team.
     */
	public function create($id,callable $onSuccess): Form
	{
        $form = $this->factory->create();
        $form->addText('branch', 'Branch: ')
            ->setRequired('Please enter code of branch to  to team.')
            ->setDefaultValue('');
        $form->addHidden('id')->setValue($id);
        $form->addSubmit('add', 'Add');
		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void {
			try {
                $this->formManager->setBranch($values->id, $values->branch);
            } catch (Model\MissingBranchException $e) {
                $form['branch']->addError('No such branch exists.');
                return;
            } catch (Model\AssignedBranchException $e){
                $form['branch']->addError('This branch is already assigned to different team.');
            }
			$onSuccess();
		};

		return $form;
	}
}
