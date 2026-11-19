-- ==============================================================================
-- ChatBot Creator Studio - Initial Seed Data
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. ROLES & PERMISSIONS
-- ------------------------------------------------------------------------------

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `created_at`) VALUES
(1, 'Super Administrator', 'superadmin', 'Full platform control, billing, user management, and system configuration', 1, NOW()),
(2, 'Administrator', 'admin', 'Platform management, chatbot moderation, support oversight', 1, NOW()),
(3, 'Billing Administrator', 'billing_admin', 'Access to payment histories, subscription plans, and invoice management', 1, NOW()),
(4, 'Support Administrator', 'support_admin', 'Customer support ticket handling and user communication', 1, NOW()),
(5, 'User', 'user', 'Regular registered platform user with personal chatbot studio workspace', 1, NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `permissions` (`id`, `name`, `slug`, `module`, `description`, `created_at`) VALUES
(1, 'Manage Users', 'users.manage', 'users', 'Create, suspend, activate, and inspect platform users', NOW()),
(2, 'Manage Chatbots', 'chatbots.manage', 'chatbots', 'Moderate, view, and suspend any chatbot platform-wide', NOW()),
(3, 'Manage Plans & Billing', 'billing.manage', 'billing', 'Configure subscription plans, view payments, and manage PayPal mapping', NOW()),
(4, 'Manage System Settings', 'settings.manage', 'settings', 'Configure OAuth credentials, PayPal credentials, AI providers, and email', NOW()),
(5, 'View Audit Logs', 'audit.view', 'audit', 'Inspect system audit trails and security logs', NOW()),
(6, 'Handle Support Tickets', 'support.handle', 'support', 'Answer user tickets and manage status', NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6),
(2, 1), (2, 2), (2, 5), (2, 6),
(3, 3), (3, 5),
(4, 6)
ON DUPLICATE KEY UPDATE `role_id` = VALUES(`role_id`);

-- ------------------------------------------------------------------------------
-- 2. SUBSCRIPTION PLANS
-- ------------------------------------------------------------------------------

INSERT INTO `plans` (`id`, `name`, `slug`, `tagline`, `description`, `price_monthly`, `price_yearly`, `currency`, `is_active`, `is_featured`, `sort_order`, `max_chatbots`, `monthly_messages`, `monthly_conversations`, `max_upload_size_mb`, `max_knowledge_entries`, `allow_custom_branding`, `allow_api_access`, `allow_ai_custom_keys`, `support_level`, `created_at`) VALUES
(1, 'Free', 'free', 'Perfect for getting started and experimenting', 'Explore chatbot creation with core customization and basic website embedding.', 0.00, 0.00, 'USD', 1, 0, 1, 1, 150, 50, 5, 25, 0, 0, 0, 'community', NOW()),
(2, 'Standard', 'standard', 'Ideal for freelancers, creators & small businesses', 'Elevate your customer interactions with up to 5 chatbots, knowledge bases, and custom exports.', 19.00, 190.00, 'USD', 1, 0, 2, 5, 3000, 1000, 25, 250, 1, 0, 0, 'standard', NOW()),
(3, 'Pro', 'pro', 'MOST POPULAR — BEST VALUE', 'Comprehensive solution for growing organizations requiring high message limits, AI provider keys, and custom branding.', 49.00, 490.00, 'USD', 1, 1, 3, 25, 15000, 5000, 100, 1500, 1, 1, 1, 'priority', NOW()),
(4, 'Enterprise', 'enterprise', 'For agencies, institutions and high-volume operations', 'Tailored limits, team collaboration, dedicated account manager, and full API infrastructure.', 199.00, 1990.00, 'USD', 1, 0, 4, 100, 100000, 50000, 500, 10000, 1, 1, 1, 'dedicated', NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `plan_features` (`plan_id`, `feature_name`, `feature_key`, `feature_value`, `is_included`, `sort_order`) VALUES
-- Free
(1, '1 Active Chatbot', 'chatbots', '1', 1, 1),
(1, '150 Messages / month', 'messages', '150', 1, 2),
(1, 'Standard Knowledge Base (25 FAQs)', 'knowledge', '25', 1, 3),
(1, 'CDN Embed Widget', 'embed', '1', 1, 4),
(1, 'Downloadable ZIP Package', 'download_zip', '1', 1, 5),
(1, 'JSON & CSV Import/Export', 'import_export', '1', 1, 6),
(1, 'Remove Branding', 'white_label', '0', 0, 7),
(1, 'Custom AI Provider Keys', 'custom_ai', '0', 0, 8),

-- Standard
(2, '5 Active Chatbots', 'chatbots', '5', 1, 1),
(2, '3,000 Messages / month', 'messages', '3000', 1, 2),
(2, 'Extended Knowledge Base (250 FAQs)', 'knowledge', '250', 1, 3),
(2, 'Remove Widget Watermark', 'white_label', '1', 1, 4),
(2, 'CDN Embed & ZIP Packages', 'embed', '1', 1, 5),
(2, 'Lead Capture & Notification', 'leads', '1', 1, 6),
(2, 'Standard Analytics', 'analytics', '1', 1, 7),
(2, 'Email Support', 'support', 'standard', 1, 8),

-- Pro (Featured)
(3, '25 Active Chatbots', 'chatbots', '25', 1, 1),
(3, '15,000 Messages / month', 'messages', '15000', 1, 2),
(3, 'Massive Knowledge Base (1,500 items)', 'knowledge', '1500', 1, 3),
(3, 'Custom AI Provider Integration (OpenAI)', 'custom_ai', '1', 1, 4),
(3, 'Full White-labeling & Custom CSS', 'white_label', '1', 1, 5),
(3, 'Advanced Domain Restrictions', 'security', '1', 1, 6),
(3, 'REST API Access', 'api', '1', 1, 7),
(3, 'Priority 24/7 Support', 'support', 'priority', 1, 8),

-- Enterprise
(4, '100+ Chatbots (Customizable)', 'chatbots', '100', 1, 1),
(4, '100,000 Messages / month', 'messages', '100000', 1, 2),
(4, 'Unlimited Knowledge Documents', 'knowledge', '10000', 1, 3),
(4, 'Multi-seat Team Collaboration', 'team', '1', 1, 4),
(4, 'Custom AI Models & Endpoints', 'custom_ai', '1', 1, 5),
(4, 'Dedicated SLA & Account Manager', 'support', 'dedicated', 1, 6),
(4, 'Custom Contract & Invoicing', 'billing', 'custom', 1, 7)
ON DUPLICATE KEY UPDATE `feature_name` = VALUES(`feature_name`);

-- ------------------------------------------------------------------------------
-- 3. CHATBOT TEMPLATES
-- ------------------------------------------------------------------------------

INSERT INTO `chatbot_templates` (`id`, `name`, `category`, `icon`, `description`, `welcome_message`, `fallback_message`, `suggested_questions`, `system_instructions`, `sample_knowledge`, `primary_color`, `theme`, `is_featured`, `created_at`) VALUES
(1, 'Business & Sales Concierge', 'business', 'briefcase', 'Assists potential customers with service descriptions, pricing inquiries, schedule requests, and collects qualified leads.', 'Hello! Welcome to our studio. How can I assist you with our services and pricing today?', 'I apologize, but I do not have specific information regarding that request. Would you like to leave your email so our sales team can contact you?', '["What services do you offer?", "Can I see your pricing plans?", "How do I book a consultation?", "Where are you located?"]', 'You are a professional, courteous sales concierge. Explain offerings clearly, emphasize value, and prompt visitors to connect with the team.', '[{"question":"What services do you provide?","answer":"We provide digital transformation, custom software engineering, cloud integration, and 24/7 customer experience automation.","tags":"services,offerings"},{"question":"What are your operating hours?","answer":"Our core business hours are Monday through Friday from 8:00 AM to 6:00 PM EST. Our automated assistant is available 24/7.","tags":"hours,schedule"},{"question":"How can I get a custom quote?","answer":"You can leave your email right here in this chat, or email sales@company.com with your project specifications.","tags":"quote,pricing"}]', '#2563eb', 'light', 1, NOW()),

(2, 'Customer Support Specialist', 'support', 'life-buoy', 'Resolves common technical questions, guides users through troubleshooting, and reduces ticket volume.', 'Hi there! I am your Support Assistant. Describe the problem you are experiencing or select a topic below.', 'I could not find a troubleshooting step for that issue. Please email support@company.com or submit a ticket in your dashboard.', '["How do I reset my password?", "Where do I find my API key?", "How do I upgrade my plan?", "Can I export my chat transcripts?"]', 'You are an empathetic, precise support agent. Guide users step-by-step with clear instructions.', '[{"question":"How do I reset my password?","answer":"Click the \\"Forgot Password\\" link on the login page, enter your registered email address, and check your inbox for the secure reset link.","tags":"account,password"},{"question":"Where do I find my API key?","answer":"Navigate to Studio -> Settings -> API Tokens to generate and copy your secret key.","tags":"api,keys"},{"question":"How do I cancel my subscription?","answer":"Go to Studio -> Billing -> Manage Subscription and click \\"Cancel Subscription\\". Your access remains active until the end of your billing cycle.","tags":"billing,cancel"}]', '#0ea5e9', 'light', 1, NOW()),

(3, 'Academic & Study Tutor', 'education', 'graduation-cap', 'Acts as a subject tutor, guides research, explains complex concepts, and quizzes students.', 'Greetings! I am your AI Study Companion. What subject or concept would you like to explore today?', 'That concept is outside my current syllabus knowledge. Try asking about our core study subjects.', '["Explain photosynthesis simply", "What is Newton\'s third law?", "Give me a 3-question math quiz", "How to structure an essay?"]', 'You are an encouraging, pedagogical tutor. Explain concepts using analogies and verify student comprehension.', '[{"question":"How to structure an academic essay?","answer":"A classic essay consists of: 1) Introduction with thesis statement, 2) Body paragraphs each presenting evidence for one argument, and 3) Conclusion synthesizing your findings.","tags":"study,writing"},{"question":"What is active recall?","answer":"Active recall is a learning technique that involves actively retrieving information from memory rather than passively reviewing notes.","tags":"study,method"}]', '#10b981', 'light', 1, NOW()),

(4, 'Real Estate Property Advisor', 'real_estate', 'home', 'Provides property details, neighborhood highlights, viewing scheduling, and mortgage estimation guidance.', 'Welcome to Premier Realty! Looking for a new home or investment? I can help you browse properties and schedule viewings.', 'I do not have current listing details matching those exact criteria. Would you like a licensed agent to send you matching listings?', '["What properties are available?", "How do I schedule a viewing?", "What are typical closing costs?", "Do you handle commercial leases?"]', 'You are a knowledgeable, inviting real estate assistant. Emphasize property features, neighborhood perks, and encourage viewing bookings.', '[{"question":"How do I schedule a property viewing?","answer":"Share your preferred date and time along with the property ID or address, and our agent will confirm your appointment within 2 hours.","tags":"viewing,tour"},{"question":"What documents do I need for pre-approval?","answer":"Typically: last 2 years tax returns, W-2s, 2 months bank statements, and government-issued ID.","tags":"mortgage,finance"}]', '#8b5cf6', 'light', 0, NOW()),

(5, 'E-Commerce Store Assistant', 'ecommerce', 'shopping-bag', 'Assists shoppers with product recommendations, order tracking, returns policy, and promotional discounts.', 'Hello shopper! Looking for something special or need help with an existing order?', 'I cannot find that product or order status. Please verify your order number or contact help@store.com.', '["Where is my order?", "What is your return policy?", "Do you offer free shipping?", "How do I apply a discount code?"]', 'You are a warm, efficient retail assistant. Help shoppers find items and provide concise store policies.', '[{"question":"What is your return policy?","answer":"We offer 30-day hassle-free returns on all unworn items in original packaging with tags attached.","tags":"returns,shipping"},{"question":"How long does delivery take?","answer":"Standard shipping takes 3-5 business days. Express shipping delivers in 1-2 business days.","tags":"delivery,shipping"}]', '#f59e0b', 'light', 0, NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ------------------------------------------------------------------------------
-- 4. SYSTEM SETTINGS
-- ------------------------------------------------------------------------------

INSERT INTO `system_settings` (`setting_group`, `key_name`, `value_content`, `is_secret`, `updated_at`) VALUES
('general', 'site_name', 'ChatBot Creator Studio', 0, NOW()),
('general', 'site_tagline', 'Create Powerful Chatbots. Customize Without Coding. Deploy Everywhere.', 0, NOW()),
('general', 'contact_email', 'support@chatbot-creator.com', 0, NOW()),
('general', 'currency', 'USD', 0, NOW()),
('ai', 'default_ai_provider', 'fallback', 0, NOW()),
('ai', 'default_model', 'gpt-4o-mini', 0, NOW()),
('billing', 'paypal_mode', 'sandbox', 0, NOW()),
('security', 'registration_enabled', '1', 0, NOW()),
('security', 'email_verification_required', '0', 0, NOW())
ON DUPLICATE KEY UPDATE `value_content` = VALUES(`value_content`);

SET FOREIGN_KEY_CHECKS = 1;
