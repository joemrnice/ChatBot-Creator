-- ==============================================================================
-- ChatBot Creator Studio - Complete MySQL 8.0+ Schema
-- Multi-Tenant SaaS Architecture
-- Character Set: utf8mb4 / Collation: utf8mb4_unicode_ci / Engine: InnoDB
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. ACCOUNTS & AUTHENTICATION
-- ------------------------------------------------------------------------------

DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `oauth_accounts`;
DROP TABLE IF EXISTS `user_sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `email_verification_tokens`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NULL,
    `avatar_url` VARCHAR(500) NULL,
    `plan_id` BIGINT UNSIGNED NULL,
    `status` ENUM('active', 'inactive', 'suspended', 'pending_verification') NOT NULL DEFAULT 'active',
    `email_verified_at` DATETIME NULL,
    `two_factor_secret` VARCHAR(255) NULL,
    `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `last_login_at` DATETIME NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_status` (`status`),
    INDEX `idx_users_plan_id` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `is_system` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `module` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_roles` (
    `user_id` BIGINT UNSIGNED NOT NULL,
    `role_id` BIGINT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `role_id`),
    CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
    `role_id` BIGINT UNSIGNED NOT NULL,
    `permission_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `oauth_accounts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `provider` VARCHAR(50) NOT NULL, -- github, google, facebook
    `provider_user_id` VARCHAR(255) NOT NULL,
    `avatar` VARCHAR(500) NULL,
    `token_data` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_provider_user` (`provider`, `provider_user_id`),
    INDEX `idx_oauth_user_id` (`user_id`),
    CONSTRAINT `fk_oauth_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_sessions` (
    `id` VARCHAR(128) PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    INDEX `idx_sessions_user` (`user_id`),
    INDEX `idx_sessions_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(128) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pwd_reset_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_verification_tokens` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `token` VARCHAR(128) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_evt_user_id` (`user_id`),
    CONSTRAINT `fk_evt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login_attempts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `email` VARCHAR(255) NULL,
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `success` TINYINT(1) NOT NULL DEFAULT 0,
    INDEX `idx_login_attempts_ip` (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. SUBSCRIPTION PLANS & PAYPAL BILLING
-- ------------------------------------------------------------------------------

DROP TABLE IF EXISTS `billing_history`;
DROP TABLE IF EXISTS `payment_transactions`;
DROP TABLE IF EXISTS `paypal_webhook_events`;
DROP TABLE IF EXISTS `usage_records`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `paypal_plans`;
DROP TABLE IF EXISTS `paypal_products`;
DROP TABLE IF EXISTS `plan_features`;
DROP TABLE IF EXISTS `plans`;

CREATE TABLE `plans` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE, -- free, standard, pro, enterprise
    `tagline` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `price_monthly` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `price_yearly` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0, -- Highlight Pro as Best Value
    `sort_order` INT NOT NULL DEFAULT 0,
    `max_chatbots` INT NOT NULL DEFAULT 1,
    `monthly_messages` INT NOT NULL DEFAULT 100,
    `monthly_conversations` INT NOT NULL DEFAULT 50,
    `max_upload_size_mb` INT NOT NULL DEFAULT 5,
    `max_knowledge_entries` INT NOT NULL DEFAULT 20,
    `allow_custom_branding` TINYINT(1) NOT NULL DEFAULT 0,
    `allow_api_access` TINYINT(1) NOT NULL DEFAULT 0,
    `allow_ai_custom_keys` TINYINT(1) NOT NULL DEFAULT 0,
    `support_level` VARCHAR(50) NOT NULL DEFAULT 'community', -- community, standard, priority, dedicated
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `plan_features` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` BIGINT UNSIGNED NOT NULL,
    `feature_name` VARCHAR(150) NOT NULL,
    `feature_key` VARCHAR(100) NOT NULL,
    `feature_value` VARCHAR(100) NOT NULL DEFAULT '1',
    `is_included` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    CONSTRAINT `fk_pf_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `paypal_products` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `paypal_product_id` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `environment` ENUM('sandbox', 'live') NOT NULL DEFAULT 'sandbox',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `paypal_plans` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` BIGINT UNSIGNED NOT NULL,
    `paypal_plan_id` VARCHAR(100) NOT NULL UNIQUE,
    `billing_interval` ENUM('MONTH', 'YEAR') NOT NULL DEFAULT 'MONTH',
    `environment` ENUM('sandbox', 'live') NOT NULL DEFAULT 'sandbox',
    `status` VARCHAR(50) NOT NULL DEFAULT 'ACTIVE',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pp_plan_id` (`plan_id`),
    CONSTRAINT `fk_pp_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `subscriptions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `plan_id` BIGINT UNSIGNED NOT NULL,
    `paypal_subscription_id` VARCHAR(100) NULL UNIQUE,
    `paypal_plan_id` VARCHAR(100) NULL,
    `status` ENUM('active', 'trialing', 'past_due', 'cancelled', 'expired', 'suspended') NOT NULL DEFAULT 'active',
    `billing_interval` ENUM('month', 'year') NOT NULL DEFAULT 'month',
    `current_period_start` DATETIME NULL,
    `current_period_end` DATETIME NULL,
    `cancelled_at` DATETIME NULL,
    `cancel_reason` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sub_user` (`user_id`),
    INDEX `idx_sub_status` (`status`),
    CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sub_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payment_transactions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscription_id` BIGINT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `transaction_id` VARCHAR(100) NOT NULL UNIQUE, -- PayPal capture / transaction ID
    `payment_provider` VARCHAR(50) NOT NULL DEFAULT 'paypal',
    `amount` DECIMAL(10, 2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `status` ENUM('completed', 'pending', 'refunded', 'failed') NOT NULL DEFAULT 'completed',
    `payment_method` VARCHAR(50) NULL,
    `raw_payload` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tx_user` (`user_id`),
    INDEX `idx_tx_sub` (`subscription_id`),
    CONSTRAINT `fk_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tx_sub` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `paypal_webhook_events` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `event_id` VARCHAR(100) NOT NULL UNIQUE,
    `event_type` VARCHAR(150) NOT NULL,
    `summary` VARCHAR(255) NULL,
    `resource_type` VARCHAR(100) NULL,
    `status` ENUM('received', 'processed', 'ignored', 'failed') NOT NULL DEFAULT 'received',
    `raw_payload` LONGTEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `processed_at` DATETIME NULL,
    INDEX `idx_pwe_event_type` (`event_type`),
    INDEX `idx_pwe_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `billing_history` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `subscription_id` BIGINT UNSIGNED NULL,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `amount` DECIMAL(10, 2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `description` VARCHAR(255) NOT NULL,
    `billing_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('paid', 'unpaid', 'refunded') NOT NULL DEFAULT 'paid',
    CONSTRAINT `fk_bh_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `usage_records` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `metric_key` VARCHAR(100) NOT NULL, -- messages_count, conversations_count, storage_bytes
    `metric_value` BIGINT NOT NULL DEFAULT 0,
    `period_year_month` CHAR(7) NOT NULL, -- e.g. '2026-11'
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_metric_period` (`user_id`, `metric_key`, `period_year_month`),
    CONSTRAINT `fk_ur_user_usage` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. CHATBOT MANAGEMENT
