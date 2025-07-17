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

        // Kirim data session DAN path ke view
        $data['session'] = $_SESSION;
        $data['current_path'] = $currentPath;

        // Jika yang login adalah Admin, ambil data statistik
        if ($_SESSION['user_role'] === 'Admin') {
            $data['stats'] = [
                'total_materials' => $this->db->count('tbl_materials', ['archived' => 0]),
                'total_users' => $this->db->count('tbl_users', ['role' => 'Pengguna Umum', 'archived' => 0]),
                'total_feedbacks' => $this->db->count('tbl_feedbacks', ['archived' => 0])
            ];

            // AMBIL 5 MATERI TERBARU (tambahkan 'type')
            $data['recent_materials'] = $this->db->select(
                'tbl_materials',
                ['id', 'title', 'type'], // <-- Tambahkan 'type' di sini
                ['archived' => 0, 'ORDER' => ['create_time' => 'DESC'], 'LIMIT' => 5]
            );

            // AMBIL 5 FEEDBACK TERBARU (tambahkan judul materi)
            $data['recent_feedbacks'] = $this->db->select('tbl_feedbacks', [
                '[><]tbl_users' => ['user_id' => 'id'],
                '[><]tbl_materials' => ['material_id' => 'id'] // <-- Tambahkan JOIN ini
            ], [
                'tbl_feedbacks.feedback_text',
                'tbl_users.name(user_name)',
                'tbl_materials.title(material_title)' // <-- Tambahkan judul materi
            ], [
                'tbl_feedbacks.archived' => 0,
                'ORDER' => ['tbl_feedbacks.create_time' => 'DESC'],
                'LIMIT' => 5
            ]);
        }
        // Jika yang login BUKAN Admin, ambil materi yang ditugaskan
        else {
            $userId = $_SESSION['user_id'];

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
