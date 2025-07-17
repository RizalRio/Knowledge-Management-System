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

    public function index(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'Kontributor'])) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $body = $this->view->render('material/material-index.twig', ['session' => $_SESSION]);
        $response->getBody()->write($body);
        return $response;
    }

    public function data(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id'])) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $conditions = ['tbl_materials.archived' => 0];
        if ($_SESSION['user_role'] !== 'Admin') {
            $conditions['tbl_materials.create_id'] = $_SESSION['user_id'];
        }

        $materials = $this->db->select('tbl_materials', [
            "[><]tbl_users" => ["create_id" => "id"]
        ], [
            'tbl_materials.id',
            'tbl_materials.title',
            'tbl_materials.type',
            'tbl_users.name(creator_name)',
            'tbl_materials.create_time'
        ], $conditions);

        foreach ($materials as &$material) {
            $material['categories'] = $this->db->select('tbl_material_categories', ['[><]tbl_categories' => ['category_id' => 'id']], 'tbl_categories.name', ['material_id' => $material['id']]);
            $material['tags'] = $this->db->select('tbl_material_tags', ['[><]tbl_tags' => ['tag_id' => 'id']], 'tbl_tags.name', ['material_id' => $material['id']]);
        }

        $output = ['data' => $materials];
        $payload = json_encode($output);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'Kontributor'])) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $body = $this->view->render('material/material-create.twig', [
            'session' => $_SESSION,
            'categories' => $this->db->select('tbl_categories', ['id', 'name'], ['archived' => 0]),
            'tags' => $this->db->select('tbl_tags', ['id', 'name'], ['archived' => 0])
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    public function store(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id'])) { /* ... handle error ... */
        }

        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $title = $data['title'];
        $description = $data['description'];
        $type = $data['type'];
        $content = '';

        if ($type === 'Link') {
            $content = $data['content_url'];
        } else {
            $uploadedFile = $uploadedFiles['content_file'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $uploadedFile->getClientFilename();
                $newFilename = date('YmdHis') . '-' . $filename;
                $path = __DIR__ . '/../../../public/uploads/materials/' . $newFilename;
                $uploadedFile->moveTo($path);
                $content = '/uploads/materials/' . $newFilename;
            } else {
                $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Gagal mengupload file.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        $this->db->insert('tbl_materials', [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'content' => $content,
            'create_id' => $_SESSION['user_id']
        ]);
        $materialId = $this->db->id();

        $categoryIds = $data['category_ids'] ?? [];
        if (!empty($categoryIds)) {
            $categoryData = array_map(fn($catId) => ['material_id' => $materialId, 'category_id' => $catId], $categoryIds);
            $this->db->insert('tbl_material_categories', $categoryData);
        }

        $tagIds = $data['tag_ids'] ?? [];
        if (!empty($tagIds)) {
            $tagData = array_map(fn($tagId) => ['material_id' => $materialId, 'tag_id' => $tagId], $tagIds);
            $this->db->insert('tbl_material_tags', $tagData);
        }

        $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Materi baru berhasil disimpan!']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $materialId = $args['id'];
        $material = $this->db->get('tbl_materials', '*', ['id' => $materialId]);

        if (!$material) {
            return $response->withHeader('Location', '/materials')->withStatus(302);
        }

        if ($_SESSION['user_role'] !== 'Admin' && $material['create_id'] != $_SESSION['user_id']) {
            return $response->withHeader('Location', '/materials')->withStatus(403);
        }

        $selectedCategoryIds = $this->db->select('tbl_material_categories', 'category_id', ['material_id' => $materialId]);
        $selectedTagIds = $this->db->select('tbl_material_tags', 'tag_id', ['material_id' => $materialId]);

        $body = $this->view->render('material/material-edit.twig', [
            'session' => $_SESSION,
            'material' => $material,
            'categories' => $this->db->select('tbl_categories', ['id', 'name'], ['archived' => 0]),
            'tags' => $this->db->select('tbl_tags', ['id', 'name'], ['archived' => 0]),
            'selected_category_ids' => $selectedCategoryIds,
            'selected_tag_ids' => $selectedTagIds,
            'active_menu' => 'materials'
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        if (!isset($_SESSION['user_id'])) { /* ... handle error ... */
        }
        $materialId = $args['id'];

        if ($_SESSION['user_role'] !== 'Admin') {
            if (!$this->db->has('tbl_materials', ['AND' => ['id' => $materialId, 'create_id' => $_SESSION['user_id']]])) {
                $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Akses ditolak.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            }
        }

        $data = $request->getParsedBody();
        $updateData = ['title' => $data['title'], 'description' => $data['description'], 'type' => $data['type'], 'update_time' => date('Y-m-d H:i:s'), 'update_id' => $_SESSION['user_id']];

        $uploadedFile = $request->getUploadedFiles()['content_file'];
        if ($data['type'] === 'Link') {
            $updateData['content'] = $data['content_url'];
        } elseif ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $uploadedFile->getClientFilename();
            $newFilename = date('YmdHis') . '-' . $filename;
            $path = __DIR__ . '/../../../public/uploads/materials/' . $newFilename;
            $uploadedFile->moveTo($path);
            $updateData['content'] = '/uploads/materials/' . $newFilename;
        }

        $this->db->update('tbl_materials', $updateData, ['id' => $materialId]);

        $categoryIds = $data['category_ids'] ?? [];
        $this->db->delete('tbl_material_categories', ['material_id' => $materialId]);
        if (!empty($categoryIds)) {
            $categoryData = array_map(fn($catId) => ['material_id' => $materialId, 'category_id' => $catId], $categoryIds);
            $this->db->insert('tbl_material_categories', $categoryData);
        }

        $tagIds = $data['tag_ids'] ?? [];
        $this->db->delete('tbl_material_tags', ['material_id' => $materialId]);
        if (!empty($tagIds)) {
            $tagData = array_map(fn($tagId) => ['material_id' => $materialId, 'tag_id' => $tagId], $tagIds);
            $this->db->insert('tbl_material_tags', $tagData);
        }

        $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Materi berhasil diperbarui!']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        if (!isset($_SESSION['user_id'])) { /* ... handle error ... */
        }
        $materialId = $args['id'];

        if ($_SESSION['user_role'] !== 'Admin') {
            if (!$this->db->has('tbl_materials', ['AND' => ['id' => $materialId, 'create_id' => $_SESSION['user_id']]])) {
                $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Akses ditolak.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            }
        }

        $this->db->update('tbl_materials', ['archived' => 1, 'update_time' => date('Y-m-d H:i:s'), 'update_id' => $_SESSION['user_id']], ['id' => $materialId]);
        $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Materi berhasil dihapus.']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk Tim dapat melihat materi dan mengirim feedback
    public function viewMaterial(Request $request, Response $response, $args): Response
    {
        // 1. Cek apakah pengguna sudah login
        if (!isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        // 2. Ambil ID dari URL dan session
        $materialId = $args['id'];
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];

        // 3. Lakukan Pengecekan Keamanan: Pastikan pengguna berhak mengakses materi ini
        $isAssigned = $this->db->has('tbl_material_assignments', [
            'AND' => [
                'material_id' => $materialId,
                'user_id' => $userId,
                'archived' => 0
            ]
        ]);

        // Jika pengguna adalah Admin, berikan akses penuh untuk tujuan preview
        if ($_SESSION['user_role'] === 'Admin') {
            $isAssigned = true;
        }

        // Jika tidak ditugaskan dan bukan Admin, lempar keluar
        if (!$isAssigned) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        // 4. Ambil data materi setelah pengecekan keamanan berhasil
        $material = $this->db->get("tbl_materials", "*", ["id" => $materialId, "archived" => 0]);

        // Jika materi tidak ada (misalnya sudah dihapus), tampilkan halaman tidak ditemukan
        if (!$material) {
            return $response->withStatus(404);
        }

        $categories = $this->db->select('tbl_material_categories', ['[><]tbl_categories' => ['category_id' => 'id']], 'tbl_categories.name', ['material_id' => $materialId]);
        $tags = $this->db->select('tbl_material_tags', ['[><]tbl_tags' => ['tag_id' => 'id']], 'tbl_tags.name', ['material_id' => $materialId]);

        // Siapkan data dasar untuk dikirim ke view
        $viewData = [
            'session' => $_SESSION,
            'material' => $material,
            'categories' => $categories,
            'tags' => $tags,
            'current_path' => $request->getUri()->getPath()
        ];

        // 5. PERUBAHAN UTAMA: Logika pengambilan feedback berdasarkan peran
        if ($userRole === 'Pengguna Umum') {
            // Jika Pengguna Umum, ambil HANYA feedback miliknya sendiri
            $viewData['feedback'] = $this->db->get("tbl_feedbacks", "*", [
                "material_id" => $materialId,
                "user_id" => $userId,
                "archived" => 0
            ]);
        } else {
            // Jika Admin atau Kontributor, ambil SEMUA feedback untuk materi ini
            $viewData['all_feedbacks'] = $this->db->select("tbl_feedbacks", [
                "[>]tbl_users" => ["user_id" => "id"]
            ], [
                "tbl_users.name(user_name)",
                "tbl_feedbacks.material_rating",
                "tbl_feedbacks.feedback_text",
                "tbl_feedbacks.create_time"
            ], [
                "tbl_feedbacks.material_id" => $materialId,
                "tbl_feedbacks.archived" => 0,
                "ORDER" => ["tbl_feedbacks.create_time" => "DESC"]
            ]);
        }

        // 6. Tampilkan halaman dengan semua data yang diperlukan
        $body = $this->view->render('material/material-view.twig', $viewData);
        $response->getBody()->write($body);
        return $response;
    }
}
