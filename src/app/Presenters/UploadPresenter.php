<?php
namespace App\Presenters;

use App\Forms;
use Nette\Application\UI\Form;

/**
 * Class UploadPresenter - Presenter for database management page.
 * @package App\Presenters
 * @author Petr Zeman
 * @version Summer 2020
 */
class UploadPresenter extends BasePresenter
{
    /** @var \App\Forms\UploadFormFactory * */
    private $uploadFactory;

    /** @var Forms\DownloadFormFactory */
    private $downloadFactory;

    /**
     * UploadPresenter constructor.
     * @param Forms\UploadFormFactory $uploadFactory
     * @param Forms\DownloadFormFactory $downloadFactory
     */
    public function __construct(Forms\UploadFormFactory $uploadFactory, Forms\DownloadFormFactory $downloadFactory)
    {
        $this->uploadFactory = $uploadFactory;
        $this->downloadFactory = $downloadFactory;
    }

    /**
     * Default renderer of page
     */
    public function renderDefault(): void
    {
        $this->template->presenter = $this->presenter->name;
        if (isset($this->user)) {
            $this->template->role = $this->user->getRoles()[0];
            if ($this->user->getIdentity()!=null) {
                $this->template->user = $this->user->getIdentity()->getData()['lastName'];
            }else{
                $this->template->user ='';
            }
        }

    }


    /** Component taking care of creation of form for importing database data from excel.
     * @return Form - Form for importing database data from excel.
     */
    protected function createComponentUploadForm(): Form
    {
        return $this->uploadFactory->create(function (): void {
            $this->redirect('Branches:');
        });
    }

    /** Component taking care of form for exporting data into excel
     * @return Form - Form for exporting database data into excel.
     */
    protected function createComponentDownloadForm(): Form
    {
        return $this->downloadFactory->create(function (): void {
            $this->redirect('Branches:');
        });
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