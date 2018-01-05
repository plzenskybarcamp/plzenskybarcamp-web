<?php

namespace App\Presenters;


/**
 * Homepage presenter.
 */
class ProgramPresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();
        $this->flashMessage('Omlouváme se, program ještě není připraven');
        $this->redirect(301, ':Homepage:default');
    }


    public function renderList( )
	{

	}

}
