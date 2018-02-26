<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends \App\Presenters\BasePresenter
{

	public function startup() {

        parent::startup();

        // Redirect user to login page, if is not logged in
        if ( !$this->user->isLoggedIn() ) {
            $this->flashMessage( 'You\'re not logged in', 'error' );
            $this->redirect( ':Homepage:default' );
        }

        $identity = $this->user->getIdentity();

        if ( ! isset( $identity->data['platforms']['fb']['id'] ) || ! $this->isAdmin( $identity->data['platforms']['fb']['id'] ) ) {
            $this->flashMessage( 'You\'re not allowed here', 'error' );
            $this->redirect( ':Homepage:default' );
        }

        //$this->flashMessage( 'Sorry, mám archivaci in-progress a teď se mi nemůžeš hrabat v administraci. Kuba.', 'error' );
        //$this->redirect( ':Homepage:default' );
	}

    public function beforeRender() {
        $parameters = $this->context->getParameters();
        $this->template->wwwDir = $parameters['wwwDir'];

        $this->template->registerHelper('twitterize', array( 'App\Components\Helpers', 'twitterize'));
    }

	private function isAdmin( $id ) {
		return in_array($id, array(
			'JB' => 1296988124,
			'Kollda' => 1011669265,
            'anton' =>100001297429314,
            'MARUSKA' =>1551789255,
            'endis' => 100000241892661,
            'verča' => 1271843661,
            'landy' => 1153781327
		));
	}



}
