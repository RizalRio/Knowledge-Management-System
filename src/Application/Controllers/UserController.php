<?php

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use Medoo\Medoo;
use SebastianBergmann\Environment\Console;

class UserController
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
        // Cek Login & Role
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $body = $this->view->render('user/user-index.twig', [
            'session' => $_SESSION,
            'current_path' => $request->getUri()->getPath()
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    public function data(Request $request, Response $response): Response
    {
        // Ambil semua query parameter dari URL
        $params = $request->getQueryParams();
        $roleToFilter = $params['role'] ?? null;

        // Siapkan kondisi dasar untuk query
        $conditions = ['archived' => 0];

        // JIKA ada parameter 'role', tambahkan ke kondisi query
        if ($roleToFilter) {
            $conditions['role'] = $roleToFilter;
        }

        // Lakukan query ke database dengan kondisi yang sudah disiapkan
        $users = $this->db->select('tbl_users', '*', $conditions);

        $payload = json_encode(['data' => $users]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response
    {
        // Cek Login & Role
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $response->getBody()->write($this->view->render('user/user-create.twig', [
            'session' => $_SESSION,
            'current_path' => $request->getUri()->getPath()
        ]));

        return $response;
    }

    public function store(Request $request, Response $response): Response
    {
        // Cek Login & Role
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // Ambil data dari request
        $data = $request->getParsedBody();

        // Validasi field
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            $response->getBody()->write('Semua field harus diisi.');
            return $response->withStatus(400);
        }

        // Cek duplikasi email
        if ($this->db->has('tbl_users', ['email' => $data['email']])) {
            $response->getBody()->write('Email sudah digunakan.');
            return $response->withStatus(409); // Conflict
        }

        // Insert data user baru
        $this->db->insert('tbl_users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'user',
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
            'create_id' => $_SESSION['id'] ?? null,
            'update_id' => $_SESSION['id'] ?? null,
            'archived' => 0
        ]);
        $payload = json_encode([
            'success' => true,
            'message' => 'Pengguna berhasil ditambahkan.'
        ]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        // Cek Login & Role
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        // Ambil ID pengguna dari parameter
        $userId = $args['id'];
        $user = $this->db->get('tbl_users', '*', ['id' => $userId]);

        // Cek apakah pengguna ditemukan
        if (!$user) {
            $response->getBody()->write('User tidak ditemukan');
            return $response->withStatus(404);
        }

        // Render the edit user template
        $response->getBody()->write($this->view->render('user/user-edit.twig', [
            'user' => $user,
            'session' => $_SESSION,
            'current_path' => $request->getUri()->getPath()
        ]));
        return $response;
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        // Cek Login & Role
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }
        // Ambil ID pengguna dari parameter
        $userId = $args['id'];
        $data = $request->getParsedBody();

        // Update user in the database
        $this->db->update('tbl_users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'user',
            'update_time' => date('Y-m-d H:i:s'),
            'update_id' => $_SESSION['user_id'] ?? null
        ], ['id' => $userId]);

        $payload = json_encode([
            'success' => true,
            'message' => 'Pengguna berhasil diperbarui.'
        ]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $userId = $args['id'];
        // Update archived menjadi 1, bukan delete
        $this->db->update('tbl_users', ['archived' => 1], ['id' => $userId]);

        $payload = json_encode([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus.'
        ]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
