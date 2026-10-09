<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Prague');

// Forms reject submissions that don't look like same-origin browser requests (CSRF protection
// in Nette\Application\UI\Form), so tests pretend to be one. Must be set before the
// container creates the HTTP request.
$_SERVER['HTTP_SEC_FETCH_SITE'] = 'same-origin';
