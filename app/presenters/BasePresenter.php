<?php

declare(strict_types=1);

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
    public function beforeRender(): void
    {
        $this->template->wwwDir = $this->webDir->getPath();
    }


    /**
     * @param WebDir $webDir
     */
    public function injectWebdir(WebDir $webDir): void
    {
        $this->webDir = $webDir;
    }
}
