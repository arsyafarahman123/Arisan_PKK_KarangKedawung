<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);

echo "<h2>Database Setup - Arisan PKK</h2>";

$host = "sql110.infinityfree.com";
$user = "if0_43086073";
$pass = "XdRNePK250k1";
$db   = "if0_43086073_arisanku";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("<p style='color:red'>Connection failed: " . $conn->connect_error . "</p>");
}
echo "<p style='color:green'>Connected to database OK</p>";

// Inline SQL - no file dependency
$statements = [
    "SET FOREIGN_KEY_CHECKS = 0",
    "DROP TABLE IF EXISTS `activity_logs`",
    "DROP TABLE IF EXISTS `notification_logs`",
    "DROP TABLE IF EXISTS `payments`",
    "DROP TABLE IF EXISTS `group_members`",
    "DROP TABLE IF EXISTS `arisan_rounds`",
    "DROP TABLE IF EXISTS `arisan_groups`",
    "DROP TABLE IF EXISTS `failed_jobs`",
    "DROP TABLE IF EXISTS `job_batches`",
    "DROP TABLE IF EXISTS `jobs`",
    "DROP TABLE IF EXISTS `cache_locks`",
    "DROP TABLE IF EXISTS `cache`",
    "DROP TABLE IF EXISTS `sessions`",
    "DROP TABLE IF EXISTS `password_reset_tokens`",
    "DROP TABLE IF EXISTS `users`",
    "DROP TABLE IF EXISTS `migrations`",

    "CREATE TABLE `users` (
      `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(255) NOT NULL,
      `email` VARCHAR(255) NULL DEFAULT NULL,
      `phone` VARCHAR(255) NOT NULL,
      `role` VARCHAR(255) NOT NULL DEFAULT 'member',
      `address` TEXT NULL DEFAULT NULL,
      `avatar` VARCHAR(255) NULL DEFAULT NULL,
      `is_active` TINYINT(1) NOT NULL DEFAULT 1,
      `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
      `password` VARCHAR(255) NOT NULL,
      `remember_token` VARCHAR(100) NULL DEFAULT NULL,
      `created_at` TIMESTAMP NULL DEFAULT NULL,
      `updated_at` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `users_email_unique` (`email`),
      UNIQUE KEY `users_phone_unique` (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `password_reset_tokens` (
      `email` VARCHAR(255) NOT NULL,
      `token` VARCHAR(255) NOT NULL,
      `created_at` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `sessions` (
      `id` VARCHAR(255) NOT NULL,
      `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
      `ip_address` VARCHAR(45) NULL DEFAULT NULL,
      `user_agent` TEXT NULL DEFAULT NULL,
      `payload` LONGTEXT NOT NULL,
      `last_activity` INT NOT NULL,
      PRIMARY KEY (`id`),
      KEY `sessions_user_id_index` (`user_id`),
      KEY `sessions_last_activity_index` (`last_activity`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `cache` (
      `key` VARCHAR(255) NOT NULL,
      `value` MEDIUMTEXT NOT NULL,
      `expiration` BIGINT NOT NULL,
      PRIMARY KEY (`key`),
      KEY `cache_expiration_index` (`expiration`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `cache_locks` (
      `key` VARCHAR(255) NOT NULL,
      `owner` VARCHAR(255) NOT NULL,
      `expiration` BIGINT NOT NULL,
      PRIMARY KEY (`key`),
      KEY `cache_locks_expiration_index` (`expiration`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `jobs` (
      `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `queue` VARCHAR(255) NOT NULL,
      `payload` LONGTEXT NOT NULL,
      `attempts` SMALLINT UNSIGNED NOT NULL,
      `reserved_at` INT UNSIGNED NULL DEFAULT NULL,
      `available_at` INT UNSIGNED NOT NULL,
      `created_at` INT UNSIGNED NOT NULL,
      PRIMARY KEY (`id`),
      KEY `jobs_queue_index` (`queue`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `job_batches` (
      `id` VARCHAR(255) NOT NULL,
      `name` VARCHAR(255) NOT NULL,
      `total_jobs` INT NOT NULL,
      `pending_jobs` INT NOT NULL,
      `failed_jobs` INT NOT NULL,
      `failed_job_ids` LONGTEXT NOT NULL,
      `options` MEDIUMTEXT NULL DEFAULT NULL,
      `cancelled_at` INT NULL DEFAULT NULL,
      `created_at` INT NOT NULL,
      `finished_at` INT NULL DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `failed_jobs` (
      `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `uuid` VARCHAR(255) NOT NULL,
      `connection` VARCHAR(255) NOT NULL,
      `queue` VARCHAR(255) NOT NULL,
      `payload` LONGTEXT NOT NULL,
      `exception` LONGTEXT NOT NULL,
      `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `arisan_groups` (
      `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(255) NOT NULL,
      `slug` VARCHAR(255) NOT NULL,
      `description` TEXT NULL DEFAULT NULL,
      `admin_id` BIGINT UNSIGNED NOT NULL,
      `contribution_amount` DECIMAL(15,2) NOT NULL,
      `period_type` VARCHAR(255) NOT NULL DEFAULT 'monthly',
      `max_members` INT NOT NULL,
      `start_date` DATE NOT NULL,
      `late_fee_per_day` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
      `grace_period_days` INT NOT NULL DEFAULT 3,
      `winner_determination` VARCHAR(255) NOT NULL DEFAULT 'lottery',
      `only_paid_can_win` TINYINT(1) NOT NULL DEFAULT 1,
      `bank_name` VARCHAR(255) NULL DEFAULT NULL,
      `bank_account_no` VARCHAR(255) NULL DEFAULT NULL,
      `bank_account_name` VARCHAR(255) NULL DEFAULT NULL,
      `qris_image` VARCHAR(255) NULL DEFAULT NULL,
      `status` VARCHAR(255) NOT NULL DEFAULT 'draft',
      `created_at` TIMESTAMP NULL DEFAULT NULL,
      `updated_at` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `arisan_groups_slug_unique` (`slug`),
      KEY `arisan_groups_admin_id_foreign` (`admin_id`),
      CONSTRAINT `arisan_groups_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `arisan_rounds` (
      `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `group_id` BIGINT UNSIGNED NOT NULL,
      `round_number` INT NOT NULL,
      `due_date` DATE NOT NULL,
      `draw_date` DATE NOT NULL,
      `host_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
      `host_location` VARCHAR(255) NULL DEFAULT NULL,
      `winner_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
      `winning_amount` DECIMAL(15,2) NULL DEFAULT NULL,
      `prize_disbursed` TINYINT(1) NOT NULL DEFAULT 0,
      `disbursed_at` DATETIME NULL DEFAULT NULL,
      `disbursement_proof` VARCHAR(255) NULL DEFAULT NULL,
      `disbursement_notes` TEXT NULL DEFAULT NULL,
      `status` VARCHAR(255) NOT NULL DEFAULT 'pending',
      `notes` TEXT NULL DEFAULT NULL,
      `created_at` TIMESTAMP NULL DEFAULT NULL,
      `updated_at` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `arisan_rounds_group_id_foreign` (`group_id`),
      KEY `arisan_rounds_host_user_id_foreign` (`host_user_id`),
      KEY `arisan_rounds_winner_user_id_foreign` (`winner_user_id`),
      CONSTRAINT `arisan_rounds_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `arisan_groups` (`id`) ON DELETE CASCADE,
      CONSTRAINT `arisan_rounds_host_user_id_foreign` FOREIGN KEY (`host_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
      CONSTRAINT `arisan_rounds_winner_user_id_foreign` FOREIGN KEY (`winner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `group_members` (
      `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `group_id` BIGINT UNSIGNED NOT NULL,
      `user_id` BIGINT UNSIGNED NOT NULL,
      `join_date` DATE NULL DEFAULT NULL,
      `fixed_order_number` INT NULL DEFAULT NULL,
      `has_won` TINYINT(1) NOT NULL DEFAULT 0,
      `won_round_id` BIGINT UNSIGNED NULL DEFAULT NULL,
      `notification_channel` VARCHAR(255) NOT NULL DEFAULT 'whatsapp',
      `is_active` TINYINT(1) NOT NULL DEFAULT 1,
      `notes` TEXT NULL DEFAULT NULL,
      `created_at` TIMESTAMP NULL DEFAULT NULL,
      `updated_at` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `group_members_group_id_user_id_unique` (`group_id`, `user_id`),
      KEY `group_members_user_id_foreign` (`user_id`),
      KEY `group_members_won_round_id_foreign` (`won_round_id`),
      CONSTRAINT `group_members_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `arisan_groups` (`id`) ON DELETE CASCADE,
      CONSTRAINT `group_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `group_members_won_round_id_foreign` FOREIGN KEY (`won_round_id`) REFERENCES `arisan_rounds` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `payments` (
      `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `round_id` BIGINT UNSIGNED NOT NULL,
      `group_id` BIGINT UNSIGNED NOT NULL,
      `user_id` BIGINT UNSIGNED NOT NULL,
      `amount` DECIMAL(15,2) NOT NULL,
      `penalty_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
      `total_amount` DECIMAL(15,2) NOT NULL,
      `due_date` DATE NOT NULL,
      `payment_status` VARCHAR(255) NOT NULL DEFAULT 'unpaid',
      `payment_method` VARCHAR(255) NOT NULL DEFAULT 'transfer',
      `proof_image` VARCHAR(255) NULL DEFAULT NULL,
      `paid_at` DATETIME NULL DEFAULT NULL,
      `verified_at` DATETIME NULL DEFAULT NULL,
      `verified_by` BIGINT UNSIGNED NULL DEFAULT NULL,
      `rejection_reason` TEXT NULL DEFAULT NULL,
      `user_notes` TEXT NULL DEFAULT NULL,
      `admin_notes` TEXT NULL DEFAULT NULL,
      `created_at` TIMESTAMP NULL DEFAULT NULL,
      `updated_at` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `payments_round_id_user_id_unique` (`round_id`, `user_id`),
      KEY `payments_group_id_foreign` (`group_id`),
      KEY `payments_user_id_foreign` (`user_id`),
      KEY `payments_verified_by_foreign` (`verified_by`),
      CONSTRAINT `payments_round_id_foreign` FOREIGN KEY (`round_id`) REFERENCES `arisan_rounds` (`id`) ON DELETE CASCADE,
      CONSTRAINT `payments_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `arisan_groups` (`id`) ON DELETE CASCADE,
      CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `payments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `notification_logs` (
      `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `user_id` BIGINT UNSIGNED NOT NULL,
      `group_id` BIGINT UNSIGNED NULL DEFAULT NULL,
      `round_id` BIGINT UNSIGNED NULL DEFAULT NULL,
      `type` VARCHAR(255) NOT NULL,
      `title` VARCHAR(255) NOT NULL,
      `message` TEXT NOT NULL,
      `channel` VARCHAR(255) NOT NULL DEFAULT 'whatsapp',
      `status` VARCHAR(255) NOT NULL DEFAULT 'sent',
      `sent_at` DATETIME NULL DEFAULT NULL,
      `read_at` DATETIME NULL DEFAULT NULL,
      `whatsapp_link` TEXT NULL DEFAULT NULL,
      `created_at` TIMESTAMP NULL DEFAULT NULL,
      `updated_at` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `notification_logs_user_id_foreign` (`user_id`),
      KEY `notification_logs_group_id_foreign` (`group_id`),
      KEY `notification_logs_round_id_foreign` (`round_id`),
      CONSTRAINT `notification_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `notification_logs_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `arisan_groups` (`id`) ON DELETE CASCADE,
      CONSTRAINT `notification_logs_round_id_foreign` FOREIGN KEY (`round_id`) REFERENCES `arisan_rounds` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `activity_logs` (
      `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
      `action` VARCHAR(255) NOT NULL,
      `description` TEXT NOT NULL,
      `ip_address` VARCHAR(255) NULL DEFAULT NULL,
      `user_agent` TEXT NULL DEFAULT NULL,
      `created_at` TIMESTAMP NULL DEFAULT NULL,
      `updated_at` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `activity_logs_user_id_foreign` (`user_id`),
      CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE `migrations` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `migration` VARCHAR(255) NOT NULL,
      `batch` INT NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "INSERT INTO `migrations` (`migration`, `batch`) VALUES
    ('0001_01_01_000000_create_users_table', 1),
    ('0001_01_01_000001_create_cache_table', 1),
    ('0001_01_01_000002_create_jobs_table', 1),
    ('2026_10_02_000001_create_arisan_groups_table', 1),
    ('2026_10_02_000002_create_arisan_rounds_table', 1),
    ('2026_10_02_000003_create_group_members_table', 1),
    ('2026_10_02_000004_create_payments_table', 1),
    ('2026_10_02_000005_create_notification_logs_table', 1),
    ('2026_10_02_000006_create_activity_logs_table', 1)",

    "INSERT INTO `users` (`id`, `name`, `email`, `phone`, `role`, `address`, `avatar`, `is_active`, `password`, `created_at`, `updated_at`) VALUES
    (1, 'Admin PKK', 'admin@arisanku.rf.gd', '08123456789', 'admin', 'Karang Kedawung', NULL, 1, '\$2y\$12\$LQv3c1yqBo9SkvXS1GbkCOZ/4hS5bYNWek.oPx1tVaFuUzVwMKnOe', NOW(), NOW())",

    "SET FOREIGN_KEY_CHECKS = 1"
];

$success = 0;
$errors = 0;
foreach ($statements as $i => $stmt) {
    if ($conn->query($stmt)) {
        $success++;
    } else {
        $errors++;
        echo "<p style='color:red'>Error #" . ($i+1) . ": " . $conn->error . "</p>";
        echo "<pre style='font-size:11px;color:#666'>" . htmlspecialchars(substr($stmt, 0, 150)) . "...</pre>";
    }
}

echo "<hr>";
echo "<p><b>Results: $success OK, $errors errors</b></p>";

// Verify tables
$result = $conn->query("SHOW TABLES");
if ($result && $result->num_rows > 0) {
    echo "<h3 style='color:green'>Tables created:</h3><ul>";
    while ($row = $result->fetch_row()) {
        $countResult = $conn->query("SELECT COUNT(*) FROM `{$row[0]}`");
        $count = $countResult ? $countResult->fetch_row()[0] : '?';
        echo "<li><b>{$row[0]}</b> ({$count} rows)</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>No tables found!</p>";
}

$conn->close();
echo "<p style='color:green'><b>Database setup complete!</b></p>";
echo "<p><a href='/'>Go to website</a></p>";
