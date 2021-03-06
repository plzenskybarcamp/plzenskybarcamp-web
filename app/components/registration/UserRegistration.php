<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control,
	Nette\Application\UI\Form,
	MongoDB\Model\UTCDateTimeConverter,
	MongoDB\Model\MongoDbSanitizer;
use Nette\Utils\Html;

class UserRegistration extends Control {

	/** var Nette\Application\UI\Form **/
	private $form;

	/** App\Model\Registration **/
	private $registrationModel;

	private $token;

	private $sns;

	public function __construct( $parent, $name, $registrationModel, $token = NULL, $sns = NULL ) {
		parent::__construct( $parent, $name );
		$this->registrationModel = $registrationModel;
		$this->token = $token;
		$this->sns = $sns;
	}

	public function render() {
		$this->template->setFile( __DIR__ . '/templates/userRegistration.latte' );
		$this->template->render();
	}

	public function createComponentForm( $name ) {
		$form = new Form( $this, $name );
		$form->setRenderer( new \App\Components\CustomFormRenderer );
		$form = $this->addUsersFields( $form );

		$identity = $this->getPresenter()->getUser()->getIdentity();
		$dafaultValues = array(
			'name' => $identity->name,
			'email' => $identity->email
		);
		if( $identity->current_platform == 'tw' ) {
			$dafaultValues['twitter'] = '@'.$identity->platforms['tw']['screen_name'];
		}
		$form->setDefaults( $dafaultValues );

		$form->addSubmit( 'submit', 'Odeslat registraci' );
		$form->onSuccess[] = array( $this, 'processRegistration' );

		$form['submit']->getControlPrototype()->class('track-click')->id = 'submit-user-registration';

		return $form;
	}

	public function addUsersFields( $container ) {

        /** @var Form $container */
        $container->addText( 'name', 'Jméno a příjmení' )
			->addRule(Form::FILLED, 'Jméno musí být vyplněno')
			->setOption('description', 'Tvoje jméno a profilovka ze sociální sítě může být veřejně viditelná v seznamu účastníků');
		$container->addText( 'twitter', 'Twitter' )
			->setAttribute('placeholder', '@TvujTwitter');
		$container->addText( 'email', 'E-mail')
			->addRule(Form::EMAIL, 'Tenhle e-mail nevypadá jako e-mail, zkuste se na to podívat')
			->setRequired( 'E-mail musí být vyplněn' )
			->setAttribute('placeholder', 'email@example.com')
			->setOption('description', 'Email nebude nikde zveřejněn');
		$container->addTextArea( 'bio', 'Bio – aneb napiš nám pár slov o sobě' )
			->setOption('description', 'Bio je veřejně viditelné v seznamu účastníků')
			->addRule(Form::FILLED, 'Prosím, nenechávej Bio prázdné a napiš nám o sobě něco.');
		$container->addCheckbox( 'lunch', 'Mám zájem o oběd (cca 130 Kč)' );
		$container->addCheckbox( 'afterparty', 'Zúčastním se afterparty v centru Plzně' );
		$container->addCheckbox( 'allow_newsletter', 'Chci dostávat informační e-maily ohledně tohoto i následujících ročníků (např.: připomenutí před akcí, informace o partnerech.)' )
			->setDefaultValue( TRUE );
		$container->addCheckbox( 'allow_publish', 'Souhlasím se zveřejněním mého jména a fotografie na seznamu účastníků.' )
			->setDefaultValue( TRUE );
		$container->addCheckbox(
		    'consens',
            Html::el()
                ->addText('Souhlasím se sběrem os. údajů (')
                ->addHtml(Html::el('a')->href("https://www.plzenskybarcamp.cz/privacy-policy")->addText('Zásady ochrany osobních údajů'))
                ->addText(')')
        )->setRequired('Potřebujeme Tvůj souhlas pro zpracování osobních údajů. Bez toho to bohužel nejde.');

		return $container;
	}

	public function processRegistration( Form $form ) {
		$values = (array) $form->getValues();
		$token = $this->token;

		if($token) {
			try {
				$this->registrationModel->validateVipToken( $token );
			}
			catch (\App\Model\InvalidTokenException $e) {
				$form->addError("Váš VIP token je neplatný, požádejte si o nový.");
				return;
			}
		}


		$user = $this->getPresenter()->getUser();
		$values['created_date'] = (new UTCDateTimeConverter())->toMongo();
		$values['picture_url'] = $user->getIdentity()->picture_url;
		$values['identity'] = $user->getIdentity()->data;
		$values['vip_token'] = $this->token;
		$this->registrationModel->updateConferree( $user->getId(), $values );
		if($this->sns) {
			$this->sns->publish(["registration"=>$values]);
		}

		if($this->token) {
			$this->registrationModel->invalideVipToken($token);
			$session = $this->getPresenter()->getContext()->getService("session")->getSection("vip")->remove();
		}

		$conferee = $this->registrationModel->findCoferree( $user->getId() );
		$user->getIdentity()->conferee = MongoDbSanitizer::sanitizeDocument( $conferee );
	}

}