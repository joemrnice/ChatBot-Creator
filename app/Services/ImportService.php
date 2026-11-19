<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Application;
use App\Core\Database;
use App\Core\Security;
use RuntimeException;

class ImportService
{
    private SubscriptionService $subscriptionService;

    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService();
    }

    /**
     * Step 1: Validate file and parse preview records
     */
    public function parseImportFile(int $userId, ?int $chatbotId, array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload error code: ' . $file['error']);
        }

        $maxSize = Application::config('chatbot.max_upload_size', 10485760);
        if ($file['size'] > $maxSize) {
            throw new RuntimeException('File exceeds maximum upload size limit (' . round($maxSize / 1048576) . 'MB).');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['json', 'csv'], true)) {
            throw new RuntimeException('Invalid file format. Only .json and .csv files are supported.');
        }

        $content = file_get_contents($file['tmp_name']);
        if ($content === false || trim($content) === '') {
            throw new RuntimeException('Uploaded file is empty.');
        }

        // Validate UTF-8 encoding
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }

        if ($extension === 'json') {
            $parsed = $this->parseJsonContent($content);
        } else {
            $parsed = $this->parseCsvContent($file['tmp_name']);
        }

        // Store import job for confirmation step
        $jobId = Database::insert('import_jobs', [
            'user_id' => $userId,
            'chatbot_id' => $chatbotId,
            'file_name' => basename($file['name']),
            'file_type' => $extension,
            'file_size' => (int)$file['size'],
            'total_records' => count($parsed['records']),
            'valid_records' => count($parsed['valid_records']),
            'invalid_records' => count($parsed['errors']),
            'status' => 'parsed',
            'preview_data' => json_encode($parsed),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'job_id' => $jobId,
            'file_type' => $extension,
            'file_name' => $file['name'],
            'total_count' => count($parsed['records']),
            'valid_count' => count($parsed['valid_records']),
            'invalid_count' => count($parsed['errors']),
            'bot_metadata' => $parsed['bot_metadata'] ?? null,
            'preview_records' => array_slice($parsed['valid_records'], 0, 10),
            'errors' => $parsed['errors'],
        ];
    }

    private function parseJsonContent(string $content): array
    {
        $json = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($json)) {
            throw new RuntimeException('Malformed JSON file: ' . json_last_error_msg());
        }

        $records = [];
        $validRecords = [];
        $errors = [];
        $botMeta = $json['bot'] ?? null;

        // Knowledge entries can be under 'knowledge' or root array
        $knowledgeItems = $json['knowledge'] ?? (isset($json[0]) ? $json : []);

        foreach ($knowledgeItems as $idx => $item) {
            $rowNum = $idx + 1;
            $type = $item['type'] ?? 'faq';
            $question = trim($item['question'] ?? '');
            $answer = trim($item['answer'] ?? '');
            $title = trim($item['title'] ?? '');
            $itemContent = trim($item['content'] ?? '');
            $category = trim($item['category'] ?? 'General');
            $tags = is_array($item['tags'] ?? null) ? implode(',', $item['tags']) : trim($item['tags'] ?? '');

            $records[] = $item;

            if ($type === 'faq' && ($question === '' || $answer === '')) {
                $errors[] = ["row" => $rowNum, "error" => "FAQ item requires both 'question' and 'answer'."];
                continue;
            }

            if ($type === 'document' && ($title === '' || $itemContent === '')) {
                $errors[] = ["row" => $rowNum, "error" => "Document item requires both 'title' and 'content'."];
                continue;
            }

            $validRecords[] = [
                'type' => $type,
                'question' => Security::sanitizeString($question),
                'answer' => Security::sanitizeHtml($answer),
                'title' => Security::sanitizeString($title),
                'content' => Security::sanitizeHtml($itemContent),
                'category' => Security::sanitizeString($category),
                'tags' => Security::sanitizeString($tags),
            ];
        }

        return [
            'bot_metadata' => $botMeta,
            'behavior' => $json['behavior'] ?? null,
            'appearance' => $json['appearance'] ?? null,
            'records' => $records,
            'valid_records' => $validRecords,
            'errors' => $errors,
        ];
    }

    private function parseCsvContent(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new RuntimeException('Could not read CSV file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new RuntimeException('CSV file is empty or missing headers.');
        }

        $header = array_map(fn($h) => strtolower(trim((string)$h)), $header);

        // Required headers validation
        $hasFaqHeaders = in_array('question', $header, true) && in_array('answer', $header, true);
        $hasDocHeaders = in_array('title', $header, true) && in_array('content', $header, true);

        if (!$hasFaqHeaders && !$hasDocHeaders) {
            fclose($handle);
            throw new RuntimeException("Invalid CSV headers. Expected at least 'question' & 'answer', or 'title' & 'content'. Found: " . implode(', ', $header));
        }

        $records = [];
        $validRecords = [];
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (empty(array_filter($row))) {
                continue; // Skip empty lines
            }

            if (count($row) !== count($header)) {
                $errors[] = ["row" => $rowNum, "error" => "Row column count does not match header count."];
                continue;
            }

            $record = array_combine($header, $row);
            $records[] = $record;

            $question = trim($record['question'] ?? '');
            $answer = trim($record['answer'] ?? '');
            $title = trim($record['title'] ?? '');
            $content = trim($record['content'] ?? '');
            $category = trim($record['category'] ?? 'General');
            $tags = trim($record['tags'] ?? '');

            $type = !empty($question) ? 'faq' : 'document';

            if ($type === 'faq' && ($question === '' || $answer === '')) {
                $errors[] = ["row" => $rowNum, "error" => "Both 'question' and 'answer' must be non-empty."];
                continue;
            }

            if ($type === 'document' && ($title === '' || $content === '')) {
                $errors[] = ["row" => $rowNum, "error" => "Both 'title' and 'content' must be non-empty."];
                continue;
            }

            $validRecords[] = [
                'type' => $type,
                'question' => Security::sanitizeString($question),
                'answer' => Security::sanitizeHtml($answer),
                'title' => Security::sanitizeString($title),
                'content' => Security::sanitizeHtml($content),
                'category' => Security::sanitizeString($category),
                'tags' => Security::sanitizeString($tags),
            ];
        }

        fclose($handle);

        return [
            'records' => $records,
            'valid_records' => $validRecords,
            'errors' => $errors,
        ];
    }

    /**
     * Step 2: Confirm and commit valid parsed records into chatbot knowledge base
     */
    public function commitImport(int $jobId, int $userId, int $chatbotId, string $mode = 'add'): array
    {
        $job = Database::fetchOne(
            "SELECT * FROM import_jobs WHERE id = :id AND user_id = :uid LIMIT 1",
            ['id' => $jobId, 'uid' => $userId]
        );

        if (!$job) {
            throw new RuntimeException('Import job not found or unauthorized.');
        }

        $parsed = json_decode($job['preview_data'], true);
        $validRecords = $parsed['valid_records'] ?? [];

        if (empty($validRecords)) {
            throw new RuntimeException('No valid records found in this import.');
        }

        // Check subscription quota for knowledge entries
        if (!$this->subscriptionService->canAddKnowledge($userId, $chatbotId, count($validRecords))) {
            throw new RuntimeException('Importing ' . count($validRecords) . ' items exceeds your subscription plan knowledge limit.');
        }

        return Database::transaction(function () use ($jobId, $chatbotId, $mode, $validRecords, $job, $parsed) {
            if ($mode === 'replace') {
                Database::delete('chatbot_knowledge', 'chatbot_id = :bot_id', ['bot_id' => $chatbotId]);
            }

            $insertedCount = 0;
            foreach ($validRecords as $r) {
                // Duplicate prevention in merge mode
                if ($mode === 'merge' && $r['type'] === 'faq') {
                    $existing = Database::fetchOne(
                        "SELECT id FROM chatbot_knowledge WHERE chatbot_id = :bid AND question = :q LIMIT 1",
                        ['bid' => $chatbotId, 'q' => $r['question']]
                    );
                    if ($existing) {
                        Database::update('chatbot_knowledge', [
                            'answer' => $r['answer'],
                            'category' => $r['category'],
                            'tags' => $r['tags'],
                            'updated_at' => date('Y-m-d H:i:s'),
                        ], 'id = :id', ['id' => $existing['id']]);
                        $insertedCount++;
                        continue;
                    }
                }

                Database::insert('chatbot_knowledge', [
                    'chatbot_id' => $chatbotId,
                    'type' => $r['type'],
                    'question' => $r['question'],
                    'answer' => $r['answer'],
                    'title' => $r['title'],
                    'content' => $r['content'],
                    'category' => $r['category'],
                    'tags' => $r['tags'],
                    'source' => $job['file_type'] . '_import',
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $insertedCount++;
            }

            // Update job status
            Database::update('import_jobs', [
                'status' => 'completed',
                'chatbot_id' => $chatbotId,
                'import_mode' => $mode,
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $jobId]);

            return [
                'success' => true,
                'imported_count' => $insertedCount,
                'mode' => $mode,
            ];
        });
    }
}
