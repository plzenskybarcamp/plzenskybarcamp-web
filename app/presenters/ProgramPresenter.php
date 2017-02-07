<?php

namespace App\Presenters;


/**
 * Homepage presenter.
 */
class ProgramPresenter extends BasePresenter
{

	public function startup() {
		parent::startup();
		$this->flashMessage('Promiň, přednášky zatím nemám připravené.');
		$this->redirect(302, 'Homepage:');

	}

	public function renderList( )
	{

	}

}
