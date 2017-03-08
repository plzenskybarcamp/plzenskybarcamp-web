<?php

namespace App\ApiModule\Presenters;

use Nette,
	App\Model,
	Nette\Http,
	Nette\Application\Responses\JsonResponse;

class UserPresenter extends Nette\Application\UI\Presenter
{

	private $registrationModel;
	private $bearerTokenModel;
	private $httpRequest;
	private $httpResponse;

	public function __construct( Model\Registration $registrationModel, Model\BearerToken $bearerTokenModel,
				Http\IRequest $httpRequest, Http\IResponse $httpResponse ) {

		$this->registrationModel = $registrationModel;
		$this->bearerTokenModel = $bearerTokenModel;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
	}

	public function actionPictureUrl( $userId, $pictureUrl ) {
		if( ! $this->httpRequest->isMethod('PUT')) {
			$function = __METHOD__;
			$this->sendErrorResponse("PUT method required", 405);
		}

		$authorizationHeader = $this->httpRequest->getHeader('Authorization');
		if( ! preg_match( '/Bearer\s+(.+)/i', $authorizationHeader, $matches ) ) {
			$this->sendErrorResponse("Missing authorization header", 403);
		}

		$authorizationToken = $matches[1];
		try {
			$this->bearerTokenModel->validateToken( $authorizationToken );
		}
		catch ( Model\InvalidTokenException $e ) {
			$reason = $e->getMessage();
			$this->sendErrorResponse("Authorization failed ($reason)", 403);
		}

		$userId = $this->httpRequest->getPost('user_id');
		if(! $this->registrationModel->isId($userId)) {
			$this->sendErrorResponse("Invalid user_id ($userId)");
		}

		$pictureUrl = $this->httpRequest->getPost('pictrute_url');
		if(! Nette\Utils\Validators::isUrl($pictureUrl)) {
			$this->sendErrorResponse("Invalid pictrute_url");
		}

		$this->sendSuccessResponse();
	}

	private function sendSuccessResponse( $data = [] ) {
		$data += array(
			'success' => TRUE,
		);
		$this->getPresenter()->sendResponse( new JsonResponse( $data ) );
		throw new Nette\Application\AbortException( $message );
	}

	private function sendErrorResponse( $message, $code = 400 ) {
		$this->httpResponse->setCode( $code );
		$data = array(
			'success' => FALSE,
			'message' => $message,
		);
		$this->getPresenter()->sendResponse( new JsonResponse( $data ) );
		throw new Nette\Application\AbortException( $message );
	}
}
