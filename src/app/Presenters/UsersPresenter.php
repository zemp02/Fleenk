<?php

declare(strict_types=1);

namespace App\Presenters;


use App\Forms\SignUpFormFactory;
use App\Model\FormManager;
use App\Model\UserManager;
use Nette\Application\UI\Form;
use App\Model\GridManager;

/**
 * Class UsersPresenter - Presenter for user management page.
 * @package App\Presenters
 * @author Petr Zeman
 * @version Summer 2020
 */
final class UsersPresenter extends BasePresenter
{

    /** @var GridManager */
    private $gridManager;

    /** @var FormManager */
    private $formManager;

    /** @var UserManager */
    private $userManager;

    /** @var SignUpFormFactory */
    private $signUpFactory;

    /** @persistent */
    public $backlink = '';

    /**
     * UsersPresenter constructor.
     * @param GridManager $gridManager
     * @param FormManager $formManager
     * @param UserManager $userManager
     * @param SignUpFormFactory $signUpFactory
     */
    public function __construct(GridManager $gridManager, FormManager $formManager, UserManager $userManager, SignUpFormFactory $signUpFactory)
    {
        $this->gridManager = $gridManager;
        $this->formManager = $formManager;
        $this->userManager = $userManager;
        $this->signUpFactory = $signUpFactory;
    }

    /**
     * Default renderer of page.
     */
    public function renderDefault(): void
    {
        $this->template->presenter = $this->presenter->name;
        if (isset($this->user)) {
            $this->template->role = $this->user->getRoles()[0];
            if ($this->user->getIdentity() != null) {
                $this->template->user = $this->user->getIdentity()->getData()['lastName'];
            } else {
                $this->template->user = '';
            }
        }


        $technicianColumns = $this->prepareTechnicians();
        $clientColumns = $this->prepareClients();
        $this->template->technicianColumns = $technicianColumns;
        $this->template->clientColumns = $clientColumns;
        $technicianRows = $this->gridManager->getTechnicianData();
        $clientRows = $this->gridManager->getClientData();
        $this->template->technicianRows = $technicianRows;
        $this->template->clientRows = $clientRows;
        $this->template->technicianColumnNumber = sizeof($technicianColumns);
        $this->template->clientColumnNumber = sizeof($clientColumns);

    }

    /**
     * Component for creation of form for adding new technician.
     * @return Form - Form for creation of technician.
     */
    protected function createComponentTechnicianForm(): Form
    {
        return $this->signUpFactory->create(function (): void {
            $this->restoreRequest($this->backlink);
        });

    }


    /** Ajax handler for removing a technician from database
     * @param $id - Id of technician to remove
     * @throws \Nette\Application\AbortException
     */
    public function handleRemoveRow($id)
    {
        if ($this->isAjax()) {
                $this->userManager->removeTechnician( $id);

            $this->sendResponse(
                new \Nette\Application\Responses\JsonResponse(
                    [
                        "status" => "ok",
                    ]
                )
            );
        }
    }

    /** Ajax handler for reseting a password of a user.
     * @param $id - id of user who should get password reset.
     * @throws \Nette\Application\AbortException
     */
    public function handleResetPassword($id)
    {
        if ($this->isAjax()) {
            $this->userManager->resetPassword($id);


            $this->sendResponse(
                new \Nette\Application\Responses\JsonResponse(
                    [
                        "status" => "ok",
                    ]
                )
            );
        }
    }

    /** Function that sets up columns of a technician table. Columns are returned in an Json encoded array.
     * @return JSON encoded array
     */
    private function prepareTechnicians()
    {
        $columns = [];
        array_push($columns, json_encode(['field' => 'technicianFirstName', 'title' => 'First Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'technicianLastName', 'title' => 'Last Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'technicianEmail', 'title' => 'Email', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'technicianPhone', 'title' => 'Phone', 'sortable' => true]));

        array_push($columns, json_encode(['field' => 'password', 'title' => 'Password', 'sortable' => false]));
        array_push($columns, json_encode(['field' => 'remove', 'title' => 'Remove', 'sortable' => false]));

        return $columns;

    }

    /** Function that sets up columns of a client table. Columns are returned in an Json encoded array.
     * @return JSON encoded array
     */
    private function prepareClients()
    {
        $columns = [];
        array_push($columns, json_encode(['field' => 'clientFirstName', 'title' => 'First Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'clientLastName', 'title' => 'Last Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'clientDescription', 'title' => 'Description', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'clientEmail', 'title' => 'Email', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'clientPhone', 'title' => 'Phone', 'sortable' => true]));

        array_push($columns, json_encode(['field' => 'password', 'title' => 'Password', 'sortable' => false]));

        return $columns;

    }

    /** Checks for correct role before rendering.
     * @throws \Nette\Application\AbortException
     */
function beforeRender()
{
    if (!$this->user->isInRole('Admin')) {
        $this->redirect('Sign:In');
    }
}

}
