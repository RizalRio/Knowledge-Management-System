<?php

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use Medoo\Medoo;

class FeedbackController
{
    private $view;
    private $db;

    public function __construct(Environment $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    // Method untuk menyimpan feedback baru dari Karyawan
    public function store(Request $request, Response $response, array $args): Response
    {
        // Cek otorisasi dasar
        if (!isset($_SESSION['user_id'])) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Anda harus login untuk memberi feedback.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $materialId = $args['id'];
        $userId = $_SESSION['user_id'];
        $data = $request->getParsedBody();
        $feedbackText = $data['feedback_text'];

        // Cek keamanan lagi, pastikan user ini memang ditugaskan materi ini
        $isAssigned = $this->db->has('tbl_material_assignments', [
            'AND' => ['material_id' => $materialId, 'user_id' => $userId]
        ]);

        if (!$isAssigned && $_SESSION['user_role'] !== 'Admin') {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Anda tidak berhak memberi feedback untuk materi ini.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // Simpan feedback ke database
        $this->db->insert('tbl_feedbacks', [
            'material_id' => $materialId,
            'user_id' => $userId,
            'feedback_text' => $feedbackText,
            'create_id' => $userId // Yang membuat feedback adalah user itu sendiri
        ]);

        $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Feedback Anda telah terkirim. Terima kasih!']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
