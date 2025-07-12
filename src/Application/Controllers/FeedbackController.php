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

    // Menampilkan halaman utama daftar feedback untuk Admin
    public function index(Request $request, Response $response): Response
    {
        // Proteksi: Hanya Admin yang boleh akses
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $body = $this->view->render('feedback/feedback-index.twig', [
            'session' => $_SESSION,
            'current_path' => $request->getUri()->getPath()
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    // Menyediakan data untuk DataTables (API Endpoint)
    public function data(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $feedbacks = $this->db->select('tbl_feedbacks', [
            "[><]tbl_materials" => ["material_id" => "id"],
            "[><]tbl_users" => ["user_id" => "id"]
        ], [
            'tbl_feedbacks.id',
            'tbl_materials.title(material_title)',
            'tbl_users.name(user_name)',
            'tbl_feedbacks.feedback_text',
            'tbl_feedbacks.create_time',
            'tbl_feedbacks.assessment_score' // Untuk cek apakah sudah dinilai
        ], [
            'tbl_feedbacks.archived' => 0,
            'tbl_materials.archived' => 0,
            'tbl_users.archived' => 0
        ]);

        $output = ['data' => $feedbacks];
        $response->getBody()->write(json_encode($output));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
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
        $ratingMaterial = $data['material_rating'];
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
            'material_rating' => $ratingMaterial,
            'feedback_text' => $feedbackText,
            "create_time" => date('Y-m-d H:i:s'),
            'create_id' => $userId // Yang membuat feedback adalah user itu sendiri
        ]);

        $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Feedback Anda telah terkirim. Terima kasih!']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function showAssessForm(Request $request, Response $response, $args): Response
    {
        // Proteksi: Hanya Admin yang boleh akses
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $feedbackId = $args['id'];

        // Ambil data feedback yang akan dinilai
        $feedback = $this->db->get('tbl_feedbacks', [
            "[><]tbl_materials" => ["material_id" => "id"],
            "[><]tbl_users" => ["user_id" => "id"]
        ], [
            'tbl_feedbacks.id',
            'tbl_feedbacks.material_id',
            'tbl_feedbacks.material_rating',
            'tbl_feedbacks.feedback_text',
            'tbl_feedbacks.assessment_score',
            'tbl_feedbacks.assessment_notes',
            'tbl_materials.title(material_title)',
            'tbl_materials.type(material_type)',
            'tbl_materials.content(material_content)',
            'tbl_users.name(user_name)'
        ], [
            'tbl_feedbacks.id' => $feedbackId
        ]);

        if (!$feedback) {
            // Jika feedback tidak ditemukan, kembali ke halaman daftar
            return $response->withHeader('Location', '/feedbacks')->withStatus(404);
        }

        $body = $this->view->render('feedback/feedback-form.twig', [
            'session' => $_SESSION,
            'feedback' => $feedback,
            'current_path' => $request->getUri()->getPath()
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    // Menyimpan hasil penilaian feedback
    public function storeAssessment(Request $request, Response $response, $args): Response
    {
        // Proteksi: Hanya Admin yang boleh akses
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $feedbackId = $args['id'];
        $adminId = $_SESSION['user_id'];
        $data = $request->getParsedBody();

        // Ambil data dari form
        $score = $data['assessment_score'] ?? null;
        $notes = $data['assessment_notes'] ?? '';

        // Validasi untuk skor 1-5
        if ($score === null || !is_numeric($score) || $score < 1 || $score > 5) {
            // Jika skor tidak valid, kembali ke form
            return $response->withHeader('Location', '/feedbacks/assess/' . $feedbackId)->withStatus(302);
        }

        // Update data di database menggunakan Medoo
        $this->db->update('tbl_feedbacks', [
            'assessment_score' => $score,
            'assessment_notes' => $notes,
            'assessed_by' => $adminId,
            'update_time' => date('Y-m-d H:i:s'),
            'update_id' => $adminId
        ], [
            'id' => $feedbackId
        ]);

        // Setelah berhasil, redirect ke halaman daftar feedback
        $response->getBody()->write(json_encode(["message" => "Berhasil disimpan"]));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function delete(Request $request, Response $response): Response
    {
        // Proteksi: Hanya Admin yang boleh hapus
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $data = $request->getParsedBody();
        $feedbackId = $data['id'] ?? null;
        $adminId = $_SESSION['user_id'];

        if (!$feedbackId) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Lakukan soft delete dengan mengupdate kolom 'archived'
        $result = $this->db->update('tbl_feedbacks', [
            'archived' => 1,
            'update_time' => date('Y-m-d H:i:s'),
            'update_id' => $adminId
        ], [
            'id' => $feedbackId
        ]);

        if ($result->rowCount() > 0) {
            $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Feedback berhasil dihapus.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Gagal menghapus feedback atau feedback tidak ditemukan.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
