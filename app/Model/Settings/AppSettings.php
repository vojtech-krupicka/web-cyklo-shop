<?php declare(strict_types=1);

namespace App\Model\Settings;

final class AppSettings
{
    public function __construct(
        public readonly string $wwwDir,
        public readonly string $resourcesDir,
        /** Where the generated TinyMCE image/file lists (tinymce.*list.js) are written */
        public readonly string $tinyMceDir,
    ) {}
}
