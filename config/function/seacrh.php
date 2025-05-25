<?php
require_once __DIR__ . "/../db.php";

use App\Models\Book;
use App\Controllers\BooksController;


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (isset($_GET['query'])) {
    $query = trim($_GET['query']);

    if (empty($query)) {
        echo json_encode([]);
        exit;
    }

    try {
        // SQL запрос для поиска по title, author, genre
        $sql = "SELECT id, title, author, genre, description, cover_image, created_at 
                FROM books 
                WHERE title LIKE :query 
                   OR author LIKE :query 
                   OR genre LIKE :query 
                ORDER BY title ASC 
                LIMIT 10";

        $stmt = $conn->prepare($sql);
        $searchTerm = "%{$query}%";
        $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($results);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Ошибка поиска: ' . $e->getMessage()]);
    }
} else {
    echo json_encode([]);
}
