<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class UserRegistration extends Control {

	/** var Nette\Application\UI\Form **/
	private $form;

	/** App\Model\Registration **/
	private $registrationModel;

	public function __construct( $parent, $name, $registrationModel ) {
		parent::__construct( $parent, $name );
		$this->registrationModel = $registrationModel;
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
		$form->setDefaults( array(
			'name' => $identity->name,
			'email' => $identity->email
		) );
		$form->addSubmit( 'submit', 'Odeslat registraci' );
		$form->onSuccess[] = array( $this, 'processRegistration' );

		return $form;
	}

	public function addUsersFields( $container ) {
		$container->addText( 'name', 'Jméno a příjmení' )
			->addRule(Form::FILLED, 'Jméno musí být vyplněno');
		$container->addText( 'twitter', 'Twitter' )
			->setAttribute('placeholder', '@');
		$container->addText( 'email', 'E-mail')
			->addRule(Form::FILLED, 'E-mail musí být vyplněn')
			->setAttribute('placeholder', '@')
			->setOption('description', 'Email nebude nikde zvěřejněn');
		$container->addTextArea( 'bio', 'Bio' )
			->addRule(Form::FILLED, 'Bio musí být vyplněno');
		return $container;
	}

	public function processRegistration( Form $form ) {
		$values = (array) $form->getValues();
		$user = $this->getPresenter()->getUser();
		$values['created_date'] = new \MongoDate( time() );
		$values['picture_url'] = $user->getIdentity()->picture_url;
		$this->registrationModel->updateConferree( $user->getId(), $values );
	}

}