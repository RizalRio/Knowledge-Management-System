<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

use App\Application\Controllers\AuthController;
use App\Application\Controllers\DashboardController;
use App\Application\Controllers\MaterialController;
use App\Application\Controllers\FeedbackController;
use App\Application\Controllers\UserController;
use App\Application\Controllers\HomeController;
use App\Application\Controllers\CategoryController;
use App\Application\Controllers\TagController;
use App\Application\Controllers\ProfileController;
use App\Application\Controllers\SearchController;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', HomeController::class . ':index');

    $app->group('/users', function (Group $group) {
        $group->get('', UserController::class . ':index');
        $group->get('/api', UserController::class . ':data');
        $group->get('/create', UserController::class . ':create');
        $group->post('', UserController::class . ':store');
        $group->get('/edit/{id}', UserController::class . ':edit');
        $group->post('/update/{id}', UserController::class . ':update');
        $group->post('/delete/{id}', UserController::class . ':delete');
    });

    $app->get('/login', AuthController::class . ':showLoginForm');
    $app->post('/login', AuthController::class . ':login');

    $app->get('/register', AuthController::class . ':showRegisterForm');
    $app->post('/register', AuthController::class . ':register');

    // Route untuk Dashboard
    $app->get('/dashboard', DashboardController::class . ':showDashboard');

    // Route untuk Materi (khusus Admin)
    $app->get('/materials', MaterialController::class . ':index');
    $app->get('/materials/create', MaterialController::class . ':create'); // <-- TAMBAHKAN INI
    $app->post('/materials', MaterialController::class . ':store');     // <-- TAMBAHKAN INI
    $app->post('/materials/delete/{id}', MaterialController::class . ':delete');
    $app->get('/materials/edit/{id}', MaterialController::class . ':edit'); // <-- TAMBAHKAN INI
    $app->post('/materials/update/{id}', MaterialController::class . ':update'); // <-- TAMBAHKAN INI
    $app->get('/materials/assign/{id}', MaterialController::class . ':showAssignForm'); // <-- TAMBAHKAN INI
    $app->post('/materials/assign/{id}', MaterialController::class . ':storeAssignment'); // <-- TAMBAHKAN INI
    $app->get('/view-material/{id}', MaterialController::class . ':viewMaterial');
    $app->get('/api/materials', MaterialController::class . ':data');

    // Route untuk Feedbacks
    $app->get('/feedbacks', FeedbackController::class . ':index');
    $app->get('/api/feedbacks', FeedbackController::class . ':data');
    $app->get('/feedbacks/assess/{id}', FeedbackController::class . ':showAssessForm');
    $app->post('/feedbacks/assess/save/{id}', FeedbackController::class . ':storeAssessment');
    $app->post('/feedbacks/delete', FeedbackController::class . ':delete');
    $app->post('/feedback/store/{id}', FeedbackController::class . ':store');

    $app->get('/categories', CategoryController::class . ':index');
    $app->get('/api/categories', CategoryController::class . ':data');
    $app->get('/api/category/{id}', CategoryController::class . ':getById'); // <-- TAMBAH
    $app->post('/categories', CategoryController::class . ':store'); // <-- TAMBAH
    $app->post('/categories/update/{id}', CategoryController::class . ':update'); // <-- TAMBAH
    $app->post('/categories/delete/{id}', CategoryController::class . ':delete'); // <-- TAMBAH

    $app->get('/tags', TagController::class . ':index');
    $app->get('/api/tags', TagController::class . ':data');
    $app->get('/api/tag/{id}', TagController::class . ':getById');
    $app->post('/tags', TagController::class . ':store');
    $app->post('/tags/update/{id}', TagController::class . ':update');
    $app->post('/tags/delete/{id}', TagController::class . ':delete');

    // Route untuk Profil
    $app->get('/profile', ProfileController::class . ':index');
    $app->post('/profile/update', ProfileController::class . ':updateProfile');
    $app->post('/profile/password', ProfileController::class . ':updatePassword');

    // Route untuk Pencarian
    $app->get('/search', SearchController::class . ':results');

    // Route untuk Logout
    $app->get('/logout', function ($request, $response) {
        session_destroy();
        return $response->withHeader('Location', '/login')->withStatus(302);
    });
};
