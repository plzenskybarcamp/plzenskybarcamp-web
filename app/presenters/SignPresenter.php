<?php

namespace App\Presenters;

use App\Components\Registration\Identity;
use App\Model\Config;
use App\Model\Registration;
use App\OAuth\AuthenticationException;
use App\OAuth\Exception as OAuthException;
use App\OAuth\Facebook;
use App\OAuth\Twitter;
use Facebook\FacebookAuthorizationException;
use MongoDB\Model\MongoDbSanitizer;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

    private $facebook;
    private $twitter;
    private $registration;
    private $config;


    public function __construct(Facebook $facebook, Twitter $twitter, Registration $registration, Config $config)
    {
        parent::__construct();

        $this->facebook = $facebook;
        $this->twitter = $twitter;
        $this->registration = $registration;
        $this->config = $config;
    }


    public function actionInFb(): void
    {
        $redirectUrl = $this->link('//processFb');
        $this->redirectUrl($this->facebook->getAuthUrl($redirectUrl, array('email')));
    }


    public function actionProcessFb(): void
    {
        try {
            $oAuthIdentity = $this->facebook->getIdentity();
        } catch (AuthenticationException $e) {
            $this->flashMessage('Omlouváme se, ale tvoje přihlášení se nepovedlo. Zkus to znovu, nebo nám dej vědět.',
                'error');
            $this->flashMessage(['event' => 'flash-message', 'action' => 'login-fail', 'label' => $e->getMessage()],
                'dataLayer-push');
            $this->redirect('in');
        } catch (FacebookAuthorizationException $e) {
            $this->flashMessage('Omlouváme se, ale tvoje přihlášení se nepovedlo. Zkus to znovu, nebo nám dej vědět.',
                'error');
            $this->flashMessage(['event' => 'flash-message', 'action' => 'login-fail', 'label' => $e->getMessage()],
                'dataLayer-push');
            $this->redirect('in');
        }

        /** @noinspection PhpUndefinedVariableInspection */
        $conferee = $this->getUserRegistration('fb', $oAuthIdentity->plartformId);
        $profile = $this->getUserIdentity($conferee);

        if (!$profile) {
            $id = hash('crc32b', uniqid('fb', true));

            $profile = $oAuthIdentity->toArray();
            $profile['id'] = $id;
        }

        $this->finishSignIn($conferee, $profile);
    }


    public function actionInTw(): void
    {
        $this->redirectUrl($this->twitter->getAuthUrl(
            $this->link('//processTw')
        ));
    }


    public function actionProcessTw($oauth_token, $oauth_verifier): void
    {

        try {
            $this->twitter->verifyAuthentication($oauth_token, $oauth_verifier);
            $oAuthIdentity = $this->twitter->getIdentity();
        } catch (OAuthException $e) {
            $this->flashMessage('Omlouváme se, ale tvoje přihlášení se nepovedlo. Zkus to znovu, nebo nám dej vědět.',
                'error');
            $this->flashMessage(['event' => 'flash-message', 'action' => 'login-fail', 'label' => $e->getMessage()],
                'dataLayer-push');
            $this->redirect('in');
        }

        /** @noinspection PhpUndefinedVariableInspection */
        $conferee = $this->getUserRegistration('tw', $oAuthIdentity->plartformId);
        $profile = $this->getUserIdentity($conferee);

        if (!$profile) {
            $id = hash('crc32b', uniqid('fb', true));

            $profile = $oAuthIdentity->toArray();
            $profile['id'] = $id;
        }

        $this->finishSignIn($conferee, $profile);
    }


    private function finishSignIn($conferee, $profile): void
    {
        $identity = new Identity($profile['id'], null, $profile);
        $this->user->login($identity);

        $eventLabel = '';
        if ($conferee) {
            $identity = $this->user->identity;
            $identity->conferee = $conferee;
            $identity->talk = $this->getUserTalk($conferee);
            $this->flashMessage('Vítej zpět, ty jsi už registrovaný, tešíme se na Tebe v Plzni', 'success');
            $eventLabel = 'registered';
        } elseif ($this->config->getConfig('isRegistrationOpen', false)) {
            $this->flashMessage('Yep. Pro účast se nezapomeň ještě registrovat tlačítkem „Potvrzuji svou účast“',
                'success');
            $eventLabel = 'ready-to-register';
        } else {
            $this->flashMessage('Jsi přihlášen, ale registrace na Barcamp ještě nejsou otevřeny, vydrž :)', 'success');
            $eventLabel = 'registration-closed';
        }
        $this->flashMessage(['event' => 'flash-message', 'action' => 'login-success', 'label' => $eventLabel],
            'dataLayer-push');

        $this->redirect('Homepage:default');
    }


    private function getUserRegistration($platform, $id)
    {
        $conferee = $this->registration->findCoferreeByPlatform($platform, $id);

        if (isset($conferee['identity'])) {
            return MongoDbSanitizer::sanitizeDocument($conferee);
        }

        return null;
    }


    private function getUserIdentity($conferee)
    {
        if (isset($conferee['identity'])) {
            return $conferee['identity'];
        }

        return null;
    }


    private function getUserTalk($conferee)
    {
        if (isset($conferee['talk'])) {
            return $conferee['talk'];
        }

        return null;
    }


    public function actionOut(): void
    {
        $this->getUser()->logout(true);
        $this->flashMessage('Jsi odhlášen');
        $this->flashMessage(['event' => 'flash-message', 'action' => 'logout-success'], "dataLayer-push");
        $this->redirect('Homepage:default');
    }

}
