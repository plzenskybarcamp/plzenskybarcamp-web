<?php

namespace App\ApiModule\Presenters;

use Nette,
	Nette\Diagnostics\Debugger,
	App\Model,
	Nette\Application\Responses\JsonResponse;

class LogPresenter extends Nette\Application\UI\Presenter
{

	public function renderXss($get) {
		$request_body = file_get_contents('php://input');
		$body = NULL;
		try{
			if($request_body) {
				$body = Nette\Utils\Json::decode( $request_body);
			}
		}
		catch( Nette\Utils\JsonException $e ) {}

		$log = array(
			'errorType' => 'XSS report',
			'date'=>date('Y-m-d H:i:s'),
			'timestamp'=>time(),
			'report'=>$body,
			'server'=> $this->itemsFrom( $_SERVER , array("HTTP_USER_AGENT", "HTTP_COOKIE", "REMOTE_ADDR")),
		);
		Debugger::log(json_encode($log), Debugger::ERROR);

		$this->sendSuccessResponse();
	}

	public function renderJsError() {

		$log = array(
			'errorType' => 'JS Error',
			'date'=>date('Y-m-d H:i:s'),
			'timestamp'=>time(),
			'post'=>$_POST,
			'server'=> $this->itemsFrom( $_SERVER , array("HTTP_USER_AGENT", "HTTP_COOKIE", "REMOTE_ADDR")),
		);
		Debugger::log(json_encode($log), Debugger::ERROR);

		$this->sendSuccessResponse();
	}

	private function itemsFrom( $var, array $names ) {
		$buffer = array();
		foreach ($names as $name) {
			$buffer[$name] = $this->getItem($var, $name);
		}
		return $buffer;
	}

	private function getItem($var, $name) {
		if(isset($var[$name])) {
			return $var[$name];
		}
		return NULL;
	}

	private function sendSuccessResponse() {
		$output = array(
			'success'=>true,
			'logged_time'=>time()
		);
		$this->getPresenter()->sendResponse( new JsonResponse( $output ) );
	}
}
