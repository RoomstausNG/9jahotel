-- Current sql file was generated after introspecting the database
-- If you want to run this migration please uncomment this code before executing migrations
/*
CREATE TABLE `amenities` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp,
	`icon` text,
	`general_amenity_id` bigint unsigned,
	CONSTRAINT `amenities_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `amenity_hotel` (
	`amenity_id` bigint unsigned,
	`hotel_id` bigint unsigned
);
--> statement-breakpoint
CREATE TABLE `booking_invoices` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`reference` varchar(255) NOT NULL,
	`amount` int NOT NULL,
	`commission_amount` int,
	`hotel_payable` int,
	`status` varchar(255) DEFAULT 'unpaid',
	`customer_id` bigint unsigned,
	`hotel_id` bigint unsigned,
	`payed_on` date,
	`created_at` timestamp,
	`updated_at` timestamp,
	`deleted_at` timestamp,
	`is_settled` tinyint(1) DEFAULT 0,
	`date_settled` date,
	`checked_in` date,
	`checked_out` date,
	`type` varchar(255) DEFAULT 'online',
	CONSTRAINT `booking_invoices_id` PRIMARY KEY(`id`),
	CONSTRAINT `booking_invoices_reference_unique` UNIQUE(`reference`)
);
--> statement-breakpoint
CREATE TABLE `bookings` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`code` varchar(255) NOT NULL,
	`bookable_id` int NOT NULL,
	`bookable_type` varchar(255) NOT NULL,
	`due` bigint,
	`discount` bigint,
	`amount_paid` bigint DEFAULT 0,
	`checked_in` date NOT NULL,
	`checked_out` date NOT NULL,
	`status` varchar(255) DEFAULT 'booked',
	`type` varchar(255) DEFAULT 'room',
	`agent_id` bigint unsigned,
	`customer_id` bigint unsigned NOT NULL,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	`mode_of_payment` varchar(255),
	`via` varchar(255) DEFAULT '9jahotel',
	`booking_invoice_id` bigint unsigned,
	CONSTRAINT `bookings_id` PRIMARY KEY(`id`),
	CONSTRAINT `bookings_code_unique` UNIQUE(`code`)
);
--> statement-breakpoint
CREATE TABLE `businesses` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`phone` varchar(255) NOT NULL,
	`email` varchar(255) NOT NULL,
	`address` varchar(255),
	`status` varchar(255),
	`city_id` bigint unsigned,
	`balance` bigint DEFAULT 0,
	`limit` bigint DEFAULT 500000,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `businesses_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `categories` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`image` varchar(255),
	`type` varchar(255) DEFAULT 'supply',
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	`deleted_at` timestamp,
	CONSTRAINT `categories_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `cities` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`country_id` bigint unsigned NOT NULL,
	`state_id` bigint unsigned NOT NULL,
	`name` varchar(255) NOT NULL,
	`country_code` varchar(3) NOT NULL,
	CONSTRAINT `cities_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `countries` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`iso2` varchar(2) NOT NULL,
	`name` varchar(255) NOT NULL,
	`status` tinyint NOT NULL DEFAULT 1,
	`phone_code` varchar(5) NOT NULL,
	`iso3` varchar(3) NOT NULL,
	`region` varchar(255) NOT NULL,
	`subregion` varchar(255) NOT NULL,
	CONSTRAINT `countries_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `credit_payments` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`amount_paid` int NOT NULL,
	`amount_owed` int,
	`business_id` bigint unsigned NOT NULL,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `credit_payments_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `currencies` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`country_id` bigint unsigned NOT NULL,
	`name` varchar(255) NOT NULL,
	`code` varchar(255) NOT NULL,
	`precision` tinyint NOT NULL DEFAULT 2,
	`symbol` varchar(255) NOT NULL,
	`symbol_native` varchar(255) NOT NULL,
	`symbol_first` tinyint NOT NULL DEFAULT 1,
	`decimal_mark` varchar(1) NOT NULL DEFAULT '.',
	`thousands_separator` varchar(1) NOT NULL DEFAULT ',',
	CONSTRAINT `currencies_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `customer_hotel` (
	`customer_id` bigint unsigned NOT NULL,
	`hotel_id` bigint unsigned NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp
);
--> statement-breakpoint
CREATE TABLE `customers` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`firstname` varchar(255) NOT NULL,
	`lastname` varchar(255) NOT NULL,
	`phone` varchar(255) NOT NULL,
	`otp` varchar(255),
	`email` varchar(255),
	`nok_name` varchar(255),
	`nok_phone` varchar(255),
	`nok_email` varchar(255),
	`date_of_birth` date,
	`avatar` varchar(255),
	`gender` varchar(255),
	`address` varchar(255),
	`city_id` bigint unsigned,
	`state_id` bigint unsigned,
	`country_id` bigint unsigned,
	`nationality` varchar(255),
	`business_id` bigint unsigned,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	`password` varchar(255) DEFAULT '$2y$12$f1kewYVMIb1n4YlWEKmKu.z6MO/89qJ/UtzpzHoJOvWMAUGqVzi6q',
	`device_name` varchar(255),
	`remember_token` varchar(100),
	`id_type` varchar(255),
	`id_number` varchar(255),
	CONSTRAINT `customers_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `departments` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	`user_id` bigint unsigned,
	CONSTRAINT `departments_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `expenditures` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`source` varchar(255) NOT NULL,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `expenditures_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `failed_jobs` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`uuid` varchar(255) NOT NULL,
	`connection` text NOT NULL,
	`queue` text NOT NULL,
	`payload` longtext NOT NULL,
	`exception` longtext NOT NULL,
	`failed_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
	CONSTRAINT `failed_jobs_id` PRIMARY KEY(`id`),
	CONSTRAINT `failed_jobs_uuid_unique` UNIQUE(`uuid`)
);
--> statement-breakpoint
CREATE TABLE `faqs` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`answer` longtext,
	`hotel_id` bigint unsigned NOT NULL,
	`question_id` bigint unsigned NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `faqs_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `favourites` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`customer_id` bigint unsigned NOT NULL,
	`hotel_id` bigint unsigned NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `favourites_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `finances` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`code` varchar(255),
	`amount` bigint NOT NULL,
	`description` text,
	`financeable_id` int NOT NULL,
	`financeable_type` varchar(255) NOT NULL,
	`type` varchar(255),
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `finances_id` PRIMARY KEY(`id`),
	CONSTRAINT `finances_code_unique` UNIQUE(`code`)
);
--> statement-breakpoint
CREATE TABLE `floors` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `floors_id` PRIMARY KEY(`id`),
	CONSTRAINT `floors_name_unique` UNIQUE(`name`)
);
--> statement-breakpoint
CREATE TABLE `general_items` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`code` varchar(255),
	`image` varchar(255),
	`price` bigint,
	`description` text,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `general_items_id` PRIMARY KEY(`id`),
	CONSTRAINT `general_items_code_unique` UNIQUE(`code`)
);
--> statement-breakpoint
CREATE TABLE `halls` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`price` int,
	`capacity` int NOT NULL,
	`status` varchar(255) DEFAULT 'available',
	`description` text,
	`image` varchar(255),
	`is_available` tinyint(1) NOT NULL DEFAULT 1,
	`type_id` bigint unsigned,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	`deposit` bigint,
	`promo_price` bigint,
	`promo_start_day` date,
	`promo_end_day` date,
	`reference` date,
	`number` varchar(255),
	`remarks` varchar(255) DEFAULT 'sellable',
	CONSTRAINT `halls_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `hotel_galleries` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`hotel_id` bigint unsigned NOT NULL,
	`from` date NOT NULL,
	`to` date NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `hotel_galleries_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `hotel_user` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`hotel_id` bigint unsigned NOT NULL,
	`user_id` bigint unsigned NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `hotel_user_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `hotels` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`code` varchar(255) NOT NULL,
	`background_image` varchar(255),
	`address` varchar(255) NOT NULL,
	`primary_currency` varchar(255),
	`secondary_currency` varchar(255),
	`city_id` bigint unsigned,
	`zip_code` varchar(255),
	`service_charge` int,
	`vat` tinyint(1) DEFAULT 0,
	`surcharge` int,
	`logo` varchar(255),
	`primary_color` varchar(255),
	`secondary_color` varchar(255),
	`checked_out` time,
	`telephone` varchar(255) NOT NULL,
	`email` varchar(255) NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp,
	`status` varchar(255) DEFAULT 'active',
	`renew` date DEFAULT '2024-06-16',
	`percentage_charge` int DEFAULT 10,
	`online_price` int DEFAULT 10,
	`non_refundable_price` int DEFAULT 15,
	`deleted_at` timestamp,
	`type` varchar(255) DEFAULT 'both',
	`team_id` bigint unsigned,
	`star_rating` int,
	`state_id` bigint unsigned,
	`country_id` bigint unsigned,
	`description` longtext,
	CONSTRAINT `hotels_id` PRIMARY KEY(`id`),
	CONSTRAINT `hotels_code_unique` UNIQUE(`code`),
	CONSTRAINT `hotels_email_unique` UNIQUE(`email`)
);
--> statement-breakpoint
CREATE TABLE `images` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`imageable_id` int NOT NULL,
	`imageable_type` varchar(255) NOT NULL,
	`url` varchar(255) NOT NULL,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `images_id` PRIMARY KEY(`id`),
	CONSTRAINT `images_url_unique` UNIQUE(`url`)
);
--> statement-breakpoint
CREATE TABLE `invoices` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`reference` varchar(255) NOT NULL,
	`description` varchar(255) NOT NULL,
	`subscription` varchar(255) NOT NULL DEFAULT 'yearly',
	`amount` bigint NOT NULL,
	`status` varchar(255) NOT NULL DEFAULT 'unpaid',
	`date_paid` date,
	`from` date,
	`to` date,
	`hotel_id` bigint unsigned,
	`deleted_at` timestamp,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `invoices_id` PRIMARY KEY(`id`),
	CONSTRAINT `invoices_reference_unique` UNIQUE(`reference`)
);
--> statement-breakpoint
CREATE TABLE `items` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`cost_price` bigint,
	`price` bigint NOT NULL,
	`quantity` bigint NOT NULL,
	`discount` bigint,
	`limit` int,
	`image` varchar(255),
	`revenue_id` bigint unsigned,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	`general_item_id` bigint unsigned,
	CONSTRAINT `items_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `jobs` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`queue` varchar(255) NOT NULL,
	`payload` longtext NOT NULL,
	`attempts` tinyint unsigned NOT NULL,
	`reserved_at` int unsigned,
	`available_at` int unsigned NOT NULL,
	`created_at` int unsigned NOT NULL,
	CONSTRAINT `jobs_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `languages` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`code` char(2) NOT NULL,
	`name` varchar(255) NOT NULL,
	`name_native` varchar(255) NOT NULL,
	`dir` char(3) NOT NULL,
	CONSTRAINT `languages_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `media` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`model_type` varchar(255) NOT NULL,
	`model_id` bigint unsigned NOT NULL,
	`uuid` char(36),
	`collection_name` varchar(255) NOT NULL,
	`name` varchar(255) NOT NULL,
	`file_name` varchar(255) NOT NULL,
	`mime_type` varchar(255),
	`disk` varchar(255) NOT NULL,
	`conversions_disk` varchar(255),
	`size` bigint unsigned NOT NULL,
	`manipulations` json NOT NULL,
	`custom_properties` json NOT NULL,
	`generated_conversions` json NOT NULL,
	`responsive_images` json NOT NULL,
	`order_column` int unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `media_id` PRIMARY KEY(`id`),
	CONSTRAINT `media_uuid_unique` UNIQUE(`uuid`)
);
--> statement-breakpoint
CREATE TABLE `migrations` (
	`id` int unsigned AUTO_INCREMENT NOT NULL,
	`migration` varchar(255) NOT NULL,
	`batch` int NOT NULL,
	CONSTRAINT `migrations_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `model_has_permissions` (
	`permission_id` bigint unsigned NOT NULL,
	`model_type` varchar(255) NOT NULL,
	`model_id` bigint unsigned NOT NULL,
	CONSTRAINT `model_has_permissions_permission_id_model_id_model_type` PRIMARY KEY(`permission_id`,`model_id`,`model_type`)
);
--> statement-breakpoint
CREATE TABLE `model_has_roles` (
	`role_id` bigint unsigned NOT NULL,
	`model_type` varchar(255) NOT NULL,
	`model_id` bigint unsigned NOT NULL,
	CONSTRAINT `model_has_roles_role_id_model_id_model_type` PRIMARY KEY(`role_id`,`model_id`,`model_type`)
);
--> statement-breakpoint
CREATE TABLE `notifications` (
	`id` char(36) NOT NULL,
	`type` varchar(255) NOT NULL,
	`notifiable_type` varchar(255) NOT NULL,
	`notifiable_id` bigint unsigned NOT NULL,
	`data` text NOT NULL,
	`read_at` timestamp,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `notifications_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `orders` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`code` varchar(255),
	`mode_of_payment` varchar(255) NOT NULL DEFAULT 'cash',
	`type` varchar(255) NOT NULL DEFAULT 'customer',
	`total_price` int,
	`discount` int,
	`quantity` int NOT NULL,
	`customer_id` bigint unsigned,
	`item_id` bigint unsigned,
	`agent_id` bigint unsigned,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	`booking_id` bigint unsigned,
	`sale_id` bigint unsigned,
	CONSTRAINT `orders_id` PRIMARY KEY(`id`),
	CONSTRAINT `orders_code_unique` UNIQUE(`code`)
);
--> statement-breakpoint
CREATE TABLE `password_reset_tokens` (
	`email` varchar(255) NOT NULL,
	`token` varchar(255) NOT NULL,
	`created_at` timestamp,
	CONSTRAINT `password_reset_tokens_email` PRIMARY KEY(`email`)
);
--> statement-breakpoint
CREATE TABLE `permissions` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`guard_name` varchar(255) NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `permissions_id` PRIMARY KEY(`id`),
	CONSTRAINT `permissions_name_guard_name_unique` UNIQUE(`name`,`guard_name`)
);
--> statement-breakpoint
CREATE TABLE `personal_access_tokens` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`tokenable_type` varchar(255) NOT NULL,
	`tokenable_id` bigint unsigned NOT NULL,
	`name` varchar(255) NOT NULL,
	`token` varchar(64) NOT NULL,
	`abilities` text,
	`last_used_at` timestamp,
	`expires_at` timestamp,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `personal_access_tokens_id` PRIMARY KEY(`id`),
	CONSTRAINT `personal_access_tokens_token_unique` UNIQUE(`token`)
);
--> statement-breakpoint
CREATE TABLE `profiles` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`account_number` varchar(255),
	`account_name` varchar(255),
	`bank_name` varchar(255),
	`special_bonus` varchar(255),
	`paye` int,
	`pension_fund_contribution` int,
	`other_deductions` int,
	`surcharges` int,
	`tip` int,
	`health_insurance` int,
	`transport_allowance` int,
	`housing_allowance` int,
	`special_duty_allowance` int,
	`overtime_allowance` int,
	`user_id` bigint unsigned NOT NULL,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `profiles_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `questions` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`text` varchar(255) NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `questions_id` PRIMARY KEY(`id`),
	CONSTRAINT `questions_text_unique` UNIQUE(`text`)
);
--> statement-breakpoint
CREATE TABLE `ratings` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`customer_id` bigint unsigned NOT NULL,
	`hotel_id` bigint unsigned NOT NULL,
	`title` varchar(255) NOT NULL,
	`body` text,
	`number` int NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `ratings_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `revenues` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`source` varchar(255) NOT NULL,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `revenues_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `role_has_permissions` (
	`permission_id` bigint unsigned NOT NULL,
	`role_id` bigint unsigned NOT NULL,
	CONSTRAINT `role_has_permissions_permission_id_role_id` PRIMARY KEY(`permission_id`,`role_id`)
);
--> statement-breakpoint
CREATE TABLE `roles` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`guard_name` varchar(255) NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `roles_id` PRIMARY KEY(`id`),
	CONSTRAINT `roles_name_guard_name_unique` UNIQUE(`name`,`guard_name`)
);
--> statement-breakpoint
CREATE TABLE `rooms` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`number` varchar(255) NOT NULL,
	`type_id` bigint unsigned,
	`floor_id` bigint unsigned,
	`hotel_id` bigint unsigned,
	`price` int,
	`capacity` int NOT NULL,
	`bed_type` varchar(255) NOT NULL,
	`status` varchar(255) DEFAULT 'available',
	`character` varchar(255) DEFAULT 'no smoking',
	`description` text,
	`image` varchar(255),
	`is_available` tinyint(1) NOT NULL DEFAULT 1,
	`created_at` timestamp,
	`updated_at` timestamp,
	`comment` varchar(255),
	CONSTRAINT `rooms_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `sales` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`code` varchar(255),
	`mode_of_payment` varchar(255) NOT NULL DEFAULT 'cash',
	`type` varchar(255) NOT NULL DEFAULT 'customer',
	`total` int,
	`discount` int DEFAULT 0,
	`customer_id` bigint unsigned,
	`hotel_id` bigint unsigned,
	`agent_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	`booking_id` bigint unsigned,
	CONSTRAINT `sales_id` PRIMARY KEY(`id`),
	CONSTRAINT `sales_code_unique` UNIQUE(`code`)
);
--> statement-breakpoint
CREATE TABLE `states` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`country_id` bigint unsigned NOT NULL,
	`name` varchar(255) NOT NULL,
	`country_code` varchar(3),
	CONSTRAINT `states_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `statuses` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`status` varchar(255),
	`booking_id` bigint unsigned,
	`room_id` bigint unsigned,
	`hall_id` bigint unsigned,
	`date` date NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `statuses_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `suppliers` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`email` varchar(255),
	`phone` varchar(255),
	`website` varchar(255),
	`image` varchar(255),
	`description` longtext,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	`category_id` bigint unsigned,
	`company_name` varchar(255),
	`status` varchar(255) DEFAULT 'active',
	`is_trusted_and_tested` tinyint(1) NOT NULL DEFAULT 0,
	CONSTRAINT `suppliers_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `team_user` (
	`team_id` bigint unsigned NOT NULL,
	`marketer_id` bigint unsigned NOT NULL,
	`created_at` timestamp,
	`updated_at` timestamp
);
--> statement-breakpoint
CREATE TABLE `teams` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`code` varchar(255),
	`description` text,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `teams_id` PRIMARY KEY(`id`),
	CONSTRAINT `teams_code_unique` UNIQUE(`code`)
);
--> statement-breakpoint
CREATE TABLE `timezones` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`country_id` bigint unsigned NOT NULL,
	`name` varchar(255) NOT NULL,
	CONSTRAINT `timezones_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `types` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`price1` bigint,
	`price2` bigint,
	`deposit1` bigint,
	`deposit2` bigint,
	`weekend_price` bigint,
	`weekend_start_day` varchar(255),
	`weekend_end_day` varchar(255),
	`promo_price` bigint,
	`promo_start_day` date,
	`promo_end_day` date,
	`remarks` text,
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `types_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `users` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`firstname` varchar(255) NOT NULL,
	`department_id` bigint unsigned,
	`lastname` varchar(255) NOT NULL,
	`username` varchar(255) NOT NULL,
	`phone` varchar(255) NOT NULL,
	`email` varchar(255),
	`nok_name` varchar(255),
	`nok_avatar` varchar(255),
	`nok_phone` varchar(255),
	`nok_email` varchar(255),
	`date_of_birth` date,
	`can_login` tinyint(1) NOT NULL DEFAULT 1,
	`date_of_employment` date NOT NULL,
	`avatar` varchar(255),
	`current_role` varchar(255),
	`designation` varchar(255) NOT NULL,
	`gender` varchar(255),
	`address` text,
	`account_number` varchar(255),
	`bank_name` varchar(255),
	`account_name` varchar(255),
	`signature` varchar(255),
	`gross_annual_salary` bigint,
	`payee` bigint,
	`surcharges` bigint,
	`special_bonus` bigint,
	`health_insurance` bigint,
	`transport_insurance` bigint,
	`housing_insurance` bigint,
	`overtime_insurance` bigint,
	`special_duty_insurance` bigint,
	`other_deductions` bigint,
	`city_id` bigint,
	`state_id` bigint,
	`country_id` bigint,
	`password` varchar(255) NOT NULL,
	`two_factor_secret` text,
	`two_factor_recovery_codes` text,
	`two_factor_confirmed_at` timestamp,
	`is_new` tinyint(1) NOT NULL DEFAULT 1,
	`email_verified_at` timestamp,
	`hotel_id` bigint unsigned,
	`remember_token` varchar(100),
	`created_at` timestamp,
	`updated_at` timestamp,
	`otp` varchar(255),
	`device_name` varchar(255) DEFAULT 'web',
	CONSTRAINT `users_id` PRIMARY KEY(`id`),
	CONSTRAINT `users_email_unique` UNIQUE(`email`),
	CONSTRAINT `users_phone_unique` UNIQUE(`phone`),
	CONSTRAINT `users_username_unique` UNIQUE(`username`)
);
--> statement-breakpoint
CREATE TABLE `videos` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`url` varchar(255) NOT NULL,
	`type` varchar(255),
	`hotel_id` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `videos_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
ALTER TABLE `amenities` ADD CONSTRAINT `amenities_general_amenity_id_foreign` FOREIGN KEY (`general_amenity_id`) REFERENCES `amenities`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `amenity_hotel` ADD CONSTRAINT `amenity_hotel_amenity_id_foreign` FOREIGN KEY (`amenity_id`) REFERENCES `amenities`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `amenity_hotel` ADD CONSTRAINT `amenity_hotel_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `booking_invoices` ADD CONSTRAINT `booking_invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `booking_invoices` ADD CONSTRAINT `booking_invoices_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `bookings` ADD CONSTRAINT `bookings_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `bookings` ADD CONSTRAINT `bookings_booking_invoice_id_foreign` FOREIGN KEY (`booking_invoice_id`) REFERENCES `booking_invoices`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `bookings` ADD CONSTRAINT `bookings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `bookings` ADD CONSTRAINT `bookings_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `businesses` ADD CONSTRAINT `businesses_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `businesses` ADD CONSTRAINT `businesses_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `categories` ADD CONSTRAINT `categories_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `credit_payments` ADD CONSTRAINT `credit_payments_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `credit_payments` ADD CONSTRAINT `credit_payments_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `customer_hotel` ADD CONSTRAINT `customer_hotel_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `customer_hotel` ADD CONSTRAINT `customer_hotel_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `customers` ADD CONSTRAINT `customers_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `customers` ADD CONSTRAINT `customers_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `customers` ADD CONSTRAINT `customers_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `customers` ADD CONSTRAINT `customers_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `customers` ADD CONSTRAINT `customers_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `departments` ADD CONSTRAINT `departments_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `departments` ADD CONSTRAINT `departments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `expenditures` ADD CONSTRAINT `expenditures_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `faqs` ADD CONSTRAINT `faqs_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `faqs` ADD CONSTRAINT `faqs_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `favourites` ADD CONSTRAINT `favourites_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `favourites` ADD CONSTRAINT `favourites_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `finances` ADD CONSTRAINT `finances_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `floors` ADD CONSTRAINT `floors_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `halls` ADD CONSTRAINT `halls_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `halls` ADD CONSTRAINT `halls_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `hotel_galleries` ADD CONSTRAINT `hotel_galleries_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `hotel_user` ADD CONSTRAINT `hotel_user_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `hotel_user` ADD CONSTRAINT `hotel_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `hotels` ADD CONSTRAINT `hotels_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `hotels` ADD CONSTRAINT `hotels_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `states`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `hotels` ADD CONSTRAINT `hotels_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `hotels` ADD CONSTRAINT `hotels_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `images` ADD CONSTRAINT `images_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `invoices` ADD CONSTRAINT `invoices_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `items` ADD CONSTRAINT `items_general_item_id_foreign` FOREIGN KEY (`general_item_id`) REFERENCES `general_items`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `items` ADD CONSTRAINT `items_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `items` ADD CONSTRAINT `items_revenue_id_foreign` FOREIGN KEY (`revenue_id`) REFERENCES `revenues`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `model_has_permissions` ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `model_has_roles` ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `orders` ADD CONSTRAINT `orders_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `orders` ADD CONSTRAINT `orders_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `orders` ADD CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `orders` ADD CONSTRAINT `orders_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `orders` ADD CONSTRAINT `orders_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `profiles` ADD CONSTRAINT `profiles_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `profiles` ADD CONSTRAINT `profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `ratings` ADD CONSTRAINT `ratings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `ratings` ADD CONSTRAINT `ratings_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `revenues` ADD CONSTRAINT `revenues_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `role_has_permissions` ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `role_has_permissions` ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `rooms` ADD CONSTRAINT `rooms_floor_id_foreign` FOREIGN KEY (`floor_id`) REFERENCES `floors`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `rooms` ADD CONSTRAINT `rooms_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `rooms` ADD CONSTRAINT `rooms_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `sales` ADD CONSTRAINT `sales_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `sales` ADD CONSTRAINT `sales_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `sales` ADD CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `sales` ADD CONSTRAINT `sales_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `statuses` ADD CONSTRAINT `statuses_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `statuses` ADD CONSTRAINT `statuses_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `statuses` ADD CONSTRAINT `statuses_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `suppliers` ADD CONSTRAINT `suppliers_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `suppliers` ADD CONSTRAINT `suppliers_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `team_user` ADD CONSTRAINT `team_user_marketer_id_foreign` FOREIGN KEY (`marketer_id`) REFERENCES `users`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `team_user` ADD CONSTRAINT `team_user_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `types` ADD CONSTRAINT `types_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `users` ADD CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `users` ADD CONSTRAINT `users_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `videos` ADD CONSTRAINT `videos_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
CREATE INDEX `jobs_queue_index` ON `jobs` (`queue`);--> statement-breakpoint
CREATE INDEX `media_model_type_model_id_index` ON `media` (`model_type`,`model_id`);--> statement-breakpoint
CREATE INDEX `media_order_column_index` ON `media` (`order_column`);--> statement-breakpoint
CREATE INDEX `model_has_permissions_model_id_model_type_index` ON `model_has_permissions` (`model_id`,`model_type`);--> statement-breakpoint
CREATE INDEX `model_has_roles_model_id_model_type_index` ON `model_has_roles` (`model_id`,`model_type`);--> statement-breakpoint
CREATE INDEX `notifications_notifiable_type_notifiable_id_index` ON `notifications` (`notifiable_type`,`notifiable_id`);--> statement-breakpoint
CREATE INDEX `personal_access_tokens_tokenable_type_tokenable_id_index` ON `personal_access_tokens` (`tokenable_type`,`tokenable_id`);
*/