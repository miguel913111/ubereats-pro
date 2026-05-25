-- Test data for Nexo Food v3.8

-- Insert Zone
INSERT INTO `zones` (`id`, `name`, `display_name`, `coordinates`, `status`, `store_wise_topic`, `customer_wise_topic`, `deliveryman_wise_topic`, `cash_on_delivery`, `digital_payment`, `increased_delivery_fee`, `increased_delivery_fee_status`, `increase_delivery_charge_message`, `offline_payment`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 'Test Zone', 'Test Zone', NULL, 1, 'test_store_topic', 'test_customer_topic', 'test_dm_topic', 1, 1, 0, 0, NULL, 0, 1, NOW(), NOW());

-- Insert Module (Food/Grocery)
INSERT INTO `modules` (`id`, `module_name`, `module_type`, `thumbnail`, `status`, `stores_count`, `icon`, `theme_id`, `description`, `all_zone_service`, `created_at`, `updated_at`) VALUES
(1, 'Food & Grocery', 'food', NULL, 1, 1, NULL, 1, 'Food and Grocery module', 0, NOW(), NOW());

-- Insert Admin Role
INSERT INTO `admin_roles` (`id`, `name`, `modules`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Master Admin', NULL, 1, NOW(), NOW());

-- Insert Admin
INSERT INTO `admins` (`id`, `f_name`, `l_name`, `email`, `phone`, `image`, `password`, `remember_token`, `login_remember_token`, `role_id`, `zone_id`, `is_logged_in`, `created_at`, `updated_at`) VALUES
(1, 'Master', 'Admin', 'admin@admin.com', '01759412381', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, 1, 1, NOW(), NOW());

-- Insert User
INSERT INTO `users` (`id`, `f_name`, `l_name`, `email`, `phone`, `password`, `image`, `remember_token`, `status`, `order_count`, `wallet_balance`, `loyalty_point`, `ref_code`, `current_language_key`, `ref_by`, `temp_token`, `module_ids`, `is_email_verified`, `is_from_pos`, `email_verified_at`, `is_phone_verified`, `created_at`, `updated_at`) VALUES
(1, 'Test', 'User', 'user@test.com', '01759412382', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, 0, 0, 0, 'USER0001', 'en', NULL, NULL, NULL, 1, 0, NOW(), 1, NOW(), NOW());

-- Insert Vendor
INSERT INTO `vendors` (`id`, `f_name`, `l_name`, `email`, `phone`, `password`, `image`, `status`, `remember_token`, `auth_token`, `created_at`, `updated_at`) VALUES
(1, 'Test', 'Vendor', 'vendor@test.com', '01759412383', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, NULL, NOW(), NOW());

-- Insert Store
INSERT INTO `stores` (`id`, `name`, `phone`, `email`, `logo`, `latitude`, `longitude`, `address`, `footer_text`, `minimum_order`, `comission`, `commission_active`, `subscription_active`, `fixed_delivery_fee_active`, `fixed_delivery_fee`, `driver_per_km_charge`, `driver_fixed_charge`, `schedule_order`, `status`, `vendor_id`, `free_delivery`, `rating`, `cover_photo`, `delivery`, `take_away`, `item_section`, `tax`, `zone_id`, `reviews_section`, `active`, `off_day`, `gst`, `self_delivery_system`, `pos_system`, `minimum_shipping_charge`, `delivery_time`, `veg`, `non_veg`, `order_count`, `total_order`, `module_id`, `pickup_zone_id`, `order_place_to_schedule_interval`, `featured`, `per_km_shipping_charge`, `prescription_order`, `slug`, `maximum_shipping_charge`, `cutlery`, `meta_title`, `meta_description`, `meta_image`, `announcement`, `announcement_message`, `comment`, `tin`, `tin_expire_date`, `tin_certificate_image`, `created_at`, `updated_at`) VALUES
(1, 'Test Store', '01759412384', 'store@test.com', NULL, '40.7128', '-74.0060', '123 Test St', NULL, 10.000, NULL, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, NULL, NULL, 1, 1, 1, 0, 1, 1, 1, NULL, NULL, 0, 0, 0, NULL, 0, 0, 0, 0, 1, NULL, 0, 0, 0, 0, 'test-store', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NOW(), NOW());

-- Insert Employee Role
INSERT INTO `employee_roles` (`id`, `name`, `modules`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Store Manager', NULL, 1, NOW(), NOW());

-- Insert Vendor Employee
INSERT INTO `vendor_employees` (`id`, `f_name`, `l_name`, `email`, `phone`, `image`, `password`, `remember_token`, `login_remember_token`, `employee_role_id`, `vendor_id`, `store_id`, `status`, `is_logged_in`, `auth_token`, `created_at`, `updated_at`) VALUES
(1, 'Test', 'Employee', 'employee@test.com', '01759412385', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, 1, 1, 1, 1, NULL, NOW(), NOW());

-- Insert Delivery Man
INSERT INTO `delivery_men` (`id`, `f_name`, `l_name`, `email`, `phone`, `password`, `image`, `identity_image`, `identity_type`, `identity_number`, `status`, `active`, `available`, `earning`, `store_id`, `current_orders`, `vehicle_id`, `zone_id`, `ref_by`, `loyalty_point`, `ref_code`, `auth_token`, `application_status`, `is_delivery`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Test', 'DeliveryMan', 'delivery@test.com', '01759412386', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 1, 1, 1, 0, NULL, 0, NULL, 1, NULL, 0, 'DM0001', NULL, 'approved', 1, 'zone_wise', NOW(), NOW());

-- Insert Business Settings (login configs)
INSERT INTO `business_settings` (`key`, `value`, `created_at`, `updated_at`) VALUES
('manual_login_status', '1', NOW(), NOW()),
('otp_login_status', '1', NOW(), NOW()),
('social_login_status', '0', NOW(), NOW()),
('google_login_status', '0', NOW(), NOW()),
('facebook_login_status', '0', NOW(), NOW()),
('apple_login_status', '0', NOW(), NOW()),
('email_verification_status', '0', NOW(), NOW()),
('phone_verification_status', '0', NOW(), NOW());
