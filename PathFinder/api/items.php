<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/matching.php';

requestMethod('GET');
try {
    $statement = database()->query('SELECT Item_ID, Item_Name, Item_Type FROM SKILL_INTEREST ORDER BY Item_Type, Item_Name');
    $items = $statement->fetchAll();
    $itemIds = [];
    foreach ($items as $item) $itemIds[$item['Item_Name']] = (int) $item['Item_ID'];
    $questions = [];
    foreach (questionScores() as $number => $options) {
        $questions[] = [
            'text' => assessmentQuestionTexts()[$number],
            'options' => array_map(fn($label) => ['label' => $label, 'itemId' => $itemIds[$label]], array_keys($options)),
        ];
    }
    jsonResponse(['success' => true, 'items' => $items, 'questions' => $questions]);
} catch (Throwable $exception) { serverError($exception); }
