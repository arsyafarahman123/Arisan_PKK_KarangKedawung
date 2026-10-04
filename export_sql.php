<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = [
    'users', 
    'password_reset_tokens', 
    'sessions', 
    'cache', 
    'cache_locks', 
    'jobs', 
    'job_batches', 
    'failed_jobs', 
    'arisan_groups', 
    'arisan_rounds', 
    'group_members', 
    'payments', 
    'notification_logs', 
    'activity_logs'
];

$sql = "-- MySQL Database Dump for Arisan PKK KarangKedawung\n";
$sql .= "-- Target Host: sql110.infinityfree.com\n";
$sql .= "-- Target Domain: arisanku.rf.gd\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
$sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
$sql .= "START TRANSACTION;\n";
$sql .= "SET time_zone = '+07:00';\n\n";

$createStatements = [
    'users' => "CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `role` enum('admin','member') NOT NULL DEFAULT 'member',
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'password_reset_tokens' => "CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'sessions' => "CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'cache' => "CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'cache_locks' => "CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'jobs' => "CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'arisan_groups' => "CREATE TABLE IF NOT EXISTS `arisan_groups` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `contribution_amount` decimal(12,2) NOT NULL,
  `period_type` enum('weekly','biweekly','monthly') NOT NULL DEFAULT 'monthly',
  `max_members` int(11) NOT NULL DEFAULT 10,
  `start_date` date NOT NULL,
  `late_fee_per_day` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grace_period_days` int(11) NOT NULL DEFAULT 3,
  `winner_determination` enum('lottery','fixed_order') NOT NULL DEFAULT 'lottery',
  `only_paid_can_win` tinyint(1) NOT NULL DEFAULT 1,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_account_no` varchar(255) DEFAULT NULL,
  `bank_account_name` varchar(255) DEFAULT NULL,
  `status` enum('draft','active','completed') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `arisan_groups_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'arisan_rounds' => "CREATE TABLE IF NOT EXISTS `arisan_rounds` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) UNSIGNED NOT NULL,
  `round_number` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `draw_date` date NOT NULL,
  `host_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `meeting_location` varchar(255) DEFAULT NULL,
  `winner_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `winning_amount` decimal(12,2) DEFAULT NULL,
  `status` enum('pending','ongoing','completed') NOT NULL DEFAULT 'pending',
  `prize_disbursed` tinyint(1) NOT NULL DEFAULT 0,
  `disbursed_at` timestamp NULL DEFAULT NULL,
  `disbursement_notes` text DEFAULT NULL,
  `disbursement_proof_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `arisan_rounds_group_id_round_number_unique` (`group_id`,`round_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'group_members' => "CREATE TABLE IF NOT EXISTS `group_members` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `join_date` date NOT NULL,
  `fixed_order_number` int(11) DEFAULT NULL,
  `has_won` tinyint(1) NOT NULL DEFAULT 0,
  `won_round_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notification_channel` enum('whatsapp','sms','none') NOT NULL DEFAULT 'whatsapp',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_members_group_id_user_id_unique` (`group_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'payments' => "CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) UNSIGNED NOT NULL,
  `round_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `penalty_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','transfer') NOT NULL DEFAULT 'transfer',
  `payment_status` enum('unpaid','pending_verification','paid','late') NOT NULL DEFAULT 'unpaid',
  `proof_image_path` varchar(255) DEFAULT NULL,
  `user_notes` text DEFAULT NULL,
  `due_date` date NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_round_id_user_id_unique` (`round_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'notification_logs' => "CREATE TABLE IF NOT EXISTS `notification_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `round_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `channel` varchar(255) NOT NULL DEFAULT 'whatsapp',
  `status` varchar(255) NOT NULL DEFAULT 'sent',
  `sent_at` timestamp NULL DEFAULT NULL,
  `whatsapp_link` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'activity_logs' => "CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

foreach ($createStatements as $tbl => $stmt) {
    $sql .= "DROP TABLE IF EXISTS `{$tbl}`;\n" . $stmt . "\n\n";
    
    // Fetch data from local SQLite
    if (Schema::hasTable($tbl)) {
        $rows = DB::table($tbl)->get();
        if ($rows->isNotEmpty()) {
            foreach ($rows as $r) {
                $cols = [];
                $vals = [];
                foreach ((array)$r as $k => $v) {
                    $cols[] = "`{$k}`";
                    if (is_null($v)) {
                        $vals[] = 'NULL';
                    } elseif (is_int($v) || (is_numeric($v) && !str_starts_with((string)$v, '0') && !in_array($k, ['phone', 'bank_account_no']))) {
                        $vals[] = $v;
                    } else {
                        $vals[] = "'" . addslashes((string)$v) . "'";
                    }
                }
                $sql .= "INSERT INTO `{$tbl}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
            }
            $sql .= "\n";
        }
    }
}

$sql .= "COMMIT;\n";
$sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

file_put_contents(__DIR__.'/database_infinityfree.sql', $sql);
echo "SQL Dump successfully generated at database_infinityfree.sql!\n";
