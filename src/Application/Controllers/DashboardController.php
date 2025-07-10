<?php

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use Medoo\Medoo;

class DashboardController
{
    private $view;
    private $db;

    public function __construct(Environment $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    public function showDashboard(Request $request, Response $response): Response
    {
        // Cek apakah user sudah login atau belum
        if (!isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        // Kirim data session ke view agar bisa diakses di template
        $data['session'] = $_SESSION;

        $body = $this->view->render('dashboard.twig', $data);
        $response->getBody()->write($body);
        return $response;
    }
}
