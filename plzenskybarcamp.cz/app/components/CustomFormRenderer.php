<?php

namespace App\Components;

use Nette\Forms\Rendering\DefaultFormRenderer;

class CustomFormRenderer extends \Nette\Forms\Rendering\DefaultFormRenderer {

	public $wrappers = array(
		'form' => array(
			'container' => NULL,
		),

		'error' => array(
			'container' => 'ul class=error',
			'item' => 'li',
		),

		'group' => array(
			'container' => NULL,
			'label' => 'h3',
			'description' => 'p',
		),

		'controls' => array(
			'container' => NULL,
		),

		'pair' => array(
			'container' => "div class='inputs-wrap'",
			'.required' => 'required',
			'.optional' => NULL,
			'.odd' => NULL,
			'.error' => NULL,
		),

		'control' => array(
			'container' => NULL,
			'.odd' => NULL,

			'description' => 'span',
			'requiredsuffix' => '',
			'errorcontainer' => 'span class=error',
			'erroritem' => '',

			'.required' => 'required',
			'.text' => 'text',
			'.password' => 'text',
			'.file' => 'text',
			'.submit' => 'button',
			'.image' => 'imagebutton',
			'.button' => 'button',
		),

		'label' => array(
			'container' => NULL,
			'suffix' => NULL,
			'requiredsuffix' => '',
		),

		'hidden' => array(
			'container' => 'div',
		),
	);

}