<?php

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class HomeController
{
    private $view;

    public function __construct(Environment $view)
    {
        $this->view = $view;
    }

    // Method untuk menampilkan landing page
    public function index(Request $request, Response $response): Response
    {
        // Cek jika user SUDAH login, langsung arahkan ke dashboard
        if (isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $body = $this->view->render('landing-page.twig');
        $response->getBody()->write($body);
        return $response;
    }
}
