<?php

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use Medoo\Medoo; // <-- Import Medoo

class AuthController
{
    private $view;
    private $db; // <-- Tambahkan properti db

    // Ubah constructor untuk menerima $view dan $db
    public function __construct(Environment $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db; // <-- Simpan db
    }

    public function showLoginForm(Request $request, Response $response): Response
    {
        $body = $this->view->render('auth-login.twig');
        $response->getBody()->write($body);
        return $response;
    }

    // ===============================================
    // TAMBAHKAN METHOD BARU DI BAWAH INI
    // ===============================================
    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // Cari user berdasarkan email
        $user = $this->db->get('tbl_users', [
            'id',
            'name',
            'email',
            'password',
            'role'
        ], [
            'email' => $email,
            'archived' => 0 // Pastikan user tidak di-soft-delete
        ]);

        // Verifikasi user dan password
        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil, simpan data ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Kirim respons JSON sukses
            $payload = json_encode(['status' => 'success', 'message' => 'Login successful!']);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }

        // Login gagal, kirim respons JSON error
        $payload = json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401); // 401 Unauthorized
    }

    public function showRegisterForm(Request $request, Response $response): Response
    {
        $body = $this->view->render('auth-register.twig');
        $response->getBody()->write($body);
        return $response;
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Validasi sederhana (nanti bisa dibuat lebih canggih)
        if ($data['password'] !== $data['password_confirm']) {
            // Password tidak cocok, kembali ke form register
            // Nanti kita tambahkan notifikasi error
            return $response->withHeader('Location', '/register?error=password')->withStatus(302);
        }

        // Hash password untuk keamanan! Ini WAJIB.
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Simpan ke database
        $this->db->insert('tbl_users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role' => 'Pengguna Umum' // Default role
        ]);

        // Redirect ke halaman login dengan notifikasi sukses
        return $response->withHeader('Location', '/login?success=1')->withStatus(302);
    }
}
