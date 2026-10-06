INSERT INTO
    `members` (
        `id`,
        `nickname`,
        `password`,
        `firstname`,
        `surname`,
        `email`,
        `role`,
        `last_logon`,
        `active`
    )
VALUES (
        1,
        'test-admin',
        'fba19bd88890b69c49c5fb597fd7d7b30b9394b70e387421b2d6f8cb67fe927e114c116493a35c247adafda828ec3ab57f4bc9d31fc0597f1c2519ff5c8bf0f7',
        'Test',
        'Admin',
        'test-admin@example.com',
        'admin',
        NULL,
        1
    ),
    (
        2,
        'test-editor',
        '1cf9bb30b548af20668b15fe575d095698bdd8c43b2b0856646c04cc3dc4be326b3c6907d671ce21dc6d6cc31489c99aab832a91569adf97a27d194c2a8b23de',
        'Test',
        'Editor',
        'test-editor@example.com',
        'editor',
        NULL,
        1
    );