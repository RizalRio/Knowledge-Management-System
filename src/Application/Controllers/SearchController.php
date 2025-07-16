<?php

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use Medoo\Medoo;

class SearchController
{
    private $view;
    private $db;

    public function __construct(Environment $view, Medoo $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    // Method untuk menampilkan hasil pencarian
    public function results(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        // Ambil kata kunci dari URL (?q=...)
        $queryParams = $request->getQueryParams();
        $keyword = $queryParams['q'] ?? '';

        $results = [];

        if (!empty($keyword)) {
            // Lakukan pencarian di database menggunakan LIKE
            $results = $this->db->select('tbl_materials', [
                'id',
                'title',
                'description',
                'type'
            ], [
                'OR' => [
                    'title[~]' => $keyword, // Cari di judul
                    'description[~]' => $keyword // Cari di deskripsi
                ],
                'archived' => 0
            ]);
        }

        $body = $this->view->render('search-results.twig', [
            'session' => $_SESSION,
            'keyword' => $keyword,
            'results' => $results
        ]);
        $response->getBody()->write($body);
        return $response;
    }
}
