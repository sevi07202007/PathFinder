<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/matching.php';

function careersForMatching(PDO $pdo): array
{
    return $pdo->query('SELECT c.Career_ID, c.Career_Title, c.Description AS Career_Description, co.Course_Name FROM CAREER c INNER JOIN COURSE co ON co.Course_ID = c.Course_ID ORDER BY c.Career_ID')->fetchAll();
}

function assessmentResults(PDO $pdo, int $assessmentId): array
{
    $statement = $pdo->prepare('SELECT si.Item_Name FROM RESPONSE r INNER JOIN SKILL_INTEREST si ON si.Item_ID = r.Item_ID WHERE r.Assessment_ID = :assessment_id ORDER BY r.Response_ID');
    $statement->execute(['assessment_id' => $assessmentId]);
    $ranked = rankCareers(array_column($statement->fetchAll(), 'Item_Name'), careersForMatching($pdo));
    $savedRanks = $pdo->prepare('SELECT Career_ID, Rank_No FROM RECOMMENDATION WHERE Assessment_ID = :assessment_id');
    $savedRanks->execute(['assessment_id' => $assessmentId]);
    $rankByCareer = [];
    foreach ($savedRanks->fetchAll() as $savedRank) $rankByCareer[(int) $savedRank['Career_ID']] = (int) $savedRank['Rank_No'];
    usort($ranked, fn($a, $b) => ($rankByCareer[$a['Career_ID']] ?? PHP_INT_MAX) <=> ($rankByCareer[$b['Career_ID']] ?? PHP_INT_MAX));
    return $ranked;
}

try {
    $pdo = database();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $assessmentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$assessmentId) jsonResponse(['success' => false, 'message' => 'A valid assessment ID is required.'], 400);
        $exists = $pdo->prepare('SELECT Assessment_ID, Date_Taken FROM ASSESSMENT WHERE Assessment_ID = :id');
        $exists->execute(['id' => $assessmentId]);
        $assessment = $exists->fetch();
        if (!$assessment) jsonResponse(['success' => false, 'message' => 'Assessment not found.'], 404);
        jsonResponse(['success' => true, 'assessment' => $assessment, 'recommendations' => assessmentResults($pdo, $assessmentId)]);
    }
    requestMethod('POST');
    $body = jsonBody();
    $items = $body['item_ids'] ?? null;
    if (!is_array($items) || count($items) !== 4 || count(array_unique($items)) !== 4 || array_filter($items, fn($id) => filter_var($id, FILTER_VALIDATE_INT) === false)) {
        jsonResponse(['success' => false, 'message' => 'Exactly four different valid assessment responses are required.'], 422);
    }
    $itemIds = array_map('intval', $items);
    $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
    $selected = $pdo->prepare("SELECT Item_ID, Item_Name, Item_Type FROM SKILL_INTEREST WHERE Item_ID IN ($placeholders)");
    $selected->execute($itemIds);
    $records = $selected->fetchAll();
    $allowed = array_merge(...array_map('array_keys', questionScores()));
    $questionNumbers = [];
    foreach ($records as $record) {
        foreach (questionScores() as $number => $question) {
            if (isset($question[$record['Item_Name']])) {
                $questionNumbers[] = $number;
                break;
            }
        }
    }
    if (count($records) !== 4 || count($questionNumbers) !== 4 || count(array_unique($questionNumbers)) !== 4 || array_diff(array_column($records, 'Item_Name'), $allowed) || array_filter($records, fn($item) => $item['Item_Type'] !== 'Interest')) {
        jsonResponse(['success' => false, 'message' => 'One or more selected assessment items are invalid.'], 422);
    }
    $pdo->beginTransaction();
    $pdo->exec('INSERT INTO ASSESSMENT () VALUES ()');
    $assessmentId = (int) $pdo->lastInsertId();
    $response = $pdo->prepare('INSERT INTO RESPONSE (Assessment_ID, Item_ID) VALUES (:assessment_id, :item_id)');
    foreach ($itemIds as $itemId) $response->execute(['assessment_id' => $assessmentId, 'item_id' => $itemId]);
    $ranked = rankCareers(array_column($records, 'Item_Name'), careersForMatching($pdo));
    $recommendation = $pdo->prepare('INSERT INTO RECOMMENDATION (Assessment_ID, Career_ID, Rank_No) VALUES (:assessment_id, :career_id, :rank_no)');
    foreach ($ranked as $index => $career) $recommendation->execute(['assessment_id' => $assessmentId, 'career_id' => $career['Career_ID'], 'rank_no' => $index + 1]);
    $pdo->commit();
    jsonResponse(['success' => true, 'assessment_id' => $assessmentId, 'recommendations' => $ranked], 201);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    serverError($exception);
}
