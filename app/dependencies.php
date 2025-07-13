<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Medoo\Medoo;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use App\Application\Controllers\AuthController;
use App\Application\Controllers\DashboardController;
use App\Application\Controllers\MaterialController;
use App\Application\Controllers\FeedbackController;
use App\Application\Controllers\UserController;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        'db' => function () {
            return new Medoo([
                'database_type' => 'mysql',
                'database_name' => 'db_kms',
                'server' => 'localhost',
                'username' => 'root',
                'password' => ''
            ]);
        },
        'view' => function () {
            // Pastikan folder 'views' ada di root direktori
            $loader = new FilesystemLoader(__DIR__ . '/../views');
            return new Environment($loader, [
                'cache' => false,
            ]);
        },
        AuthController::class => function (ContainerInterface $c) {
            // Ambil 'view' dan 'db' dari container
            $view = $c->get('view');
            $db = $c->get('db');

            // Buat AuthController dengan DUA dependency
            return new AuthController($view, $db);
        },
        DashboardController::class => function (ContainerInterface $c) {
            $view = $c->get('view');
            $db = $c->get('db');
            return new DashboardController($view, $db);
        },
        MaterialController::class => function (ContainerInterface $c) {
            $view = $c->get('view');
            $db = $c->get('db');
            return new MaterialController($view, $db);
        },
        FeedbackController::class => function (ContainerInterface $c) {
            // Ambil 'view' dan 'db' yang sudah terdaftar
            $view = $c->get('view');
            $db = $c->get('db');

            // Buat FeedbackController dengan dependency yang benar
            return new FeedbackController($view, $db);
        },
        UserController::class => function (ContainerInterface $c) {
            // Ambil 'view' dan 'db' dari container
            $view = $c->get('view');
            $db = $c->get('db');

            // Buat UserController dengan DUA dependency
            return new UserController($view, $db);
        },
    ]);
};
