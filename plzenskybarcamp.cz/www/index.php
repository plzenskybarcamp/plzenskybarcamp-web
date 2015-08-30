<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';

$container = require __DIR__ . '/../app/bootstrap.php';

function replacecdn($buffer)
{
  // replace all the apples with oranges
  return (str_replace(
  		[
  			'd32gpbkjyl921a.cloudfront.net',
  			'href="/css/',
  			'src="/media/',
  			'src="/js/',
  			'src="/images/',
  		],
  		[
  			'1009227059.rsc.cdn77.org',
  			'href="https://1166919629.rsc.cdn77.org/css/',
  			'src="https://1166919629.rsc.cdn77.org/media/',
  			'src="https://1166919629.rsc.cdn77.org/js/',
  			'src="https://1166919629.rsc.cdn77.org/images/',
  		], $buffer));
}

$amazon = isset($_GET['cdn']) && $_GET['cdn'] == 'amazon';

if(!$amazon) {
	ob_start("replacecdn");
}

$container->getService('application')->run();

if(!$amazon) {
	ob_end_flush();
}
