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
}
