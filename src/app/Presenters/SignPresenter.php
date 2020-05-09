<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms;
use Nette\Application\UI\Form;


/**
 * Class SignPresenter - Presenter for Authorisation/User addition/Pssword Change management pages.
 * @package App\Presenters
 * @author Petr Zeman
 * @version Summer 2020
 */
final class SignPresenter extends BasePresenter
{
	/** @persistent */
	public $backlink = '';

	/** @var Forms\SignInFormFactory */
	private $signInFactory;

	/** @var Forms\ChangePasswordFormFactory */
    private $changePasswordFactory;

	/** @var Forms\UploadFormFactory */
	private $uploadFactory;


    /**
     * SignPresenter constructor.
     * @param Forms\SignInFormFactory $signInFactory
     * @param Forms\UploadFormFactory $uploadFactory
     * @param Forms\ChangePasswordFormFactory $changePasswordFactory
     */
	public function __construct(Forms\SignInFormFactory $signInFactory,
                                Forms\UploadFormFactory $uploadFactory,
                                Forms\ChangePasswordFormFactory $changePasswordFactory)
	{
		$this->signInFactory = $signInFactory;
		$this->uploadFactory = $uploadFactory;
		$this->changePasswordFactory = $changePasswordFactory;
	}


    /** Component taking care of form for signing in.
     * @return Form - Form for signing in.
     */
	protected function createComponentSignInForm(): Form
	{
		return $this->signInFactory->create(function (): void {
			$this->restoreRequest($this->backlink);
			$this->redirect('Branches:');
		});
	}


    /** Component taking care of form for changing password of user.
     * @return Form - Form for changing password of user.
     * @throws \Nette\Application\AbortException
     */
    public function createComponentChangePasswordForm()
    {
        if (isset($this->user)) {
            if ($this->user->getIdentity() != null) {
                $this->template->user = $this->user->getIdentity()->getData()['lastName'];
            } else {
                $this->redirect('Sign:In');
            }
        }
        return $this->changePasswordFactory->create(function (): void {
            $this->restoreRequest($this->backlink);
            $this->redirect('Branches:');
        });
    }


    /** Handles signing out of users.
     * @throws \Nette\Application\AbortException
     */
    public function actionOut(): void
	{
		$this->getUser()->logout(true);
        $this->redirect('Branches:');
	}


}
