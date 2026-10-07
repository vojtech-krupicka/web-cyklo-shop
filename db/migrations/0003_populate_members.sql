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
        '$2y$12$mMYtfW8z83L/Rf8TVOt7SuJ0.Wv1JgC4z1SP0OY853H.udJ3s4KDO',
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
        '$2y$12$zAsRDNOLSVM4cYgKJE641ubtFvW6r8.6vU/nO6fRKj7FCvwdGYtfq',
        'Test',
        'Editor',
        'test-editor@example.com',
        'editor',
        NULL,
        1
    );
