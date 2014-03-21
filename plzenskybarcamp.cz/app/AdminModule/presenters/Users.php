<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
    Nette\Application\UI\Form,
    App\Components\BootstrapizeForm;


  class UsersPresenter extends BasePresenter
{

    private $registrationModel;

    public function __construct( Model\Registration $registrationModel ) {
        $this->registrationModel = $registrationModel;
    }

    public function renderList( ) {
        $this->template->users = $this->registrationModel->getConferrees();
    }
    public function actionCsv( ) {
        $users = $this->registrationModel->getConferrees();

        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array("Jméno", "E-mail", "Registrace"), ";", '"');
        foreach ($users as $user) {
            @fputcsv($df, array(
                $user['name'],
                $user['email'],
                ( $user['created_date'] ? date( 'Y-m-d H:i:s',  $user['created_date']->sec) : NULL )
            ), ";", '"');
        }
        fclose($df);
        $csv = ob_get_clean();

        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Length: " . strlen($csv));

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename=users.csv");
        header("Content-Transfer-Encoding: binary");
        echo $csv;
        $this->terminate();
    }

}
