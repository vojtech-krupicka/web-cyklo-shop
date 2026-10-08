<?php declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;

/**
 * Second layer against uploaded scripts: the upload folder is below the web root, so it ships a
 * .htaccess that forbids executing anything there (the first layer is the extension allowlist).
 */
final class UploadsHtaccessTest extends TestCase
{
    private function htaccess(): string
    {
        $path = dirname(__DIR__, 2) . '/www/resources/.htaccess';
        $this->assertFileExists($path, 'www/resources/.htaccess must be tracked in git and deployed');

        return (string) file_get_contents($path);
    }

    public function testScriptExtensionsAreDeniedInTheUploadFolder(): void
    {
        $htaccess = $this->htaccess();

        $this->assertStringContainsString('<FilesMatch', $htaccess);
        $this->assertStringContainsString('Require all denied', $htaccess);

        // the pattern between the quotes of <FilesMatch "...">
        if (preg_match('#<FilesMatch "([^"]+)">#', $htaccess, $m) !== 1) {
            $this->fail('the .htaccess has no <FilesMatch "..."> block');
        }
        $regex = '#' . str_replace('#', '\\#', preg_replace('#^\(\?i\)#', '', $m[1])) . '#i';
        foreach (['shell.php', 'shell.PHP', 'shell.phtml', 'shell.phar', 'shell.php5', 'shell.pht', 'shell.sh', 'shell.cgi', 'shell.php.txt', 'shell.phtml.zip', 'x.PHP.pdf'] as $name) {
            $this->assertSame(1, preg_match($regex, $name), "$name must be denied");
        }
        foreach (['photo.png', 'dokument.pdf', 'thumb_photo.jpg', 'archiv.zip', 'notes.txt'] as $name) {
            $this->assertSame(0, preg_match($regex, $name), "$name must stay reachable");
        }
    }
}
