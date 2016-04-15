<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
    Nette\Application\UI\Form,
    App\Components\BootstrapizeForm,
    MongoDB\Model\UTCDateTimeConverter;


  class UsersPresenter extends BasePresenter
{

    private $registrationModel;

    public function __construct( Model\Registration $registrationModel ) {
        $this->registrationModel = $registrationModel;
    }

    public function renderList( ) {
        $this->template->registerHelper('mongoFormat', array( 'App\Components\Helpers', 'mongoFormat'));
        $this->template->users = $this->registrationModel->getConferrees();
    }
    public function actionCsv( ) {
        $users = $this->registrationModel->getConferrees();

        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array("E-mail", "Jméno", "Příjmení", "Registrace", "Oběd", "Afterparty", "Bio"), ",", '"');
        foreach ($users as $user) {
            $names = explode(" ", $user['name'], 2);
            $fname = $names[0];
            $lname = (isset($names[1])?$names[1]:"");
            @fputcsv($df, array(
                $user['email'],
                $fname,
                $lname,
                ( $user['created_date'] ? (new UTCDateTimeConverter($user['created_date']))->format('Y-m-d H:i:s') : NULL ),
                ( $user['lunch'] ? "Ano" : "Ne"),
                ( $user['afterparty'] ? "Ano" : "Ne"),
                ( $user['bio'] ),
            ), ",", '"');
        }
        fclose($df);
        $csv = ob_get_clean();

        $now = gmdate("D, d M Y H:i:s");
        $fileDatePostfix = gmdate("Ymd.his");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Length: " . strlen($csv));

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename=users-$fileDatePostfix.csv");
        header("Content-Transfer-Encoding: binary");
        echo $csv;
        $this->terminate();
    }

}
