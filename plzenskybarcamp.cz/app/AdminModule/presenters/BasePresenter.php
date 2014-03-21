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
	}

	private function isAdmin( $id ) {
		return in_array($id, array(
			'JB' => 1296988124,
			'Kollda' => 1011669265,
            'anton' =>100001297429314,
            'MARUSKA' =>1551789255,
            'endis' => 100000241892661,
            'verča' => 1271843661
		));
	}



}
