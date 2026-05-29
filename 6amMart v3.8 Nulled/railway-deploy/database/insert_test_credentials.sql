-- Test Credentials for Nexo Food v3.8
-- All accounts use password: 12345678

SET FOREIGN_KEY_CHECKS = 0;

-- Admin Role
INSERT INTO `admin_roles` (`id`, `name`, `modules`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Master Admin', NULL, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE id=id;

-- Employee Role
INSERT INTO `employee_roles` (`id`, `name`, `modules`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Store Manager', NULL, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE id=id;

-- Admin Account
INSERT INTO `admins` (`id`, `f_name`, `l_name`, `phone`, `email`, `image`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `zone_id`, `is_logged_in`, `login_remember_token`) VALUES
(1, 'Master', 'Admin', '01759412381', 'admin@admin.com', NULL, '$2y$10$0Nx.nYyGqNEZS60yoD/OJeg7aClDB.yNWQHPPJCOv8C9Y63C38pDy', NULL, NOW(), NOW(), 1, 1, 1, NULL)
ON DUPLICATE KEY UPDATE id=id;

-- User Account
INSERT INTO `users` (`id`, `f_name`, `l_name`, `phone`, `email`, `image`, `is_phone_verified`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `interest`, `cm_firebase_token`, `status`, `order_count`, `login_medium`, `social_id`, `zone_id`, `wallet_balance`, `loyalty_point`, `ref_code`, `current_language_key`, `ref_by`, `temp_token`, `module_ids`, `is_email_verified`, `is_from_pos`) VALUES
(1, 'Test', 'User', '01759412382', 'user@test.com', NULL, 1, NOW(), '$2y$10$0Nx.nYyGqNEZS60yoD/OJeg7aClDB.yNWQHPPJCOv8C9Y63C38pDy', NULL, NOW(), NOW(), NULL, NULL, 1, 0, 'manual', NULL, 1, 0, 0, 'USER0001', 'en', NULL, NULL, NULL, 1, 0)
ON DUPLICATE KEY UPDATE id=id;

-- Vendor Account
INSERT INTO `vendors` (`id`, `f_name`, `l_name`, `phone`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `rejection_note`, `branch`, `holder_name`, `account_no`, `image`, `status`, `firebase_token`, `auth_token`, `login_remember_token`) VALUES
(1, 'Test', 'Vendor', '01759412383', 'vendor@test.com', NOW(), '$2y$10$0Nx.nYyGqNEZS60yoD/OJeg7aClDB.yNWQHPPJCOv8C9Y63C38pDy', NULL, NOW(), NOW(), NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL)
ON DUPLICATE KEY UPDATE id=id;

-- Store (linked to vendor)
INSERT INTO `stores` (`id`, `name`, `phone`, `email`, `logo`, `latitude`, `longitude`, `address`, `footer_text`, `minimum_order`, `comission`, `schedule_order`, `status`, `vendor_id`, `created_at`, `updated_at`, `free_delivery`, `rating`, `cover_photo`, `delivery`, `take_away`, `item_section`, `tax`, `zone_id`, `reviews_section`, `active`, `off_day`, `gst`, `self_delivery_system`, `pos_system`, `minimum_shipping_charge`, `delivery_time`, `veg`, `non_veg`, `order_count`, `total_order`, `module_id`, `order_place_to_schedule_interval`, `featured`, `per_km_shipping_charge`, `prescription_order`, `slug`, `maximum_shipping_charge`, `cutlery`, `meta_title`, `meta_description`, `meta_image`, `meta_data`, `announcement`, `announcement_message`, `store_business_model`, `package_id`, `pickup_zone_id`, `comment`, `tin`, `tin_expire_date`, `tin_certificate_image`) VALUES
(1, 'Test Store', '01759412384', 'store@test.com', NULL, '40.7128', '-74.0060', '123 Test Street, New York', NULL, 10.00, NULL, 0, 1, 1, NOW(), NOW(), 0, NULL, NULL, 1, 1, 1, 0.00, 1, 1, 1, '', NULL, 0, 0, 0.00, '30-40', 1, 1, 0, 0, 1, 0, 0, 0.000, 0, 'test-store', NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 'commission', NULL, NULL, NULL, NULL, NULL, NULL)
ON DUPLICATE KEY UPDATE id=id;

-- Vendor Employee Account
INSERT INTO `vendor_employees` (`id`, `f_name`, `l_name`, `phone`, `email`, `image`, `employee_role_id`, `vendor_id`, `store_id`, `password`, `status`, `remember_token`, `firebase_token`, `auth_token`, `created_at`, `updated_at`, `is_logged_in`, `login_remember_token`) VALUES
(1, 'Test', 'Employee', '01759412385', 'employee@test.com', NULL, 1, 1, 1, '$2y$10$0Nx.nYyGqNEZS60yoD/OJeg7aClDB.yNWQHPPJCOv8C9Y63C38pDy', 1, NULL, NULL, NULL, NOW(), NOW(), 1, NULL)
ON DUPLICATE KEY UPDATE id=id;

-- Delivery Man Account
INSERT INTO `delivery_men` (`id`, `f_name`, `l_name`, `phone`, `email`, `identity_number`, `identity_type`, `identity_image`, `image`, `password`, `auth_token`, `fcm_token`, `zone_id`, `created_at`, `updated_at`, `status`, `active`, `earning`, `current_orders`, `type`, `store_id`, `application_status`, `order_count`, `assigned_order_count`, `vehicle_id`, `loyalty_point`, `ref_code`, `ref_by`, `is_delivery`, `is_ride`) VALUES
(1, 'Test', 'DeliveryMan', '01759412386', 'delivery@test.com', NULL, NULL, '', NULL, '$2y$10$0Nx.nYyGqNEZS60yoD/OJeg7aClDB.yNWQHPPJCOv8C9Y63C38pDy', NULL, NULL, 1, NOW(), NOW(), 1, 1, NULL, 0, 'zone_wise', NULL, 'approved', 0, 0, NULL, 0, 'DM0001', NULL, 1, 0)
ON DUPLICATE KEY UPDATE id=id;

SET FOREIGN_KEY_CHECKS = 1;
