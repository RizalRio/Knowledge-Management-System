<?php

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use Medoo\Medoo;

class MaterialController
{
    private $view;
    private $db;

    public function __construct(Environment $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    // Method untuk menampilkan halaman utama Kelola Materi
    public function index(Request $request, Response $response): Response
    {
        // Cek login & role (wajib!)
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            // Jika bukan admin, lempar ke dashboard biasa
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $body = $this->view->render('material/material-index.twig', [
            'session' => $_SESSION
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    // Method untuk API DataTables (mengembalikan JSON)
    public function data(Request $request, Response $response): Response
    {
        // Cek login & role
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // Ambil data dari database
        $materials = $this->db->select('tbl_materials', [
            "[><]tbl_users" => ["create_id" => "id"] // JOIN dengan tabel user
        ], [
            'tbl_materials.id',
            'tbl_materials.title',
            'tbl_materials.type',
            'tbl_users.name(creator_name)', // Ambil nama pembuat
            'tbl_materials.create_time'
        ], [
            'tbl_materials.archived' => 0
        ]);

        // Format data agar sesuai dengan yang diminta DataTables
        $output = ['data' => $materials];
        $payload = json_encode($output);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk menampilkan form tambah
    public function create(Request $request, Response $response): Response
    {
        // Cek login & role
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $body = $this->view->render('material/material-create.twig', [
            'session' => $_SESSION
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    // Method untuk menyimpan data baru
    public function store(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $title = $data['title'];
        $description = $data['description'];
        $type = $data['type'];
        $content = '';

        // Handle konten berdasarkan tipe
        if ($type === 'Link') {
            $content = $data['content_url'];
        } else {
            // Handle file upload (PDF atau Image)
            $uploadedFile = $uploadedFiles['content_file'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $uploadedFile->getClientFilename();
                $newFilename = date('YmdHis') . '-' . $filename;
                $directory = __DIR__ . '/../../../public/uploads/materials';
                $path = $directory . '/' . $newFilename;
                $uploadedFile->moveTo($path);
                $content = '/uploads/materials/' . $newFilename; // Simpan path relatif
            } else {
                $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Gagal mengupload file.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        // Simpan ke database
        $this->db->insert('tbl_materials', [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'content' => $content,
            'create_id' => $_SESSION['user_id']
        ]);

        $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Materi baru berhasil disimpan!']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        // Cek otorisasi dulu
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $materialId = $args['id'];

        // Lakukan soft delete: update kolom 'archived' menjadi 1
        $result = $this->db->update('tbl_materials', [
            'archived' => 1,
            'update_time' => date('Y-m-d H:i:s'), // Catat waktu update
            'update_id' => $_SESSION['user_id']   // Catat siapa yang update
        ], [
            'id' => $materialId
        ]);

        if ($result->rowCount() > 0) {
            $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Materi berhasil dihapus.']));
        } else {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Materi tidak ditemukan atau gagal dihapus.']));
            return $response->withStatus(404);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk menampilkan form edit
    public function edit(Request $request, Response $response, array $args): Response
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $materialId = $args['id'];
        $material = $this->db->get('tbl_materials', '*', ['id' => $materialId]);

        if (!$material) {
            // Jika materi tidak ditemukan, kembali ke daftar
            return $response->withHeader('Location', '/materials')->withStatus(302);
        }

        $body = $this->view->render('material/material-edit.twig', [
            'session' => $_SESSION,
            'material' => $material
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    // Method untuk memproses update
    public function update(Request $request, Response $response, array $args): Response
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $materialId = $args['id'];
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        // Data untuk di-update
        $updateData = [
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'update_time' => date('Y-m-d H:i:s'),
            'update_id' => $_SESSION['user_id']
        ];

        // Cek apakah ada file baru yang diupload atau tipe diubah
        $uploadedFile = $uploadedFiles['content_file'];
        if ($data['type'] === 'Link') {
            $updateData['content'] = $data['content_url'];
        } elseif ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            // Ada file baru, proses upload
            $filename = $uploadedFile->getClientFilename();
            $newFilename = date('YmdHis') . '-' . $filename;
            $directory = __DIR__ . '/../../../public/uploads/materials';
            $path = $directory . '/' . $newFilename;
            $uploadedFile->moveTo($path);
            $updateData['content'] = '/uploads/materials/' . $newFilename;
        }
        // Jika tidak ada file baru, kita tidak mengubah kolom 'content'.

        $this->db->update('tbl_materials', $updateData, ['id' => $materialId]);

        $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Materi berhasil diperbarui!']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function showAssignForm(Request $request, Response $response, array $args): Response
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $materialId = $args['id'];
        $material = $this->db->get('tbl_materials', ['id', 'title'], ['id' => $materialId]);

        if (!$material) {
            return $response->withHeader('Location', '/materials')->withStatus(302);
        }

        // Ambil semua user dengan role 'Pengguna Umum'
        $users = $this->db->select('tbl_users', ['id', 'name', 'email'], ['role' => 'Pengguna Umum', 'archived' => 0]);

        // Ambil data user yang sudah ditugaskan materi ini
        $assignedUserIds = $this->db->select('tbl_material_assignments', 'user_id', ['material_id' => $materialId]);

        // Tambahkan flag 'is_assigned' ke data user untuk menandai checkbox
        foreach ($users as &$user) {
            $user['is_assigned'] = in_array($user['id'], $assignedUserIds);
        }

        $body = $this->view->render('material/material-assign.twig', [
            'session' => $_SESSION,
            'material' => $material,
            'users' => $users
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    // Method untuk menyimpan data penugasan
    public function storeAssignment(Request $request, Response $response, array $args): Response
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $materialId = $args['id'];
        $data = $request->getParsedBody();
        $assignedUserIds = $data['user_ids'] ?? []; // Ambil array user_id dari form

        // Strategi Sinkronisasi: Hapus semua penugasan lama, lalu buat yang baru.
        // 1. Hapus semua penugasan untuk materi ini
        $this->db->delete('tbl_material_assignments', ['material_id' => $materialId]);

        // 2. Jika ada user yang dipilih, insert penugasan baru
        if (!empty($assignedUserIds)) {
            $newAssignments = [];
            foreach ($assignedUserIds as $userId) {
                $newAssignments[] = [
                    'material_id' => $materialId,
                    'user_id' => $userId,
                    'create_id' => $_SESSION['user_id']
                ];
            }
            $this->db->insert('tbl_material_assignments', $newAssignments);
        }

        $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Penugasan materi berhasil diperbarui!']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk Karyawan melihat materi
    public function viewMaterial(Request $request, Response $response, array $args): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $materialId = $args['id'];
        $userId = $_SESSION['user_id'];

        // PENTING: Cek keamanan, pastikan user ini memang ditugaskan materi ini
        $isAssigned = $this->db->has('tbl_material_assignments', [
            'AND' => [
                'material_id' => $materialId,
                'user_id' => $userId
            ]
        ]);

        // Jika user adalah Admin, izinkan akses (untuk preview)
        if ($_SESSION['user_role'] === 'Admin') {
            $isAssigned = true;
        }

        if (!$isAssigned) {
            // Jika tidak berhak, lempar ke dashboard
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $material = $this->db->get('tbl_materials', '*', ['id' => $materialId]);

        $body = $this->view->render('material/material-view.twig', [
            'session' => $_SESSION,
            'material' => $material
        ]);
        $response->getBody()->write($body);
        return $response;
    }
}
