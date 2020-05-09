<?php

declare(strict_types=1);

namespace App\Presenters;


use App\Model\FormManager;
use App\Model\GridManager;

/**
 * Class BranchesPresenter - Presenter for branches management page.
 * @package App\Presenters
 * @author Petr Zeman
 * @version Summer 2020
 */
final class BranchesPresenter extends BasePresenter
{

    /** @var GridManager */
    private $gridManager;

    /** @var FormManager */
    private $formManager;

    public function __construct(GridManager $gridManager, FormManager $formManager)
    {
        $this->gridManager = $gridManager;
        $this->formManager = $formManager;
    }

    /**
     * Default renderer of page
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
        if ($this->getUser()->getRoles()[0] == 'Klient') {

            $columns = $this->prepareClient();
            $this->template->columns = $columns;
            $rows = $this->gridManager->getBranchData($this->user->getRoles()[0], $this->getUser()->getId());
            $this->template->rows = $rows;
            $this->template->rowNumber = sizeof($rows);
            $this->template->expand = false;
            $this->template->columnNumber = 6;
            $this->template->secondaryColumnNumber = 0;

        } else if ($this->getUser()->getRoles()[0] == 'Technik') {

            $columns = $this->prepareTechnician();
            $this->template->columns = $columns;
            $rows = $this->gridManager->getBranchData($this->user->getRoles()[0], $this->getUser()->getId());
            $this->template->rows = $rows;
            $this->template->rowNumber = sizeof($rows);
            $this->template->expand = true ;
            $this->template->columnNumber = 6;
            $this->template->secondaryColumnNumber = 4;

        }else if ($this->getUser()->getRoles()[0] == 'Admin') {
            $columns = $this->prepareAdmin();
            $this->template->columns = $columns;
            $rows = $this->gridManager->getBranchData($this->user->getRoles()[0], $this->getUser()->getId());
            $this->template->rows = $rows;
            $this->template->rowNumber = sizeof($rows);
            $this->template->expand = true ;
            $this->template->columnNumber = 7;
            $this->template->secondaryColumnNumber = 5;

        }
    }

    /** Function that sets up columns of a table for clients. Columns are returned in an Json encoded array.
     * @return JSON encoded array
     */
    private function prepareClient()
    {
        $columns = [];
        array_push($columns, json_encode(['field' => 'BranchCode', 'title' => 'BranchCode', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchAddress', 'title' => 'BranchAddress', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeader', 'title' => 'BranchLeader', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeaderPhone', 'title' => 'BranchLeaderPhone', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'State', 'title' => 'state', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'button', 'title' => 'Change State', 'sortable' => false]));
        return $columns;

    }

    /** Function that sets up columns of a table for technicians. Columns are returned in an Json encoded array.
     * @return JSON encoded array
     */
    private function prepareTechnician()
    {
        $columns = [];
        array_push($columns, json_encode(['field' => 'BranchCode', 'title' => 'BranchCode', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'Supervisor', 'title' => 'Supervisor', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchAddress', 'title' => 'BranchAddress', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeader', 'title' => 'BranchLeader', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeaderPhone', 'title' => 'BranchLeaderPhone', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'State', 'title' => 'State', 'sortable' => true]));

        array_push($columns, json_encode(['field' => 'JanitorPhone', 'title' => 'JanitorPhone', 'sortable' => false]));
        array_push($columns, json_encode(['field' => 'BranchDescription', 'title' => 'BranchDescription', 'sortable' => false]));
        array_push($columns, json_encode(['field' => 'BranchNote', 'title' => 'BranchNote', 'sortable' => false]));
        array_push($columns, json_encode(['field' => 'button', 'title' => 'Change State', 'sortable' => false]));


        return $columns;

    }

    /** Function that sets up columns of a table for administrators. Columns are returned in an Json encoded array.
     * @return JSON encoded array
     */
    private function prepareAdmin()
    {
        $columns = [];
        array_push($columns, json_encode(['field' => 'BranchCode', 'title' => 'BranchCode', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'Supervisor', 'title' => 'Supervisor', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchAddress', 'title' => 'BranchAddress', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeader', 'title' => 'BranchLeader', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeaderPhone', 'title' => 'BranchLeaderPhone', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'State', 'title' => 'State', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchDescription', 'title' => 'BranchDescription', 'sortable' => false]));


        array_push($columns, json_encode(['field' => 'TeamName', 'title' => 'TeamName', 'sortable' => false]));
        array_push($columns, json_encode(['field' => 'TeamLeaderPhone', 'title' => 'TeamLeaderPhone', 'sortable' => false]));
        array_push($columns, json_encode(['field' => 'BranchNote', 'title' => 'BranchNote', 'sortable' => false]));
        array_push($columns, json_encode(['field' => 'select', 'title' => 'Select State', 'sortable' => false]));
        array_push($columns, json_encode(['field' => 'button', 'title' => 'Change State', 'sortable' => false]));



        return $columns;

    }

    /**
     * Changes state.
     */
    public function handleChangeState($branchCode, $newState)
    {

        if ($this->isAjax()) {

            $newerState =$this->formManager->setState($branchCode,$newState,
                $this->getUser()->getIdentity()->getData()['id']);


            $this->sendResponse(
                new \Nette\Application\Responses\JsonResponse(
                    [
                        "status"    => "ok",
                        "branch"    => $branchCode,
                        "newState"  => $newState,
                        "newerState" => $newerState,
                    ]
                )
            );
        }
    }

    /** Checks for correct role before rendering the page.
     * @throws \Nette\Application\AbortException
     */
    function beforeRender()
    {
        if ($this->user->isInRole('guest')) {
            $this->redirect('Sign:In');
        }
        if ($this->user->getIdentity()->tempPassword == true){
            $this->redirect('Sign:changePassword');
        }
    }

}
