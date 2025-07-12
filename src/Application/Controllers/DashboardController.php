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
        if (!isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        // Ambil path saat ini dari request
        $currentPath = $request->getUri()->getPath();

        $data['session'] = $_SESSION;
        $data['current_path'] = $currentPath;

        // Jika yang login BUKAN Admin, ambil materi yang ditugaskan
        if ($_SESSION['user_role'] !== 'Admin') {
            $userId = $_SESSION['user_id'];

            // Query dengan JOIN untuk mengambil data materi berdasarkan penugasan
            $data['assigned_materials'] = $this->db->select('tbl_material_assignments', [
                '[><]tbl_materials' => ['material_id' => 'id']
            ], [
                'tbl_materials.id',
                'tbl_materials.title',
                'tbl_materials.description',
                'tbl_materials.type'
            ], [
                'tbl_material_assignments.user_id' => $userId,
                'tbl_materials.archived' => 0
            ]);
        }

        $body = $this->view->render('dashboard.twig', $data);
        $response->getBody()->write($body);
        return $response;
    }
}
