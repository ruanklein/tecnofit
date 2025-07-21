CREATE TABLE IF NOT EXISTS `personal_records` (
    `id` int NOT NULL AUTO_INCREMENT,
    `user_id` int NOT NULL,
    `movement_id` int NOT NULL,
    `value` FLOAT NOT NULL,
    `date` DATETIME NOT NULL,
    PRIMARY KEY (`id`)
);

ALTER TABLE `personal_records` ADD CONSTRAINT `personal_records_fk0` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`);
ALTER TABLE `personal_records` ADD CONSTRAINT `personal_records_fk1` FOREIGN KEY (`movement_id`) REFERENCES `movements`(`id`);