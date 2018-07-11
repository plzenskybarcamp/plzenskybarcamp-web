<?php

namespace App\Presenters;

use App\Model\WebDir;
use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /**
     * @var WebDir
     */
    private $webDir;


    /**
     *
     */
    public function beforeRender()
    {
        $this->template->wwwDir = $this->webDir->getPath();
    }


    /**
     * @param WebDir $webDir
     */
    public function injectImages(WebDir $webDir)
    {
        $this->webDir = $webDir;
    }
}
