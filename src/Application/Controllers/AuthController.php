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
        $email = $data['email'];
        $password = $data['password'];

        // 1. Cari user berdasarkan email
        $user = $this->db->get('tbl_users', '*', ['email' => $email]);

        // 2. Verifikasi user dan password
        if ($user && password_verify($password, $user['password'])) {
            // Password cocok, login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Arahkan ke dashboard
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        // Jika gagal, kembali ke halaman login (nanti kita tambahkan SweetAlert)
        return $response->withHeader('Location', '/login?error=1')->withStatus(302);
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
