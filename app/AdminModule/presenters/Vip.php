<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
    Nette\Application\UI\Form,
    App\Components\BootstrapizeForm;


  class VipPresenter extends BasePresenter
{

    private $registrationModel;

    public function __construct( Model\Registration $registrationModel ) {
        $this->registrationModel = $registrationModel;
    }

    public function renderList( $token = NULL ) {
        $this->template->registerHelper('mongoFormat', array( 'App\Components\Helpers', 'mongoFormat'));

        $tokensCursor = $this->registrationModel->findVipTokens([], ['sort'=>['created_date'=>-1]]);

        $this->template->currentToken = NULL;
        $this->template->tokens = array();
        foreach ($tokensCursor as $tokenObject) {
            $tokenObject['invalidity'] = $invalidity = $this->registrationModel->getVipTokenInvalidity( $tokenObject );
            if( $invalidity == FALSE ) {
                $tokenObject['validation'] = "Platný";
            }
            elseif( $invalidity == Model\Registration::TOKEN_EXCEPTION_EXPIRED ) {
                $tokenObject['validation'] = "Expiroval";
            }
            else {
                $tokenObject['validation'] = "Neplatný";
            }

            if( $tokenObject['_id'] == $token ) {
                $this->template->currentToken = $tokenObject;
            }
            $this->template->tokens[] = $tokenObject;
        }

    }

    public function renderInvalidate ($token ){
        $this['invalidateVipTokenForm']['token']->setValue($token);
    }

    public function createComponentVipTokenForm() {
        $form = new Form();

        $form->addText( 'name', 'Pro koho je token určen')
            ->setRequired('Zadej jméno adresáta tokenu');
        $expired_date = $form->addContainer( 'expired_date' );
        $expired_date->addText( 'day', 'Den expirace tokenu')
            ->setType('date')
            ->setOption('description', 'Pokud nebude vyplněn, token bude mít časově neomezenou platnost');
        $expired_date->addText( 'hour', 'Hodina expirace tokenu')
            ->setType('time')
            ->setDefaultValue('00:00:00');

        $form->addSubmit( 'send', 'Vytvořit VIP token');

        $form->onSuccess[] = array( $this, 'processVipToken');
        BootstrapizeForm::bootstrapize( $form );
        return $form;
    }

    public function processVipToken( Form $form ) {
        $values = (array) $form->getValues();
        if( $values['expired_date']['day'] && $values['expired_date']['hour']) {
            $date = $this->parseDate($values['expired_date']['day']  . " " . $values['expired_date']['hour']);
            if( ! $date ) {
                $form['expired_date']['day']->addError("Datum není platné. Pro správné vyplnění je třeba použít moderní browser s podporou HTML5 formulářů");
                return false;
            }
            $values['expired_date'] = $date->getTimestamp();
        }
        else {
            $values['expired_date'] = NULL;
        }

        $token = $this->registrationModel->createVipToken( $values );

        $this->flashMessage('Token vytvořen', 'success');
        $this->redirect( 'list', array('token'=>$token) );

    }

    public function createComponentInvalidateVipTokenForm() {
        $form = new Form();

        $form->addHidden( 'token' );

        $form->addSubmit( 'yes', 'Ano, opravdu zneplatnit')
            ->getControlPrototype()->class[] = 'btn-danger';
        $form->addSubmit( 'cancel', 'Storno')
            ->getControlPrototype()->class[] = 'btn-info';

        $form->addProtection("Selhalo bezpečnostní ověření, pošlete formulář znovu", 120);

        $form->onSuccess[] = array( $this, 'processInvalidateVipToken');
        BootstrapizeForm::bootstrapize( $form );
        return $form;
    }

    public function processInvalidateVipToken( Form $form ) {
        $values = (array) $form->getValues();

        if ( ! $form['yes']->isSubmittedBy()) {
            $this->flashMessage('Operace stornována', 'warning');
            $this->redirect( 'list', array('token'=>$values['token']) );
        }

        $this->registrationModel->invalideVipToken($values['token']);

        $this->flashMessage('Token invalidován', 'success');
        $this->redirect( 'list' );
    }

    private function parseDate($date) {
        $matches = array();
        if( preg_match("/^(\d+)-(\d+)-(\d+) (\d+):(\d+)(?::(\d+))?$/", $date, $matches) && checkdate($matches[2], $matches[3],$matches[1]) ) {
            return new \DateTime( $date );
        }
        else {
            die(var_dump($matches));
            return NULL;
        }
    }

}
