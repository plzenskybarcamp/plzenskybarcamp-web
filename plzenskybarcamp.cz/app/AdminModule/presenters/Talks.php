<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
    Nette\Application\UI\Form,
    App\Components\BootstrapizeForm;


  class TalksPresenter extends BasePresenter
{

    private $registrationModel;

    public function __construct( Model\Registration $registrationModel ) {
        $this->registrationModel = $registrationModel;
    }

    public function renderList( ) {
        $this->template->talks = $this->registrationModel->getTalks();
    }
   

}