-- ------------------------------------------------------------------------------

DROP TABLE IF EXISTS `chatbot_allowed_domains`;
DROP TABLE IF EXISTS `chatbot_integrations`;
DROP TABLE IF EXISTS `chatbot_api_tokens`;
DROP TABLE IF EXISTS `chatbot_knowledge`;
DROP TABLE IF EXISTS `chatbot_configurations`;
DROP TABLE IF EXISTS `chatbot_versions`;
DROP TABLE IF EXISTS `chatbots`;
DROP TABLE IF EXISTS `chatbot_templates`;

CREATE TABLE `chatbot_templates` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL, -- business, support, personal, education, real_estate, ecommerce
    `icon` VARCHAR(50) NOT NULL DEFAULT 'bot',
    `description` TEXT NOT NULL,
    `welcome_message` TEXT NOT NULL,
    `fallback_message` TEXT NOT NULL,
    `suggested_questions` JSON NULL,
    `system_instructions` TEXT NOT NULL,
    `sample_knowledge` JSON NULL,
    `primary_color` VARCHAR(20) NOT NULL DEFAULT '#2563eb',
    `theme` VARCHAR(20) NOT NULL DEFAULT 'light',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chatbots` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'business',
    `language` VARCHAR(10) NOT NULL DEFAULT 'en',
    `public_token` VARCHAR(64) NOT NULL UNIQUE,
    `status` ENUM('draft', 'published', 'suspended') NOT NULL DEFAULT 'draft',
    `visibility` ENUM('public', 'private', 'domain_restricted') NOT NULL DEFAULT 'public',
    `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `conversations_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_bots_user` (`user_id`),
    INDEX `idx_bots_public_token` (`public_token`),
    INDEX `idx_bots_status` (`status`),
    CONSTRAINT `fk_bots_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chatbot_configurations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chatbot_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    -- Behavior & Personality
    `personality` VARCHAR(50) NOT NULL DEFAULT 'friendly', -- professional, friendly, empathetic, technical
    `tone` VARCHAR(50) NOT NULL DEFAULT 'helpful',
    `response_style` VARCHAR(50) NOT NULL DEFAULT 'concise',
    `welcome_message` TEXT NOT NULL,
    `fallback_message` TEXT NOT NULL,
    `system_instructions` TEXT NULL,
    `suggested_questions` JSON NULL,
    `contact_capture_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `contact_capture_prompt` VARCHAR(255) NULL DEFAULT 'Would you like someone from our team to reach out?',
    `handoff_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `handoff_email` VARCHAR(255) NULL,
    -- Appearance
    `theme` ENUM('light', 'dark', 'auto') NOT NULL DEFAULT 'light',
    `primary_color` VARCHAR(20) NOT NULL DEFAULT '#2563eb',
    `secondary_color` VARCHAR(20) NOT NULL DEFAULT '#f8fafc',
    `header_title` VARCHAR(100) NOT NULL DEFAULT 'Chat Support',
    `header_subtitle` VARCHAR(150) NULL DEFAULT 'We typically reply in seconds',
    `avatar_url` VARCHAR(500) NULL,
    `widget_position` ENUM('bottom-right', 'bottom-left') NOT NULL DEFAULT 'bottom-right',
    `widget_border_radius` INT NOT NULL DEFAULT 16,
    `hide_watermark` TINYINT(1) NOT NULL DEFAULT 0,
    `custom_css` TEXT NULL,
    -- AI Provider settings
    `ai_provider` VARCHAR(50) NOT NULL DEFAULT 'fallback', -- fallback, openai, custom
    `ai_model` VARCHAR(100) NULL,
    `ai_temperature` DECIMAL(3, 2) NOT NULL DEFAULT 0.70,
    `ai_custom_api_key` TEXT NULL, -- encrypted
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_bot_cfg_bot` FOREIGN KEY (`chatbot_id`) REFERENCES `chatbots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chatbot_knowledge` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chatbot_id` BIGINT UNSIGNED NOT NULL,
    `type` ENUM('faq', 'document', 'text', 'link') NOT NULL DEFAULT 'faq',
    `question` TEXT NULL,
    `answer` LONGTEXT NULL,
    `title` VARCHAR(255) NULL,
    `content` LONGTEXT NULL,
    `category` VARCHAR(100) NULL,
    `tags` VARCHAR(255) NULL,
    `source` VARCHAR(255) NULL, -- manual, csv_import, json_import
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `hits_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ck_bot` (`chatbot_id`),
    FULLTEXT KEY `ft_ck_qa` (`question`, `answer`, `title`, `content`),
    CONSTRAINT `fk_ck_bot` FOREIGN KEY (`chatbot_id`) REFERENCES `chatbots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chatbot_versions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chatbot_id` BIGINT UNSIGNED NOT NULL,
    `version_number` INT NOT NULL,
    `snapshot` LONGTEXT NOT NULL, -- JSON snapshot of bot, config, knowledge
    `notes` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_bv_bot` FOREIGN KEY (`chatbot_id`) REFERENCES `chatbots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chatbot_allowed_domains` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chatbot_id` BIGINT UNSIGNED NOT NULL,
    `domain` VARCHAR(255) NOT NULL, -- e.g. example.com or *.example.com
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_cad_bot` (`chatbot_id`),
    CONSTRAINT `fk_cad_bot` FOREIGN KEY (`chatbot_id`) REFERENCES `chatbots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chatbot_api_tokens` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chatbot_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `token_hash` VARCHAR(128) NOT NULL UNIQUE,
    `last_used_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cat_bot` FOREIGN KEY (`chatbot_id`) REFERENCES `chatbots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chatbot_integrations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chatbot_id` BIGINT UNSIGNED NOT NULL,
    `integration_type` VARCHAR(50) NOT NULL, -- webhook, zapier, email, slack
    `config` JSON NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ci_bot` FOREIGN KEY (`chatbot_id`) REFERENCES `chatbots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. CONVERSATION MANAGEMENT
