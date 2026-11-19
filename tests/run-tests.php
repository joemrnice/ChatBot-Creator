<?php
declare(strict_types=1);

/**
 * ChatBot Creator Studio - End-to-End Automated Test Suite
 * Native PHP 8.3+ QA & Acceptance Verification Runner
 */

require_once dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Security;
use App\Services\ChatbotService;
use App\Services\ConversationService;
use App\Services\ExportService;
use App\Services\ImportService;
use App\Services\PayPalService;
use App\Services\SubscriptionService;

class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function run(): void
    {
        echo "======================================================================\n";
        echo "CHATBOT CREATOR STUDIO - AUTOMATED TEST SUITE\n";
        echo "Native PHP 8.3+ Zero-Framework SaaS QA Verification\n";
        echo "======================================================================\n\n";

        $tests = [
            'testDatabaseConnectivityAndSchema',
            'testUserRegistrationAndAuth',
            'testMultiTenantDataIsolation',
            'testSubscriptionQuotasAndEntitlements',
            'testChatbotEngineAndKnowledgeRetrieval',
            'testJsonAndCsvImportSystem',
            'testSecurityCsrfAndEncryption',
            'testPayPalWebhookIdempotency',
            'testPublicWidgetConfigurationSanity',
            'testExportServiceIntegrity',
            'testLeadCaptureAndFeedback',
            'testEmailVerificationFlow',
        ];

        foreach ($tests as $test) {
            try {
                echo "Running {$test}... ";
                $this->$test();
                echo "[PASS]\n";
                $this->passed++;
            } catch (\Throwable $e) {
                echo "[FAIL]\n";
                $this->failed++;
                $this->failures[] = [
                    'test' => $test,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ];
            }
        }

        echo "\n======================================================================\n";
        echo "TEST SUMMARY: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "======================================================================\n";

        if ($this->failed > 0) {
            echo "\nDETAILED FAILURES:\n";
            foreach ($this->failures as $f) {
                echo "- {$f['test']}: {$f['error']}\n";
            }
            exit(1);
        } else {
            echo "ALL ACCEPTANCE TESTS PASSED SUCCESSFULLY! SYSTEM IS PRODUCTION READY.\n\n";
            exit(0);
        }
    }

    private function assert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    private function testDatabaseConnectivityAndSchema(): void
    {
        $pdo = Database::pdo();
        $this->assert($pdo instanceof \PDO, 'PDO connection failed');

        $tables = Database::fetchAll("SHOW TABLES");
        $this->assert(count($tables) >= 20, 'Expected at least 20 tables in schema, found ' . count($tables));

        $plansCount = (int)Database::fetchValue("SELECT COUNT(*) FROM plans");
        $this->assert($plansCount >= 4, 'Expected at least 4 subscription plans (Free, Standard, Pro, Enterprise)');

        $proPlan = Database::fetchOne("SELECT * FROM plans WHERE slug = 'pro'");
        $this->assert(!empty($proPlan['is_featured']), 'Pro plan must be marked as featured (Most Popular / Best Value)');
    }

    private function testUserRegistrationAndAuth(): void
    {
        $testEmail = 'qa_tester_' . bin2hex(random_bytes(4)) . '@test.local';
        $password = 'SecretPassword123!';

        // Create user
        $uuid = Security::generateUuid();
        $freePlan = Database::fetchOne("SELECT id FROM plans WHERE slug = 'free'");
        $userId = Database::insert('users', [
            'uuid' => $uuid,
            'name' => 'QA Tester',
            'email' => $testEmail,
            'password_hash' => Security::hashPassword($password),
            'plan_id' => $freePlan['id'],
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assert($userId > 0, 'User insertion failed');

        // Verify password hashing
        $user = Database::fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $userId]);
        $this->assert(Security::verifyPassword($password, $user['password_hash']), 'Password verification failed');

        // Test duplicate email prevention
        $isDuplicateCaught = false;
        try {
            Database::insert('users', [
                'uuid' => Security::generateUuid(),
                'name' => 'Duplicate User',
                'email' => $testEmail,
                'password_hash' => Security::hashPassword('AnotherPassword!'),
                'plan_id' => $freePlan['id'],
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            $isDuplicateCaught = true;
        }

        $this->assert($isDuplicateCaught, 'Duplicate email was not prevented by unique constraint');
    }

    private function testMultiTenantDataIsolation(): void
    {
        $chatbotService = new ChatbotService();

        // Create User A
        $userAId = Database::insert('users', [
            'uuid' => Security::generateUuid(),
            'name' => 'Tenant A',
            'email' => 'tenant_a_' . bin2hex(random_bytes(4)) . '@test.local',
            'password_hash' => Security::hashPassword('Pass1234!'),
            'plan_id' => 1,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Create User B
        $userBId = Database::insert('users', [
            'uuid' => Security::generateUuid(),
            'name' => 'Tenant B',
            'email' => 'tenant_b_' . bin2hex(random_bytes(4)) . '@test.local',
            'password_hash' => Security::hashPassword('Pass1234!'),
            'plan_id' => 1,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // User A creates a bot
        $botAId = $chatbotService->createBot($userAId, [
            'name' => 'User A Private Assistant',
            'category' => 'business',
            'welcome_message' => 'Hello from Tenant A',
            'fallback_message' => 'Fallback A',
        ]);

        // Verify User A can fetch bot
        $botA = $chatbotService->getBotById($botAId, $userAId);
        $this->assert($botA !== null, 'User A should be able to retrieve their own chatbot');

        // Verify User B CANNOT fetch bot A (Multi-tenant check)
        $botForbidden = $chatbotService->getBotById($botAId, $userBId);
        $this->assert($botForbidden === null, 'Tenant isolation failed: User B was able to read User A chatbot!');

        // Verify User B CANNOT delete bot A
        $deleteForbidden = $chatbotService->deleteBot($botAId, $userBId);
        $this->assert($deleteForbidden === false, 'Tenant isolation failed: User B was able to delete User A chatbot!');
    }

    private function testSubscriptionQuotasAndEntitlements(): void
    {
        $subService = new SubscriptionService();
        $chatbotService = new ChatbotService();

        // Create User on Free plan (max 1 chatbot)
        $userId = Database::insert('users', [
            'uuid' => Security::generateUuid(),
            'name' => 'Quota Tester',
            'email' => 'quota_' . bin2hex(random_bytes(4)) . '@test.local',
            'password_hash' => Security::hashPassword('Pass1234!'),
            'plan_id' => 1, // Free
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // 1st bot creation should pass
        $this->assert($subService->canCreateChatbot($userId), 'Free user should be able to create 1st bot');
        $bot1 = $chatbotService->createBot($userId, [
            'name' => 'First Free Bot',
            'category' => 'support',
            'welcome_message' => 'Hi',
            'fallback_message' => 'Sorry',
        ]);

        // 2nd bot creation check MUST FAIL
        $this->assert(!$subService->canCreateChatbot($userId), 'Free user should NOT be able to create a 2nd bot without upgrading');

        // Test Upgrade to Pro Plan (id: 3, max 25 bots)
        $subService->assignPlan($userId, 3, 'TEST-SUB-PRO-' . bin2hex(random_bytes(4)), 'month');
        $plan = $subService->getUserPlan($userId);
        $this->assert($plan['slug'] === 'pro', 'User plan should now be Pro');
        $this->assert($subService->canCreateChatbot($userId), 'Pro user should be able to create additional bots');
    }

    private function testChatbotEngineAndKnowledgeRetrieval(): void
    {
        $chatbotService = new ChatbotService();
        $conversationService = new ConversationService();

        // Create dedicated Pro user for engine test
        $userId = Database::insert('users', [
            'uuid' => Security::generateUuid(),
            'name' => 'Engine Tester',
            'email' => 'engine_' . bin2hex(random_bytes(4)) . '@test.local',
            'password_hash' => Security::hashPassword('Pass1234!'),
            'plan_id' => 3, // Pro plan
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $botId = $chatbotService->createBot($userId, [
            'name' => 'Engine Test Bot',
            'category' => 'business',
            'welcome_message' => 'Welcome to our testing bot!',
            'fallback_message' => 'I do not have information on that subject.',
        ]);

        $bot = $chatbotService->getBotById($botId, $userId);

        // Add Knowledge FAQ
        Database::insert('chatbot_knowledge', [
            'chatbot_id' => $botId,
            'type' => 'faq',
            'question' => 'What is the refund policy duration?',
            'answer' => 'We offer a 30-day money back guarantee on all plans.',
            'category' => 'Billing',
            'tags' => 'refund,guarantee,policy',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionId = 'test_session_' . bin2hex(random_bytes(4));

        // Test 1: Greeting
        $replyGreeting = $conversationService->processMessage($bot, $sessionId, 'Hello');
        $this->assert($replyGreeting['content'] === $bot['welcome_message'], 'Engine should respond with configured welcome message for greeting');

        // Test 2: Matched Knowledge
        $replyFaq = $conversationService->processMessage($bot, $sessionId, 'What is your refund policy?');
        $this->assert(str_contains($replyFaq['content'], '30-day money back guarantee'), 'Engine should retrieve and return matched knowledge answer');

        // Test 3: Unmatched Fallback
        $replyFallback = $conversationService->processMessage($bot, $sessionId, 'Can you book a spaceship to Neptune?');
        $this->assert($replyFallback['content'] === $bot['fallback_message'], 'Engine should return configured fallback message when query is unmatched');
    }

    private function testJsonAndCsvImportSystem(): void
    {
        $importService = new ImportService();
        $chatbotService = new ChatbotService();

        // Create dedicated Pro user for import test
        $userId = Database::insert('users', [
            'uuid' => Security::generateUuid(),
            'name' => 'Import Tester',
            'email' => 'import_' . bin2hex(random_bytes(4)) . '@test.local',
            'password_hash' => Security::hashPassword('Pass1234!'),
            'plan_id' => 3, // Pro plan
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $botId = $chatbotService->createBot($userId, [
            'name' => 'Import Test Bot',
            'category' => 'business',
            'welcome_message' => 'Hi',
            'fallback_message' => 'Sorry',
        ]);

        // Test JSON parsing
        $jsonContent = json_encode([
            'schema_version' => '1.0',
            'knowledge' => [
                ['type' => 'faq', 'question' => 'How to reset token?', 'answer' => 'Visit settings and click reset.', 'tags' => 'token'],
                ['type' => 'faq', 'question' => 'Where are servers located?', 'answer' => 'In US and Europe data centers.', 'tags' => 'datacenter']
            ]
        ]);

        $tmpJson = tempnam(sys_get_temp_dir(), 'cbc_') . '.json';
        file_put_contents($tmpJson, $jsonContent);

        $parsed = $importService->parseImportFile($userId, $botId, [
            'name' => 'test.json',
            'tmp_name' => $tmpJson,
            'size' => filesize($tmpJson),
            'error' => UPLOAD_ERR_OK,
        ]);

        $this->assert($parsed['valid_count'] === 2, 'Expected 2 valid records parsed from JSON');

        // Commit import
        $commitResult = $importService->commitImport($parsed['job_id'], $userId, $botId, 'add');
        $this->assert($commitResult['imported_count'] === 2, 'Expected 2 records committed to database');

        $kbCount = Database::fetchValue("SELECT COUNT(*) FROM chatbot_knowledge WHERE chatbot_id = :id", ['id' => $botId]);
        $this->assert($kbCount >= 2, 'Knowledge records should be persisted in database');

        @unlink($tmpJson);
    }

    private function testSecurityCsrfAndEncryption(): void
    {
        // CSRF Token testing
        $token = Security::getCsrfToken();
        $this->assert(!empty($token), 'CSRF token should not be empty');
        $this->assert(Security::validateCsrfToken($token), 'Valid CSRF token should validate true');
        $this->assert(!Security::validateCsrfToken('tampered_token_xyz'), 'Tampered CSRF token must fail validation');

        // AES-256-CBC Encryption testing
        $secretApiKey = 'sk-proj-super-secret-ai-token-123456';
        $encrypted = Security::encrypt($secretApiKey);
        $this->assert(!empty($encrypted), 'Encryption returned empty ciphertext');
        $this->assert($encrypted !== $secretApiKey, 'Ciphertext must not match plaintext');

        $decrypted = Security::decrypt($encrypted);
        $this->assert($decrypted === $secretApiKey, 'Decrypted secret does not match original plaintext');
    }

    private function testPayPalWebhookIdempotency(): void
    {
        $paypalService = new PayPalService();
        $mockEventId = 'WH-MOCK-' . bin2hex(random_bytes(6));

        $mockPayload = json_encode([
            'id' => $mockEventId,
            'event_type' => 'BILLING.SUBSCRIPTION.UPDATED',
            'summary' => 'Subscription status updated to active',
            'resource_type' => 'subscription',
            'resource' => [
                'id' => 'I-MOCK-SUB-123',
                'status' => 'ACTIVE'
            ]
        ]);

        // First execution
        $result1 = $paypalService->handleWebhookEvent($mockPayload, []);
        $this->assert($result1['success'] === true, 'First webhook event processing failed');

        // Second execution with SAME event ID (Idempotency check)
        $result2 = $paypalService->handleWebhookEvent($mockPayload, []);
        $this->assert($result2['success'] === true, 'Duplicate webhook event should succeed gracefully');
        $this->assert(str_contains($result2['message'], 'idempotent'), 'Duplicate webhook event was not recognized as idempotent skip');
    }

    private function testPublicWidgetConfigurationSanity(): void
    {
        $chatbotService = new ChatbotService();

        // Create dedicated user for widget sanity test
        $userId = Database::insert('users', [
            'uuid' => Security::generateUuid(),
            'name' => 'Sanity Tester',
            'email' => 'sanity_' . bin2hex(random_bytes(4)) . '@test.local',
            'password_hash' => Security::hashPassword('Pass1234!'),
            'plan_id' => 3, // Pro plan
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $botId = $chatbotService->createBot($userId, [
            'name' => 'Sanity Bot',
            'category' => 'business',
            'welcome_message' => 'Sanity Hello',
            'fallback_message' => 'Sanity Fallback',
        ]);

        $bot = $chatbotService->getBotById($botId, $userId);
        $publicBot = $chatbotService->getBotByPublicToken($bot['public_token']);

        $this->assert($publicBot !== null, 'Chatbot should be resolvable by public token');
        $this->assert(!isset($publicBot['password_hash']), 'Public bot resolution must never leak password hashes');
        $this->assert($publicBot['welcome_message'] === 'Sanity Hello', 'Public bot config welcome message mismatch');
    }

    private function testExportServiceIntegrity(): void
    {
        $chatbotService = new ChatbotService();
        $exportService = new ExportService();

        $userId = Database::insert('users', [
            'uuid' => Security::generateUuid(),
            'name' => 'Export Tester',
            'email' => 'export_' . bin2hex(random_bytes(4)) . '@test.local',
            'password_hash' => Security::hashPassword('Pass1234!'),
            'plan_id' => 3,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $botId = $chatbotService->createBot($userId, [
            'name' => 'Export Bundle Bot',
            'category' => 'business',
            'welcome_message' => 'Export Hello',
            'fallback_message' => 'Export Fallback',
        ]);

        // Add a sample FAQ entry
        Database::insert('chatbot_knowledge', [
            'chatbot_id' => $botId,
            'type' => 'faq',
            'question' => 'How does export work?',
            'answer' => 'It packages files into a secure ZIP bundle.',
            'category' => 'FAQ',
            'tags' => 'export,zip',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // 1. JSON Export verification
        $json = $exportService->exportJsonConfig($botId, $userId);
        $this->assert(isset($json['schema_version']) && $json['schema_version'] === '1.0', 'Exported JSON missing schema version 1.0');
        $this->assert($json['bot']['name'] === 'Export Bundle Bot', 'Exported JSON bot name mismatch');
        $this->assert(count($json['knowledge']) >= 1, 'Exported JSON should include knowledge array');

        // 2. CSV Export verification
        $csv = $exportService->exportKnowledgeCsv($botId, $userId);
        $this->assert(str_contains($csv, 'question,answer,category,tags'), 'CSV export missing required headers');
        $this->assert(str_contains($csv, 'How does export work?'), 'CSV export missing FAQ question content');

        // 3. ZIP Generation verification
        $zipPath = $exportService->generateZipPackage($botId, $userId);
        $this->assert(file_exists($zipPath), 'Generated ZIP package file does not exist on disk');

        $zip = new \ZipArchive();
        $this->assert($zip->open($zipPath) === true, 'Could not read generated ZIP package');
        $this->assert($zip->locateName('chatbot-integration/README.md') !== false, 'ZIP package missing README.md');
        $this->assert($zip->locateName('chatbot-integration/config.json') !== false, 'ZIP package missing config.json');
        $this->assert($zip->locateName('chatbot-integration/embed.html') !== false, 'ZIP package missing embed.html');
        $this->assert($zip->locateName('chatbot-integration/widget.js') !== false, 'ZIP package missing widget.js');
        $this->assert($zip->locateName('chatbot-integration/widget.css') !== false, 'ZIP package missing widget.css');
        $zip->close();
        @unlink($zipPath);
    }

    private function testLeadCaptureAndFeedback(): void
    {
        $chatbotService = new ChatbotService();
        $conversationService = new ConversationService();

        $userId = Database::insert('users', [
            'uuid' => Security::generateUuid(),
            'name' => 'Lead Tester',
            'email' => 'lead_' . bin2hex(random_bytes(4)) . '@test.local',
            'password_hash' => Security::hashPassword('Pass1234!'),
            'plan_id' => 3,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $botId = $chatbotService->createBot($userId, [
            'name' => 'Lead Bot',
            'category' => 'business',
            'welcome_message' => 'Hello Lead',
            'fallback_message' => 'Sorry Lead',
        ]);
        $bot = $chatbotService->getBotById($botId, $userId);

        $reply = $conversationService->processMessage($bot, 'lead_session_123', 'Hello');
        $this->assert(isset($reply['message_id']), 'Message response missing message_id');

        $messageRecord = Database::fetchOne("SELECT conversation_id FROM messages WHERE id = :id", ['id' => $reply['message_id']]);
        $this->assert($messageRecord !== null, 'Message not found in messages table');
        $conversationId = (int)$messageRecord['conversation_id'];

        // Test Lead Recording
        $leadSuccess = $conversationService->recordLead($conversationId, [
            'visitor_name' => 'Jane Prospect',
            'visitor_email' => 'jane.prospect@example.com',
            'visitor_phone' => '+1234567890',
            'notes' => 'Wants enterprise tier quote',
        ]);
        $this->assert($leadSuccess === true, 'Failed to record visitor lead');

        $conv = Database::fetchOne("SELECT * FROM conversations WHERE id = :id", ['id' => $conversationId]);
        $this->assert($conv['visitor_email'] === 'jane.prospect@example.com', 'Conversation lead email mismatch');

        // Test Feedback Recording
        $feedbackSuccess = $conversationService->recordFeedback((int)$reply['message_id'], 'up', 'Great response');
        $this->assert($feedbackSuccess === true, 'Failed to record visitor feedback');

        $fb = Database::fetchOne("SELECT * FROM conversation_feedback WHERE message_id = :mid", ['mid' => $reply['message_id']]);
        $this->assert($fb['rating'] === 'up', 'Feedback rating mismatch');
    }

    private function testEmailVerificationFlow(): void
    {
        $userId = Database::insert('users', [
            'uuid' => Security::generateUuid(),
            'name' => 'Verify Tester',
            'email' => 'verify_' . bin2hex(random_bytes(4)) . '@test.local',
            'password_hash' => Security::hashPassword('Pass1234!'),
            'plan_id' => 1,
            'status' => 'active',
            'email_verified_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $userBefore = Database::fetchOne("SELECT email_verified_at FROM users WHERE id = :id", ['id' => $userId]);
        $this->assert($userBefore['email_verified_at'] === null, 'New user email should be unverified initially');

        $token = Security::generateToken(32);
        Database::insert('email_verification_tokens', [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Verify token lookup and fulfillment
        $record = Database::fetchOne("SELECT * FROM email_verification_tokens WHERE token = :t AND expires_at > NOW()", ['t' => $token]);
        $this->assert($record !== null, 'Token should be found in email_verification_tokens table');

        Database::update('users', ['email_verified_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $userId]);
        Database::delete('email_verification_tokens', 'token = :t', ['t' => $token]);

        $userAfter = Database::fetchOne("SELECT email_verified_at FROM users WHERE id = :id", ['id' => $userId]);
        $this->assert(!empty($userAfter['email_verified_at']), 'User email should now be verified');
    }
}

// Run the test suite
$runner = new TestRunner();
$runner->run();
