<?php

declare(strict_types=1);

namespace App\Presenters;


use App\Forms\NewTeamFormFactory;
use Nette\Application\UI\Form;
use App\Model\GridManager;

/**
 * Class TeamsPresenter - Presenter for team management page.
 * @package App\Presenters
 * @author Petr Zeman
 * @version Summer 2020
 */
final class TeamsPresenter extends BasePresenter
{

    /** @var GridManager */
    private $gridManager;

    /** @var NewTeamFormFactory */
    private $newTeamFactory;

    /** @persistent */
    public $backlink = '';

    /**
     * TeamsPresenter constructor.
     * @param GridManager $gridManager
     * @param NewTeamFormFactory $newTeamFactory
     */
    public function __construct(GridManager $gridManager, NewTeamFormFactory $newTeamFactory)
    {
        $this->gridManager = $gridManager;
        $this->newTeamFactory = $newTeamFactory;
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
                $this->template->user = $this->user
                    ->getIdentity()
                    ->getData()['lastName'];
            } else {
                $this->template->user = '';
            }
        }

        $columns = $this->prepareTeams();
        $this->template->columns = $columns;
        $rows = $this->gridManager->getTeamsData();
        $this->template->rows = $rows;
        $this->template->rowNumber = sizeof($rows);
        $this->template->expand = false;
        $this->template->columnNumber = 6;
        $this->template->secondaryColumnNumber = 6;
        $this->template->tertiaryColumnNumber = 4;

    }

    /** Creates component taking care of form for creation of new team.
     * @return Form - Form for creation of new team.
     */
    protected function createComponentTeamForm(): Form
    {
        return $this->newTeamFactory->create(function (): void {
            $this->restoreRequest($this->backlink);
        });

    }

    /** Function that sets up columns of a team table. Columns are returned in an Json encoded array.
     * @return JSON encoded array
     */
    private function prepareTeams()
    {
        $columns = [];
        array_push($columns, json_encode(['field' => 'TeamName', 'title' => 'Team Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'Branches', 'title' => 'Branches', 'sortable' => false]));
        array_push($columns, json_encode(['field' => 'LeaderName', 'title' => 'Leader Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'LeaderPhone', 'title' => 'Leader Phone', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'Members', 'title' => 'Members', 'sortable' => false]));
        array_push($columns, json_encode(['field' => 'teamPage', 'title' => 'Team Page', 'sortable' => false]));

        array_push($columns, json_encode(['field' => 'BranchCode', 'title' => 'BranchCode', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchAddress', 'title' => 'BranchAddress', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchDescription', 'title' => 'BranchDescription', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeader', 'title' => 'BranchLeader', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeaderPhone', 'title' => 'BranchLeaderPhone', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'State', 'title' => 'state', 'sortable' => true]));

        array_push($columns, json_encode(['field' => 'MemberFirstName', 'title' => 'Member First Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'MemberLastName', 'title' => 'Member Last Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'MemberEmail', 'title' => 'Member Email', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'MemberPhone', 'title' => 'Member Phone', 'sortable' => true]));


        return $columns;

    }

    /** Checks for correct role before rendering the page.
     * @throws \Nette\Application\AbortException
     */
    function beforeRender()
    {
        if (! $this->user->isInRole('Admin')) {
            $this->redirect('Sign:In');
        }
    }

}
