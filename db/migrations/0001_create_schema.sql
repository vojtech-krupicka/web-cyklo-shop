-- Initial schema of the cyklo-shop application (tables, indexes, foreign keys).
-- Derived from the 2017 production dump; charset modernised to utf8mb4.

DROP TABLE IF EXISTS `comments`;

DROP TABLE IF EXISTS `menu_items`;

DROP TABLE IF EXISTS `gallery_items`;

DROP TABLE IF EXISTS `pages`;

DROP TABLE IF EXISTS `galleries`;

DROP TABLE IF EXISTS `news`;

DROP TABLE IF EXISTS `members`;

CREATE TABLE `pages` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `heading` varchar(64) NOT NULL DEFAULT '',
    `seo_title` varchar(64) NOT NULL DEFAULT '',
    `seo_keywords` varchar(256) NOT NULL DEFAULT '',
    `seo_description` varchar(256) NOT NULL DEFAULT '',
    `content` text NOT NULL,
    `created` datetime NOT NULL,
    `modified` datetime DEFAULT NULL,
    `allow_comments` tinyint(1) NOT NULL DEFAULT '0',
    `is_homepage` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci;

CREATE TABLE `galleries` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(64) NOT NULL DEFAULT '',
    `description` varchar(256) NOT NULL DEFAULT '',
    `added` datetime NOT NULL,
    `active` tinyint(1) NOT NULL DEFAULT '0',
    `allow_comments` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci;

CREATE TABLE `gallery_items` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `gallery_id` int unsigned NOT NULL,
    `file_name` varchar(64) NOT NULL DEFAULT '',
    `title` varchar(64) NOT NULL DEFAULT '',
    `description` varchar(256) NOT NULL DEFAULT '',
    `added` datetime NOT NULL,
    `sort_order` int unsigned NOT NULL DEFAULT '0',
    `active` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `fk_gallery_items_galleries1` (`gallery_id`),
    CONSTRAINT `fk_gallery_items_galleries1` FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci;

CREATE TABLE `news` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(256) NOT NULL DEFAULT '',
    `content` text NOT NULL,
    `added` datetime NOT NULL,
    `active` tinyint(1) NOT NULL DEFAULT '0',
    `allow_comments` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci;

CREATE TABLE `comments` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `parent_id` int unsigned DEFAULT NULL,
    `gallery_id` int unsigned DEFAULT NULL,
    `page_id` int unsigned DEFAULT NULL,
    `news_id` int unsigned DEFAULT NULL,
    `nickname` varchar(32) NOT NULL DEFAULT '',
    `email` varchar(96) NOT NULL DEFAULT '',
    `subject` varchar(96) NOT NULL DEFAULT '',
    `comment` text NOT NULL,
    `added` datetime NOT NULL,
    PRIMARY KEY (`id`),
    KEY `fk_comments_pages1` (`page_id`),
    KEY `fk_comments_comments1` (`parent_id`),
    KEY `fk_comments_galleries1` (`gallery_id`),
    KEY `fk_comments_news1` (`news_id`),
    CONSTRAINT `fk_comments_comments1` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
    CONSTRAINT `fk_comments_galleries1` FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_comments_news1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_comments_pages1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci;

CREATE TABLE `members` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `nickname` varchar(32) NOT NULL DEFAULT '',
    `password` char(128) NOT NULL,
    `firstname` varchar(32) NOT NULL DEFAULT '',
    `surname` varchar(32) NOT NULL DEFAULT '',
    `email` varchar(96) NOT NULL DEFAULT '',
    `role` varchar(32) NOT NULL DEFAULT '',
    `last_logon` datetime DEFAULT NULL,
    `active` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci;

CREATE TABLE `menu_items` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `parent_id` int unsigned DEFAULT NULL,
    `page_id` int unsigned DEFAULT NULL,
    `name` varchar(64) NOT NULL DEFAULT '',
    `title` varchar(64) NOT NULL DEFAULT '',
    `url_query` varchar(256) NOT NULL DEFAULT '',
    `url_fragment` varchar(64) NOT NULL DEFAULT '',
    `url_rewrite_name` varchar(64) NOT NULL DEFAULT '',
    `url` varchar(256) NOT NULL DEFAULT '',
    `sort_order` int unsigned NOT NULL DEFAULT '0',
    `active` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `fk_menu_items_menu_items` (`parent_id`),
    KEY `fk_menu_items_pages1` (`page_id`),
    CONSTRAINT `fk_menu_items_menu_items` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_menu_items_pages1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci;