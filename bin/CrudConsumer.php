<?php declare(strict_types = 1);
namespace noxkiwi\crud;

use noxkiwi\crud\Consumer\CrudConsumer;

require_once dirname(__FILE__, 5) . '/bootstrap.php';


define('NK_ENVIRONMENT', '/home/phpkiwi/_conf/nox.kiwi/' . Path::CONFIG_ENVIRONMENT);
App::getInstance();
$consumer = new CrudConsumer('Mailer');
$consumer->run();

