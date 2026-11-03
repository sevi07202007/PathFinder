<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/matching.php';

function careerViewModel(array $career): array
{
    $metadata = careerDisplayMetadata()[$career['Career_Title']] ?? [];
    return array_merge($metadata, [
        'careerId' => (int) $career['Career_ID'],
        'title' => $career['Career_Title'],
        'summary' => $career['Career_Description'] ?? '',
        'courseId' => (int) $career['Course_ID'],
        'courseName' => $career['Course_Name'],
    ]);
}

requestMethod('GET');
try {
    $pdo = database();
    $careerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $sql = 'SELECT c.Career_ID, c.Career_Title, c.Description AS Career_Description, co.Course_ID, co.Course_Name, co.Description AS Course_Description FROM CAREER c INNER JOIN COURSE co ON co.Course_ID = c.Course_ID';
    if ($careerId !== false && $careerId !== null) {
        $statement = $pdo->prepare($sql . ' WHERE c.Career_ID = :id');
        $statement->execute(['id' => $careerId]);
        $career = $statement->fetch();
        if (!$career) jsonResponse(['success' => false, 'message' => 'Career not found.'], 404);
        jsonResponse(['success' => true, 'career' => careerViewModel($career)]);
    }
    $statement = $pdo->query($sql . ' ORDER BY c.Career_ID');
    jsonResponse(['success' => true, 'careers' => array_map('careerViewModel', $statement->fetchAll())]);
} catch (Throwable $exception) { serverError($exception); }
