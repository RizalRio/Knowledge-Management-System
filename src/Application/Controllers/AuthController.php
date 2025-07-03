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
        $body = $this->view->render('auth-login.html');
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
}
