<?php

namespace App\CliModule\Presenters;

use Nette,
	Nette\Diagnostics\Debugger,
	App\Model,
	Nette\Application\Responses\TextResponse,
	App\OAuth\Twitter;

class UserThumbnailsPresenter extends Nette\Application\UI\Presenter
{
	private $registrationModel;
	private $twitter;

	public function __construct( Model\Registration $registrationModel, Twitter $twitter ) {
		$this->registrationModel = $registrationModel;
		$this->twitter = $twitter;
	}

	public function renderFixTwitter() {
		$this->twitter->singleUserMode();

		$users = $this->registrationModel->getFilteredConferrees(array('picture_mirror'=>FALSE, 'identity.platforms.tw.id'=>array('$ne'=>NULL)));
		foreach ($users as $value) {
			$name = $value['identity']['platforms']['tw']['screen_name'];
			$data = $this->twitter->get('users/show', array('screen_name'=>$name, 'include_entities'=>false));
			if(isset($data->errors)) {
				echo "Invalid api for $name\n";
				die();
			}
			die($data->profile_image_url_https);
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
				$token = base64_encode(dechex(crc32(uniqid())));
				$url = $this->getContext()->getService('s3')->putObject($file, "2015/pictures/profiles/tw-profile-$name-$token");

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
		echo "($type) ";
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
