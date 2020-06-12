<?php

declare(strict_types=1);

namespace App\Presenters;


use App\Forms\AddClientFormFactory;
use App\Forms\AddMemberFormFactory;
use App\Model\FormManager;
use Nette\Application\UI\Form;
use App\Model\GridManager;

/**
 * Class TeamPagePresenter - Presenter for specific team management page.
 * @package App\Presenters
 * @author Petr Zeman
 * @version Summer 2020
 */
final class ManagementPresenter extends BasePresenter
{

    /** @var GridManager */
    private $gridManager;

    /** @var FormManager */
    private $formManager;

    /** @var AddMemberFormFactory */
    private $addMemberFactory;

    /** @var AddClientFormFactory */
    private $addClientFactory;


    /** @persistent */
    public $backlink = '';

    /**
     * TeamPagePresenter constructor.
     * @param GridManager $gridManager
     * @param FormManager $formManager
     * @param AddMemberFormFactory $addMemberFactory
     */
    public function __construct(GridManager $gridManager,
                                FormManager $formManager)
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


        $freeTechnicianColumns = $this->prepareFreeTechnicians();
        $freeBranchColumns = $this->prepareFreeBranches();
        $teamColumns = $this->prepareTeams();


        $this->template->freeTechnicianColumns = $freeTechnicianColumns;
        $this->template->freeBranchColumns = $freeBranchColumns;
        $this->template->TeamColumns = $teamColumns;

        $freeTechnicianRows = $this->gridManager->getFreeTechnicianData();
        $this->template->freeTechnicianRows = $freeTechnicianRows;

        $freeBranchRows = $this->gridManager->getFreeBranchData();
        $this->template->freeBranchRows = $freeBranchRows;

        $highLoadRows = $this->gridManager->getTeamLoadData(true);
        $this->template->highLoadRows = $highLoadRows;

        $lowLoadRows = $this->gridManager->getTeamLoadData(false);
        $this->template->lowLoadRows = $lowLoadRows;

        $this->template->freeTechnicianColumnNumber = sizeof($freeTechnicianColumns);
        $this->template->freeBranchColumnNumber = sizeof($freeBranchColumns);
        $this->template->TeamColumnNumber = sizeof($teamColumns);



    }


    /** Function that sets up columns of a member table. Columns are returned in an Json encoded array.
     * @return JSON encoded array
     */
    private function prepareFreeTechnicians()
    {
        $columns = [];

        array_push($columns, json_encode(['field' => 'TechnicianFirstName', 'title' => 'First Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'TechnicianLastName', 'title' => 'Last Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'TechnicianEmail', 'title' => 'Email', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'TechnicianPhone', 'title' => 'Phone', 'sortable' => true]));


        return $columns;
    }

    /** Function that sets up columns of assigned branches table. Columns are returned in an Json encoded array.
     * @return JSON encoded array
     */
    private function prepareFreeBranches()
    {
        $columns = [];

        array_push($columns, json_encode(['field' => 'BranchCode', 'title' => 'BranchCode', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchAddress', 'title' => 'BranchAddress', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchDescription', 'title' => 'BranchDescription', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeader', 'title' => 'BranchLeader', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeaderPhone', 'title' => 'BranchLeaderPhone', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'State', 'title' => 'state', 'sortable' => true]));

        return $columns;
    }

    /** Function that sets up columns of a team table. Columns are returned in an Json encoded array.
     * @return JSON encoded array
     */
    private function prepareTeams()
    {
        $columns = [];
        array_push($columns, json_encode(['field' => 'TeamName', 'title' => 'Team Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'LeaderName', 'title' => 'Leader Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'LeaderPhone', 'title' => 'Leader Phone', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'teamPage', 'title' => 'Team Page', 'sortable' => false]));

        return $columns;
    }

    /** Checks for correct role before rendering the page.
     * @throws \Nette\Application\AbortException
     */
    function beforeRender()
    {
        if (!$this->user->isInRole('Admin')) {
            $this->redirect('Sign:In');
        }
    }

}
