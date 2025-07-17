<?php

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use Medoo\Medoo;

class CategoryController
{
    private $view;
    private $db;

    public function __construct(Environment $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    // Method untuk menampilkan halaman utama Manajemen Kategori
    public function index(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $body = $this->view->render('category/category-index.twig', [
            'session' => $_SESSION,
            'current_path' => $request->getUri()->getPath()
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    // Method untuk API DataTables
    public function data(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $categories = $this->db->select('tbl_categories', '*', ['archived' => 0]);

        $output = ['data' => $categories];
        $payload = json_encode($output);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk mengambil satu kategori by ID (untuk form edit)
    public function getById(Request $request, Response $response, array $args): Response
    {
        $category = $this->db->get('tbl_categories', '*', ['id' => $args['id']]);
        $response->getBody()->write(json_encode($category));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk menyimpan kategori baru
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->db->insert('tbl_categories', [
            'name' => $data['name'],
            'create_id' => $_SESSION['user_id']
        ]);
        $response->getBody()->write(json_encode(['message' => 'Kategori berhasil ditambahkan.']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk mengupdate kategori
    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $this->db->update('tbl_categories', [
            'name' => $data['name'],
            'update_time' => date('Y-m-d H:i:s'),
            'update_id' => $_SESSION['user_id']
        ], ['id' => $args['id']]);
        $response->getBody()->write(json_encode(['message' => 'Kategori berhasil diperbarui.']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk menghapus (soft delete) kategori
    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->db->update('tbl_categories', [
            'archived' => 1,
            'update_time' => date('Y-m-d H:i:s'),
            'update_id' => $_SESSION['user_id']
        ], ['id' => $args['id']]);
        $response->getBody()->write(json_encode(['message' => 'Kategori berhasil dihapus.']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
