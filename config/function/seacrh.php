<?php
require_once __DIR__ . "/../db.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db.php';

try {
    if (!isset($_GET['query']) || empty(trim($_GET['query']))) {
        echo json_encode(['error' => 'Пустой запрос']);
        exit;
    }

    $query = trim($_GET['query']);

    if (strlen($query) < 2) {
        echo json_encode(['error' => 'Слишком короткий запрос']);
        exit;
    }

    // Подготавливаем поисковый запрос
    $searchTerm = '%' . $query . '%';

    // SQL запрос для поиска по названию, автору и жанру
    $sql = "SELECT 
                b.id,
                b.title,
                b.author,
                b.genre,
                b.cover_image,
                b.description,
                b.publication_year,
                CASE 
                    WHEN b.title LIKE ? THEN 3
                    WHEN b.author LIKE ? THEN 2
                    WHEN b.genre LIKE ? THEN 1
                    ELSE 0
                END as relevance
            FROM books b 
            WHERE b.title LIKE ? 
               OR b.author LIKE ? 
               OR b.genre LIKE ?
               OR b.description LIKE ?
            ORDER BY relevance DESC, b.title ASC
            LIMIT 20";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'sssssss',
        $searchTerm,
        $searchTerm,
        $searchTerm, // для relevance
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm  // для WHERE
    );

    $stmt->execute();
    $result = $stmt->get_result();

    $books = [];
    while ($row = $result->fetch_assoc()) {
        // Добавляем полный путь к изображению, если он относительный
        if (!empty($row['cover_image']) && !str_starts_with($row['cover_image'], 'http')) {
            if (!str_starts_with($row['cover_image'], '/')) {
                $row['cover_image'] = '/' . $row['cover_image'];
            }
        }

        $books[] = $row;
    }

    echo json_encode($books);
} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    echo json_encode(['error' => 'Ошибка поиска']);
}
