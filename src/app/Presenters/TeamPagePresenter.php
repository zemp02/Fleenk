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
final class TeamPagePresenter extends BasePresenter
{

    /** @var GridManager */
    private $gridManager;

    /** @var FormManager */
    private $formManager;

    /** @var AddMemberFormFactory */
    private $addMemberFactory;

    /** @var AddClientFormFactory */
    private $addClientFactory;

    /** Id of team to open. */
    private $id;

    /** @persistent */
    public $backlink = '';

    /**
     * TeamPagePresenter constructor.
     * @param GridManager $gridManager
     * @param FormManager $formManager
     * @param AddMemberFormFactory $addMemberFactory
     */
    public function __construct(GridManager $gridManager,
                                FormManager $formManager,
                                AddMemberFormFactory $addMemberFactory,
                                AddClientFormFactory $addClientFactory )
    {
        $this->gridManager = $gridManager;
        $this->formManager = $formManager;
        $this->addMemberFactory = $addMemberFactory;
        $this->addClientFactory = $addClientFactory;
    }

    /**
     * Default renderer of page
     */
    public function renderDefault($id): void
    {
        if ($id == null) {
            $this->redirect('Sign:In');
        }
        $this->id = $id;
        $this->template->presenter = $this->presenter->name;
        if (isset($this->user)) {
            $this->template->role = $this->user->getRoles()[0];
            if ($this->user->getIdentity() != null) {
                $this->template->user = $this->user->getIdentity()->getData()['lastName'];
            } else {
                $this->template->user = '';
            }
        }


        $memberColumns = $this->prepareMembers();
        $branchColumns = $this->prepareBranches();
        $this->template->memberColumns = $memberColumns;
        $this->template->branchColumns = $branchColumns;
        $memberRows = $this->gridManager->getTeamPageMemberData($this->id);
        $branchRows = $this->gridManager->getTeamPageBranchData($this->id);
        $teamLeaderData = array_shift($memberRows);
        $this->template->TeamId = $this->id;
        $this->template->TeamName = $teamLeaderData['TeamName'];
        $this->template->TeamLeaderName = $teamLeaderData['TeamLeader'];
        $this->template->TeamLeaderPhone = $teamLeaderData['TeamLeaderPhone'];
        $this->template->memberRows = $memberRows;
        $this->template->branchRows = $branchRows;
        $this->template->memberColumnNumber = sizeof($memberColumns);
        $this->template->branchColumnNumber = sizeof($branchColumns);

    }


    /** Component taking care of form for adding technicians into team
     * @return Form - Form for adding technicians into team.
     */
    protected function createComponentMemberForm(): Form
    {
        return $this->addMemberFactory->create($this->id ,function (): void {
            $this->restoreRequest($this->backlink);
        });

    }

    /** Component taking care of form for assigning branches to team.
     * @return Form - Form for assigning branches to team.
     */
    protected function createComponentBranchForm(): Form
    {
        return $this->addClientFactory->create($this->id ,function (): void {
            $this->restoreRequest($this->backlink);
        });

    }

    /**
     * removes member from team.
     */
    public function handleRemoveRow($id, $input, $teamId)
    {
        if ($this->isAjax()) {
            if ($input == 'member') {
                $this->formManager->removeMember($teamId, $id);
            } elseif ($input == 'branch') {
                $this->formManager->removeBranch($id);
            }


            $this->sendResponse(
                new \Nette\Application\Responses\JsonResponse(
                    [
                        "status" => "ok",
                    ]
                )
            );
        }
    }

    /** Function that sets up columns of a member table. Columns are returned in an Json encoded array.
     * @return JSON encoded array
     */
    private function prepareMembers()
    {
        $columns = [];
        array_push($columns, json_encode(['field' => 'MemberEmail', 'title' => 'Email', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'MemberName', 'title' => 'Name', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'MemberPhone', 'title' => 'Phone', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'Remove', 'title' => 'Remove', 'sortable' => false]));

        return $columns;
    }

    /** Function that sets up columns of assigned branches table. Columns are returned in an Json encoded array.
     * @return JSON encoded array
     */
    private function prepareBranches()
    {
        $columns = [];

        array_push($columns, json_encode(['field' => 'BranchCode', 'title' => 'BranchCode', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchAddress', 'title' => 'BranchAddress', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchDescription', 'title' => 'BranchDescription', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeader', 'title' => 'BranchLeader', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'BranchLeaderPhone', 'title' => 'BranchLeaderPhone', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'State', 'title' => 'state', 'sortable' => true]));
        array_push($columns, json_encode(['field' => 'Remove', 'title' => 'Remove', 'sortable' => true]));

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
