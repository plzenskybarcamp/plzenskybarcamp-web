<?php

namespace App\Presenters;

use Nette,
	App\Model,
	App\Components\Registration\Main;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{

	}

	public function createComponentRegistration( $name ) {
		return new Main( $this, $name );
	}

}
