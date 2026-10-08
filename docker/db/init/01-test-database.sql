-- Runs once, when the MySQL data volume is created (see docker-compose.yml).
-- The `app` user only gets the database from MYSQL_DATABASE, so the test
-- database and its grant are created here. Existing volumes: see README / bin/db-test-reset.
CREATE DATABASE IF NOT EXISTS `app_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci;
GRANT ALL PRIVILEGES ON `app_test`.* TO 'app'@'%';
