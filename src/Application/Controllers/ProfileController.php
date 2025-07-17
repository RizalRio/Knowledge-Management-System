<?php

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use Medoo\Medoo;

class ProfileController
{
    private $view;
    private $db;

    public function __construct(Environment $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    // Method untuk menampilkan halaman profil
    public function index(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $user = $this->db->get('tbl_users', ['id', 'name', 'email'], ['id' => $_SESSION['user_id']]);

        $body = $this->view->render('profile.twig', [
            'session' => $_SESSION,
            'user' => $user,
            'current_path' => $request->getUri()->getPath()
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    // Method untuk update nama
    public function updateProfile(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return $response->withStatus(403);
        }

        $data = $request->getParsedBody();
        $this->db->update('tbl_users', ['name' => $data['name']], ['id' => $_SESSION['user_id']]);

        // Update juga nama di session agar langsung berubah di sidebar
        $_SESSION['user_name'] = $data['name'];

        $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Nama berhasil diperbarui!']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Method untuk ganti password
    public function updatePassword(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return $response->withStatus(403);
        }

        $data = $request->getParsedBody();
        $user = $this->db->get('tbl_users', ['password'], ['id' => $_SESSION['user_id']]);

        // 1. Verifikasi password lama
        if (!password_verify($data['old_password'], $user['password'])) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Password lama salah.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 2. Cek apakah password baru & konfirmasi cocok
        if ($data['new_password'] !== $data['confirm_password']) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Konfirmasi password baru tidak cocok.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 3. Hash password baru dan update ke database
        $newHashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
        $this->db->update('tbl_users', ['password' => $newHashedPassword], ['id' => $_SESSION['user_id']]);

        $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Password berhasil diubah!']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
