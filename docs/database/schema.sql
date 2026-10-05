SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS `hotel_api`;

CREATE DATABASE `hotel_api`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `hotel_api`;

CREATE TABLE `hotels` (
                          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                          `external_id` VARCHAR(50) NOT NULL,
                          `name` VARCHAR(255) NOT NULL,
                          `created_at` TIMESTAMP NULL,
                          `updated_at` TIMESTAMP NULL,

                          PRIMARY KEY (`id`),
                          UNIQUE KEY `uq_hotels_external_id` (`external_id`)
) ENGINE = InnoDB;

CREATE TABLE `rooms` (
                         `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                         `hotel_id` BIGINT UNSIGNED NOT NULL,
                         `external_id` VARCHAR(50) NOT NULL,
                         `name` VARCHAR(255) NOT NULL,
                         `created_at` TIMESTAMP NULL,
                         `updated_at` TIMESTAMP NULL,

                         PRIMARY KEY (`id`),
                         UNIQUE KEY `uq_rooms_hotel_external_id` (`hotel_id`, `external_id`),

                         CONSTRAINT `fk_rooms_hotel_id`
                             FOREIGN KEY (`hotel_id`)
                                 REFERENCES `hotels` (`id`)
                                 ON DELETE RESTRICT
                                 ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `guests` (
                          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                          `name` VARCHAR(255) NOT NULL,
                          `last_name` VARCHAR(255) NOT NULL,
                          `phone` VARCHAR(20) NOT NULL,
                          `created_at` TIMESTAMP NULL,
                          `updated_at` TIMESTAMP NULL,

                          PRIMARY KEY (`id`),
                          UNIQUE KEY `uq_guests_phone` (`phone`)
) ENGINE = InnoDB;

CREATE TABLE `reserves` (
                            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                            `external_id` VARCHAR(50) NOT NULL,
                            `hotel_id` BIGINT UNSIGNED NOT NULL,
                            `room_id` BIGINT UNSIGNED NOT NULL,
                            `guest_id` BIGINT UNSIGNED NOT NULL,
                            `check_in` DATE NOT NULL,
                            `check_out` DATE NOT NULL,
                            `total` DECIMAL(10, 2) UNSIGNED NOT NULL,
                            `created_at` TIMESTAMP NULL,
                            `updated_at` TIMESTAMP NULL,

                            PRIMARY KEY (`id`),
                            UNIQUE KEY `uq_reserves_external_id` (`external_id`),
                            KEY `idx_reserves_hotel_id` (`hotel_id`),
                            KEY `idx_reserves_guest_id` (`guest_id`),
                            KEY `idx_reserves_room_dates` (`room_id`, `check_in`, `check_out`),

                            CONSTRAINT `chk_reserves_check_out_after_check_in`
                                CHECK (`check_out` > `check_in`),

                            CONSTRAINT `fk_reserves_hotel_id`
                                FOREIGN KEY (`hotel_id`)
                                    REFERENCES `hotels` (`id`)
                                    ON DELETE RESTRICT
                                    ON UPDATE CASCADE,

                            CONSTRAINT `fk_reserves_room_id`
                                FOREIGN KEY (`room_id`)
                                    REFERENCES `rooms` (`id`)
                                    ON DELETE RESTRICT
                                    ON UPDATE CASCADE,

                            CONSTRAINT `fk_reserves_guest_id`
                                FOREIGN KEY (`guest_id`)
                                    REFERENCES `guests` (`id`)
                                    ON DELETE RESTRICT
                                    ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `reserve_dailies` (
                                   `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                   `reserve_id` BIGINT UNSIGNED NOT NULL,
                                   `date` DATE NOT NULL,
                                   `value` DECIMAL(10, 2) UNSIGNED NOT NULL,
                                   `created_at` TIMESTAMP NULL,
                                   `updated_at` TIMESTAMP NULL,

                                   PRIMARY KEY (`id`),
                                   UNIQUE KEY `uq_reserve_dailies_reserve_date` (`reserve_id`, `date`),

                                   CONSTRAINT `fk_reserve_dailies_reserve_id`
                                       FOREIGN KEY (`reserve_id`)
                                           REFERENCES `reserves` (`id`)
                                           ON DELETE CASCADE
                                           ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `payment_methods` (
                                   `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                   `external_id` VARCHAR(50) NOT NULL,
                                   `created_at` TIMESTAMP NULL,
                                   `updated_at` TIMESTAMP NULL,

                                   PRIMARY KEY (`id`),
                                   UNIQUE KEY `uq_payment_methods_external_id` (`external_id`)
) ENGINE = InnoDB;

CREATE TABLE `payments` (
                            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                            `reserve_id` BIGINT UNSIGNED NOT NULL,
                            `payment_method_id` BIGINT UNSIGNED NOT NULL,
                            `value` DECIMAL(10, 2) UNSIGNED NOT NULL,
                            `created_at` TIMESTAMP NULL,
                            `updated_at` TIMESTAMP NULL,

                            PRIMARY KEY (`id`),
                            KEY `idx_payments_payment_method_id` (`payment_method_id`),

                            CONSTRAINT `fk_payments_reserve_id`
                                FOREIGN KEY (`reserve_id`)
                                    REFERENCES `reserves` (`id`)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE,

                            CONSTRAINT `fk_payments_payment_method_id`
                                FOREIGN KEY (`payment_method_id`)
                                    REFERENCES `payment_methods` (`id`)
                                    ON DELETE RESTRICT
                                    ON UPDATE CASCADE
) ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
