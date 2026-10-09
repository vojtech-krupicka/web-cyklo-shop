-- Mock data for development and testing. Not real shop content.
-- Keeps the original menu structure (including nested "Sortiment" items).

INSERT INTO `pages` (`id`, `heading`, `seo_title`, `seo_keywords`, `seo_description`, `content`, `created`, `modified`, `allow_comments`, `is_homepage`) VALUES
(1, 'Vítejte v testovacím cyklo-shopu', 'Prodej a servis jízdních kol', 'cyklo, shop, jízdní kola, servis', 'Testovací úvodní stránka cyklo-shopu.', '<h2>Vítejte</h2>\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Toto je testovací obsah úvodní stránky.</p>', '2024-01-01 10:00:00', NULL, 0, 1),
(2, 'Historie', 'Historie', '', '', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Testovací text o historii obchodu.</p>', '2024-01-01 10:05:00', NULL, 0, 0),
(3, 'Sortiment', 'Sortiment', 'sortiment, jízdní kola', '', '<p>Přehled sortimentu. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>', '2024-01-01 10:10:00', NULL, 0, 0),
(4, 'Náhradní díly', 'Náhradní díly', 'přehazovačky, řetězy, brzdy', '', '<p>Testovací nabídka náhradních dílů. Ut enim ad minim veniam, quis nostrud exercitation.</p>\r\n<ul>\r\n<li>Řetězy</li>\r\n<li>Kazety</li>\r\n<li>Brzdy</li>\r\n</ul>', '2024-01-01 10:15:00', NULL, 0, 0),
(5, 'Oblečení', 'Oblečení', 'cyklistické oblečení', '', '<p>Testovací nabídka cyklistického oblečení. Duis aute irure dolor in reprehenderit.</p>', '2024-01-01 10:20:00', NULL, 0, 0),
(6, 'Minidrogerie', 'Minidrogerie', '', '', '<p>Testovací nabídka minidrogerie. Excepteur sint occaecat cupidatat non proident.</p>', '2024-01-01 10:25:00', NULL, 0, 0),
(7, 'Příslušenství', 'Příslušenství', 'přilby, osvětlení, nářadí', '', '<p>Testovací nabídka příslušenství. Lorem ipsum dolor sit amet.</p>', '2024-01-01 10:30:00', NULL, 0, 0),
(8, 'Akce a slevy', 'Akce a slevy', '', '', '<h2>Aktuální akce</h2>\r\n<p>Testovací akční nabídka se slevou 10 %.</p>', '2024-01-01 10:35:00', NULL, 0, 0),
(9, 'Servis a opravy', 'Servis a opravy', 'servis, opravy kol', '', '<p>Testovací popis servisu a oprav jízdních kol.</p>', '2024-01-01 10:40:00', NULL, 0, 0),
(10, 'Z cest', 'Z cest', '', '', '<p>Testovací cestopisné zápisky. Nemo enim ipsam voluptatem quia voluptas sit.</p>', '2024-01-01 10:45:00', NULL, 1, 0),
(11, 'Půjčovna', 'Půjčovna', 'půjčovna kol', '', '<p>Testovací informace o půjčovně kol včetně ceníku.</p>', '2024-01-01 10:50:00', NULL, 0, 0),
(12, 'Novinky', 'Novinky', '', '', '<p>Testovací stránka s novinkami.</p>', '2024-01-01 10:55:00', NULL, 0, 0);

INSERT INTO `menu_items` (`id`, `parent_id`, `page_id`, `name`, `title`, `url_query`, `url_fragment`, `url_rewrite_name`, `url`, `sort_order`, `active`) VALUES
(1, NULL, 2, 'Historie', 'Historie', '', '', 'historie', 'historie', 1, 1),
(2, NULL, 3, 'Sortiment', 'Sortiment', '', '', 'sortiment', 'sortiment', 2, 1),
(3, 2, 4, 'Náhradní díly', 'Náhradní díly', '', '', 'nahradni-dily', 'sortiment/nahradni-dily', 0, 1),
(4, 2, 5, 'Oblečení', 'Oblečení', '', '', 'obleceni', 'sortiment/obleceni', 1, 1),
(5, 2, 6, 'Minidrogerie', 'Minidrogerie', '', '', 'minidrogerie', 'sortiment/minidrogerie', 3, 1),
(6, 2, 7, 'Příslušenství', 'Příslušenství', '', '', 'prislusenstvi', 'sortiment/prislusenstvi', 2, 1),
(7, NULL, 8, 'Akce a slevy', 'Akce a slevy', '', '', 'akce-a-slevy', 'akce-a-slevy', 3, 1),
(8, NULL, 9, 'Servis a opravy', 'Servis a opravy', '', '', 'servis-a-opravy', 'servis-a-opravy', 4, 1),
(9, NULL, 10, 'Z cest', 'Z cest', '', '', 'z-cest', 'z-cest', 5, 1),
(10, NULL, 11, 'Půjčovna', 'Půjčovna', '', '', 'pujcovna', 'pujcovna', 6, 1),
(11, NULL, 12, 'Novinky', 'Novinky', '', '', 'novinky', 'novinky', 0, 1);

INSERT INTO `galleries` (`id`, `name`, `description`, `added`, `active`, `allow_comments`) VALUES
(1, 'Obchod', 'Testovací galerie s fotografiemi obchodu.', '2024-02-01 12:00:00', 1, 1),
(2, 'Akce', 'Testovací galerie s fotografiemi z akcí.', '2024-02-02 12:00:00', 1, 0);

INSERT INTO `gallery_items` (`id`, `gallery_id`, `file_name`, `title`, `description`, `added`, `sort_order`, `active`) VALUES
(1, 1, 'test1.jpg', 'Pohled na obchod', '', '2024-02-01 12:01:00', 0, 1),
(2, 1, 'test2.jpg', 'Uvnitř obchodu', '', '2024-02-01 12:02:00', 1, 1),
(3, 1, 'test3.jpg', 'Opravna jízdních kol', '', '2024-02-01 12:03:00', 2, 1),
(4, 1, 'test4.jpg', 'Sortiment a příslušenství', '', '2024-02-01 12:04:00', 3, 1),
(5, 2, 'test5.jpg', 'Společná jízda', '', '2024-02-02 12:01:00', 0, 1),
(6, 2, 'test6.jpg', 'Závod v okolí', '', '2024-02-02 12:02:00', 1, 1);

INSERT INTO `news` (`id`, `title`, `content`, `added`, `active`, `allow_comments`) VALUES
(1, 'Testovací novinka', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>', '2024-03-01 09:00:00', 1, 1);
