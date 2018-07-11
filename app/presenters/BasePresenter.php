<?php

namespace App\Presenters;

use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    public function beforeRender()
    {
        $parameters = $this->context->getParameters();
        $this->template->wwwDir = $parameters['wwwDir'];

        $this->template->isDevelop = $this->getContext()->getService("developFlag")->isDevelop();
    }

}
