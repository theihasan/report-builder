<?php

namespace Ihasan\ReportBuilder\Tests;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ArchTest extends TestCase
{
    public function test_it_will_not_use_debugging_functions(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__.'/../src')
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            $this->assertIsString($contents);

            if (preg_match('/(?<![A-Za-z0-9_])(dd|dump|ray)\s*\(/', $contents, $matches) === 1) {
                $this->fail(sprintf('Found [%s(] in [%s].', $matches[1], $file->getPathname()));
            }
        }
    }
}
