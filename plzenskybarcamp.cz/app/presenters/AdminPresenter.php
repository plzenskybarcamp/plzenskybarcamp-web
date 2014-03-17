<?php

namespace App\Presenters;

use Nette,
	App\Model,
    Nette\Application\UI\Form;


  class AdminPresenter extends BasePresenter
{


    private $configModel;

    public function __construct( Model\Config $configModel ) {
        $this->configModel = $configModel;
    }

	public function startup() {

        parent::startup();

        // Redirect user to login page, if is not logged in
        if ( !$this->user->isLoggedIn() ) {
            $this->flashMessage( 'You\'re not logged in', 'error' );
            $this->redirect( '//Homepage:default' );
        }

        $identity = $this->user->getIdentity();

        if ( ! isset( $identity->data['platforms']['fb']['id'] ) || ! $this->isAdmin( $identity->data['platforms']['fb']['id'] ) ) {
            $this->flashMessage( 'You\'re not allowed here', 'error' );
            $this->redirect( '//Homepage:default' );
        }
	}

	private function isAdmin( $id ) {
		return in_array($id, array(
			'JB' => 1296988124,
			'Kollda' => 1011669265
		));
	}


    public function createComponentSwitchesForm() {
        $form = new Form();

        $form->addCheckbox( 'isRegistrationOpen', 'Registrace otevřena');
        $form->addText( 'registrationCapatity', 'Kapacita návštěvníků')
            ->setType('number')
            ->addRule(Form::INTEGER, 'Must be number')
            ->addRule(Form::RANGE, 'Number must be between %d and %d', array(0,9999))
            ->setRequired('Must be valid number.');
        $form->addSubmit( 'send', 'Uložit');

        $form->setDefaults( array(
            'isRegistrationOpen' => $this->configModel->getConfig( 'isRegistrationOpen', FALSE ),
            'registrationCapatity' => $this->configModel->getConfig( 'registrationCapatity', 0 ),
        ) );

        $form->onSuccess[] = array( $this, 'processSwitches');
        return $form;
    }

    public function processSwitches( Form $form ) {
        $values = (array) $form->getValues();

        $this->setConfig( 'isRegistrationOpen', $values['isRegistrationOpen'] );
        $this->setConfig( 'registrationCapatity', $values['registrationCapatity'] );

        $this->flashMessage('OK, sucessfull saved.');
        $this->redirect( 'this' );

    }

    private function setConfig( $id, $value ) {
        $currentValue = $this->configModel->getConfig( $id );
        if( $currentValue !== $value) {
            $this->configModel->setConfig( $id, $value );
        }
    }
}
