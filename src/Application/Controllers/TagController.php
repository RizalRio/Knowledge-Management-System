<?php

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use Medoo\Medoo;

class TagController
{
    private $view;
    private $db;

    public function __construct(Environment $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    // Method untuk menampilkan halaman utama Manajemen Tag
    public function index(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $body = $this->view->render('tag/tag-index.twig', [
            'session' => $_SESSION
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

        $tags = $this->db->select('tbl_tags', '*', ['archived' => 0]);

        $output = ['data' => $tags];
        $payload = json_encode($output);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk mengambil satu tag by ID (untuk form edit)
    public function getById(Request $request, Response $response, array $args): Response
    {
        $tag = $this->db->get('tbl_tags', '*', ['id' => $args['id']]);
        $response->getBody()->write(json_encode($tag));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk menyimpan tag baru
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->db->insert('tbl_tags', [
            'name' => $data['name'],
            'create_id' => $_SESSION['user_id']
        ]);
        $response->getBody()->write(json_encode(['message' => 'Tag berhasil ditambahkan.']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk mengupdate tag
    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $this->db->update('tbl_tags', [
            'name' => $data['name'],
            'update_time' => date('Y-m-d H:i:s'),
            'update_id' => $_SESSION['user_id']
        ], ['id' => $args['id']]);
        $response->getBody()->write(json_encode(['message' => 'Tag berhasil diperbarui.']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk menghapus (soft delete) tag
    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->db->update('tbl_tags', [
            'archived' => 1,
            'update_time' => date('Y-m-d H:i:s'),
            'update_id' => $_SESSION['user_id']
        ], ['id' => $args['id']]);
        $response->getBody()->write(json_encode(['message' => 'Tag berhasil dihapus.']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