-- ------------------------------------------------------------------------------

DROP TABLE IF EXISTS `conversation_feedback`;
DROP TABLE IF EXISTS `conversation_events`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `conversations`;

CREATE TABLE `conversations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `chatbot_id` BIGINT UNSIGNED NOT NULL,
    `session_id` VARCHAR(100) NOT NULL,
    `visitor_ip` VARCHAR(45) NULL,
    `visitor_user_agent` TEXT NULL,
    `visitor_name` VARCHAR(100) NULL,
    `visitor_email` VARCHAR(255) NULL,
    `visitor_phone` VARCHAR(50) NULL,
    `messages_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `status` ENUM('active', 'closed', 'handed_off') NOT NULL DEFAULT 'active',
    `referrer_url` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_conv_bot` (`chatbot_id`),
    INDEX `idx_conv_session` (`session_id`),
    INDEX `idx_conv_created` (`created_at`),
    CONSTRAINT `fk_conv_bot` FOREIGN KEY (`chatbot_id`) REFERENCES `chatbots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `messages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` BIGINT UNSIGNED NOT NULL,
    `sender_type` ENUM('user', 'bot', 'system', 'agent') NOT NULL,
    `content` LONGTEXT NOT NULL,
    `matched_knowledge_id` BIGINT UNSIGNED NULL,
    `response_source` VARCHAR(50) NOT NULL DEFAULT 'knowledge', -- knowledge, ai, fallback, greeting
    `ai_model_used` VARCHAR(100) NULL,
    `tokens_used` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_msg_conv` (`conversation_id`),
    INDEX `idx_msg_created` (`created_at`),
    CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_msg_mk` FOREIGN KEY (`matched_knowledge_id`) REFERENCES `chatbot_knowledge` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `conversation_feedback` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `message_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    `rating` ENUM('up', 'down') NOT NULL,
    `comment` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cf_msg` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `conversation_events` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` BIGINT UNSIGNED NOT NULL,
    `event_name` VARCHAR(100) NOT NULL, -- opened, message_sent, contact_captured, handoff_requested, closed
    `metadata` JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ce_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. DATA IMPORTS & EXPORTS
-- ------------------------------------------------------------------------------

DROP TABLE IF EXISTS `import_errors`;
DROP TABLE IF EXISTS `import_jobs`;
DROP TABLE IF EXISTS `export_jobs`;

CREATE TABLE `import_jobs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `chatbot_id` BIGINT UNSIGNED NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_type` ENUM('json', 'csv') NOT NULL,
    `file_size` INT NOT NULL,
    `total_records` INT NOT NULL DEFAULT 0,
    `valid_records` INT NOT NULL DEFAULT 0,
    `invalid_records` INT NOT NULL DEFAULT 0,
    `import_mode` ENUM('add', 'merge', 'replace') NOT NULL DEFAULT 'add',
    `status` ENUM('pending', 'parsed', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    `preview_data` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME NULL,
    INDEX `idx_ij_user` (`user_id`),
    CONSTRAINT `fk_ij_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ij_bot` FOREIGN KEY (`chatbot_id`) REFERENCES `chatbots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_errors` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `import_job_id` BIGINT UNSIGNED NOT NULL,
    `row_number` INT NOT NULL,
    `field_name` VARCHAR(100) NULL,
    `error_message` TEXT NOT NULL,
    `raw_row` TEXT NULL,
    CONSTRAINT `fk_ie_job` FOREIGN KEY (`import_job_id`) REFERENCES `import_jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `export_jobs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `chatbot_id` BIGINT UNSIGNED NOT NULL,
    `export_type` ENUM('json_config', 'csv_knowledge', 'zip_package', 'conversations_csv') NOT NULL,
    `file_path` VARCHAR(500) NULL,
    `status` ENUM('completed', 'failed') NOT NULL DEFAULT 'completed',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ej_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ej_bot` FOREIGN KEY (`chatbot_id`) REFERENCES `chatbots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. ADMINISTRATION & SYSTEM
-- ------------------------------------------------------------------------------

DROP TABLE IF EXISTS `support_messages`;
DROP TABLE IF EXISTS `support_tickets`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `system_settings`;

CREATE TABLE `system_settings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `key_name` VARCHAR(100) NOT NULL UNIQUE,
    `value_content` LONGTEXT NULL,
    `is_secret` TINYINT(1) NOT NULL DEFAULT 0,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL, -- user_login, bot_created, bot_deleted, subscription_change, settings_updated
    `entity_type` VARCHAR(50) NULL, -- bot, user, subscription, setting
    `entity_id` BIGINT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `details` JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_al_user` (`user_id`),
    INDEX `idx_al_action` (`action`),
    INDEX `idx_al_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info',
    `link` VARCHAR(500) NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_user` (`user_id`, `is_read`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `announcements` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `type` ENUM('info', 'alert', 'feature', 'maintenance') NOT NULL DEFAULT 'info',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `starts_at` DATETIME NULL,
    `ends_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_tickets` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_number` VARCHAR(32) NOT NULL UNIQUE,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `status` ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
    `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_st_user` (`user_id`),
    CONSTRAINT `fk_st_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_messages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `is_admin_reply` TINYINT(1) NOT NULL DEFAULT 0,
    `message` LONGTEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_sm_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
