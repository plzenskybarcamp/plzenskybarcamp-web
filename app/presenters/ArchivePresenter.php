<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\ArchiveLoader;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\TextResponse;


/**
 * Class ArchivePresenter
 * @package App\Presenters
 */
class ArchivePresenter extends BasePresenter
{
    /**
     * @var ArchiveLoader
     */
    private $loader;


    /**
     * ArchivePresenter constructor.
     * @param ArchiveLoader $loader
     */
    public function __construct(ArchiveLoader $loader)
    {
        parent::__construct();
        $this->loader = $loader;
    }


    /**
     * @param string $year
     * @param string $path
     * @throws BadRequestException
     */
    public function renderDefault(string $year, string $path = ''): void
    {
        $path = $this->preparePath($path, $year);
        $output = $this->loader->load($path);
        if ($output['status'] !== 200) {
            $this->error('Cannot load archived page ' . $path, $output['status']);
        }
        $this->sendResponse(new TextResponse($output['content']));
    }


    /**
     * @param string $path
     * @param string $year
     * @return string
     */
    private function preparePath(string $path, string $year): string
    {
        return "/$year" . rtrim("/$path", '/') . '.html';
    }
}
