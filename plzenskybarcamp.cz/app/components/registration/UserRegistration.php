<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class UserRegistration extends Control {

	/** var Nette\Application\UI\Form **/
	private $form;

	/** App\Model\Registration **/
	private $registrationModel;

	private $token;

	public function __construct( $parent, $name, $registrationModel, $token = NULL ) {
		parent::__construct( $parent, $name );
		$this->registrationModel = $registrationModel;
		$this->token = $token;
	}
	
	public function render() {
		$this->template->setFile( __DIR__ . '/templates/userRegistration.latte' );
		$this->template->render();
	}

	public function createComponentForm( $name ) {
		$form = new Form( $this, $name );
		$form->setRenderer( new \App\Components\CustomFormRenderer );
		$form = $this->addUsersFields( $form );

		$identity = $this->getPresenter()->getUser()->getIdentity();
		$dafaultValues = array(
			'name' => $identity->name,
			'email' => $identity->email
		);
		if( $identity->current_platform == 'tw' ) {
			$dafaultValues['twitter'] = '@'.$identity->platforms['tw']->screen_name;
		}
		$form->setDefaults( $dafaultValues );

		$form->addSubmit( 'submit', 'Odeslat registraci' );
		$form->onSuccess[] = array( $this, 'processRegistration' );

		return $form;
	}

	public function addUsersFields( $container ) {
		$container->addText( 'name', 'Jméno a příjmení' )
			->addRule(Form::FILLED, 'Jméno musí být vyplněno');
		$container->addText( 'twitter', 'Twitter' )
			->setAttribute('placeholder', '@DavidGrudl');
		$container->addText( 'email', 'E-mail')
			->addRule(Form::EMAIL, 'Tenhle e-mail nevypadá jako e-mail, zkuste se na to podívat')
			->setRequired( 'E-mail musí být vyplněn' )
			->setAttribute('placeholder', 'grudl@gmail.com')
			->setOption('description', 'Email nebude nikde zveřejněn');
		$container->addTextArea( 'bio', 'Bio' )
			->setOption('description', 'Můžete doplnit také později ve svém profilu');
		$container->addCheckbox( 'lunch', 'Mám mimo drobného občerstvení zájem také o oběd (cca 100Kč)' );
		$container->addCheckbox( 'afterparty', 'Zúčastním se afterparty v centru Plzně' );
		return $container;
	}

	public function processRegistration( Form $form ) {
		$values = (array) $form->getValues();
		$token = $this->token;

		if($token) {
			try {
				$this->registrationModel->validateVipToken( $token );
			}
			catch (\App\Model\InvalidTokenException $e) {
				$form->addError("Váš VIP token je neplatný, požádejte si o nový.");
				return;
			}
		}


		$user = $this->getPresenter()->getUser();
		$values['created_date'] = new \MongoDate( time() );
		$values['picture_url'] = $user->getIdentity()->picture_url;
		$values['identity'] = $user->getIdentity()->data;
		$values['vip_token'] = $this->token;
		$this->registrationModel->updateConferree( $user->getId(), $values );

		if($this->token) {
			$this->registrationModel->invalideVipToken($token);
			$session = $this->getPresenter()->getContext()->getService("session")->getSection("vip")->remove();
		}

		$conferee = $this->registrationModel->findCoferree( $user->getId() );
		$user->getIdentity()->conferee = $conferee;
	}

}