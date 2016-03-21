<?php

namespace App\Presenters;

use Nette,
	App\Model,
	App\Components\Registration\Main,
	App\Components\Lists\UsersList,
	App\Components\Lists\TalksList;


/**
 * Homepage presenter.
 */
class VipPresenter extends BasePresenter
{

	private $registrationModel;

	public function __construct( Model\Registration $registrationModel ) {
		$this->registrationModel = $registrationModel;
	}

	public function actionUseToken( $token ) 
	{
		try {
			$this->registrationModel->validateVipToken( $token );
		}
		catch( Model\InvalidTokenException $e) {
			$this->flashMessage("Omlouváme se, ale Váš VIP token je neplatný.", 'error');
			$this->flashMessage(['event'=>'flash-message','action'=>'vip-token-invalid'], "dataLayer-push");
			$this->redirect('Homepage:default');
		}

		$session = $this->getContext()->getService("session")->getSection("vip");
		$session->token = $token;

		$this->flashMessage("Tvůj VIP token byl přijat a můžeš se registrovat kliknutím na „Chci přijít“" , 'success');
		$this->flashMessage(['event'=>'flash-message','action'=>'vip-token-accepted'], "dataLayer-push");
		$this->redirect('Homepage:default');
	}

}
