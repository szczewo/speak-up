<?php
declare(strict_types=1);
use Symfony\Component\Dotenv\Dotenv;

/*
 * Register Symfony ErrorHandler early to avoid PHPUnit 11 "risky test" warning
 * about unremoved exception handlers.
 */
use Symfony\Component\ErrorHandler\ErrorHandler;


ErrorHandler::register(null, false);
require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
