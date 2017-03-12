<?php

namespace App\CliModule\Presenters;

use Nette,
	Nette\Utils\Random,
	Nette\Diagnostics\Debugger,
	App\Model,
	Nette\Application\Responses\TextResponse,
	App\Aws\S3Object,
	App\OAuth\Twitter,
	App\OAuth\Facebook,
	Facebook\FacebookSession,
	Facebook\FacebookRequest;

class UserThumbnailsPresenter extends Nette\Application\UI\Presenter
{
	private $registrationModel;
	private $twitter;
	private $facebook;

	public function __construct( Model\Registration $registrationModel, Twitter $twitter, Facebook $facebook ) {
		$this->registrationModel = $registrationModel;
		$this->twitter = $twitter;
		$this->facebook = $facebook;
	}

	public function renderMoveProfileImages() {
		$s3 = $this->getContext()->getService('s3');

		$list = $s3->listObjects('2015/pictures/profiles/')->getObjects();


		foreach( $list as $item ) {
			$key = $item['Key'];
			$path = $s3->path2Key($item['Key']);
			$object = $s3->headObject($path);

			$copyOpbject = new S3Object();
			$copyOpbject->ContentType = $object->ContentType;
			$copyOpbject->setCacheControl('+ 1 year');
			$copyOpbject->MetadataDirective = 'REPLACE';
			foreach ($object->Metadata as $key => $value) {
				$copyOpbject->addMetadata($key, $value);
			}
			echo $s3->copyObject( $copyOpbject, $path, "public/$path") . "\n";
		}
	}


	public function renderFixFacebook() {
		$accessToken = $this->facebook->getAppToken();
		$session = new FacebookSession($accessToken);

		$users = $this->registrationModel->getFilteredConferrees(array('picture_mirror'=>array('$exists'=>FALSE), 'identity.platforms.fb.id'=>array('$exists'=>TRUE)));
		foreach ($users as $value) {
			$id = $value['identity']['platforms']['fb']['id'];
			$response = (new FacebookRequest(
				$session, 'GET', '/' . $id . '/picture', array(
				'width' => 200,
				'height'=> 200,
				'redirect'=>false
			)
			))->execute()->getGraphObject();
			$url = $response->getProperty('url');
			if(!$url){
				echo "Invalid image for $id\n";
				die();
			}
			$image = $this->tryDown($url);
			if($image) {
				$file = \App\Aws\S3Object::createFromString( $image['body'], $image['type'])
					->setCacheControl('+ 1 year')
					->addMetadata('Origin-Url',$url);
				$token = Random::generate('12');
				$url = $this->getContext()->getService('s3')->putObject($file, "public/2017/pictures/profiles/fb-profile-$id-$token");

				if($url) {
					$this->registrationModel->updateConferree( $value['_id'], array('picture_url' => $url, 'picture_mirror'=>TRUE) );
				}
				echo "Saved: $url\n";
			}
			else {
				echo "Invalid image for $id\n";
			}
		}
	}


	public function renderFixTwitter() {
		$this->twitter->singleUserMode();

		$users = $this->registrationModel->getFilteredConferrees(array('picture_mirror'=>array('$exists'=>FALSE), 'identity.platforms.tw.id'=>array('$exists'=>TRUE)));
		foreach ($users as $value) {
			$name = $value['identity']['platforms']['tw']['screen_name'];
			$data = $this->twitter->get('users/show', array('screen_name'=>$name, 'include_entities'=>false));
			if(isset($data->errors)) {
				echo "Invalid api for $name\n";
				die();
			}
			$url = preg_replace('~_normal\\.([a-z]+)$~i', ".$1", $data->profile_image_url_https);
			$image = $this->tryDown($url);
			if($image) {
				$imageSmall = $this->resizeImage($image['body'], 200);
				if(!$imageSmall) {
					die("Invalid image: $value[picture_url]\n");
				}
				$file = \App\Aws\S3Object::createFromString( $imageSmall, $image['type'])
					->setCacheControl('+ 1 year')
					->addMetadata('Origin-Url',$url);
				$token = Random::generate('12');
				$urlname = strtolower($name);
				$url = $this->getContext()->getService('s3')->putObject($file, "public/2017/pictures/profiles/tw-profile-$urlname-$token");

				if($url) {
					$this->registrationModel->updateConferree( $value['_id'], array('picture_url' => $url, 'picture_mirror'=>TRUE) );
				}
				echo "Saved: $url\n";
			}
			else {
				echo "Invalid image for $name\n";
			}
		}
	}

	private function resizeImage($data, $width) {
		$filename = __DIR__.'/temp.jpg';
		file_put_contents($filename, $data);
		list($x, $y, $type) = getimagesize($filename);
		if($type == IMG_JPG) {
			$i = imagecreatefromjpeg($filename);
		}
		elseif($type==IMG_PNG || $type==3) {
			$i = imagecreatefrompng($filename);
		}
		elseif($type==IMG_GIF) {
			$i = imagecreatefromgif($filename);
		}
		else {
			return NULL;
		}
		if(!$i) {
			return NULL;
		}
		unlink($filename);

		$tmp = imagecreatetruecolor($width, $width);
		imagecopyresampled($tmp, $i, 0, 0, max(0,($x-$y)/2), max(0,($y-$x)/2), $width, $width, min($x,$y), min($x,$y));

		imagejpeg($tmp, $filename, 95);
		imagedestroy($tmp);
		imagedestroy($i);
		$data = file_get_contents($filename);
		unlink($filename);
		return $data;
	}

	private function tryDown($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FAILONERROR, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$body = curl_exec($ch);
		if(curl_errno($ch)) {
			$en = curl_errno($ch);
			$em = curl_error($ch);
			throw new Exception("cURL Request failed: ($en) $em", 1);
		}
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close($ch);
		if($code == 200) {
			return array('body'=>$body, 'type'=>$type);
		}
		return NULL;
}

	public function afterRender() {
		parent::afterRender();
		$this->sendResponse(new TextResponse("\nDONE\n"));
	}
}
