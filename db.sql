DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id`         int(11)               NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`            varchar(64)           NOT NULL,
  `email`           varchar(64)           NOT NULL UNIQUE,
  `password`        varchar(255)          NOT NULL,
  `country`         varchar(32)           NOT NULL,
  `timezone`        varchar(3)            NOT NULL,
  `salt`            varchar(32)
) CHARACTER SET utf8;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `session_id`      int(11)               NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`         int(11)               NOT NULL REFERENCES `users` (`user_id`) 
                                                   ON DELETE CASCADE ON UPDATE CASCADE,
  `user_sess_code`  varchar(15)           NOT NULL,
  `user_http_agent` varchar(255)          NOT NULL,
  `user_status`     enum('0','1','2','3') NOT NULL
) CHARACTER SET utf8;
