<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class BrowserDialogTest extends TestCase
{
    public function test_views_do_not_use_native_browser_dialogs(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('views'))
        );

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            $this->assertDoesNotMatchRegularExpression(
                '/(?<!AppConfirm\.)\b(?:alert|confirm|prompt)\s*\(/',
                $contents,
                "Native browser dialog found in {$file->getPathname()}"
            );
        }
    }

    public function test_shared_dialog_exposes_confirmation_and_alert_modes(): void
    {
        $contents = file_get_contents(resource_path('views/shared/confirm-dialog.blade.php'));

        $this->assertStringContainsString('window.AppConfirm = { open, alert: alertDialog }', $contents);
        $this->assertStringContainsString('showCancel: false', $contents);
    }
}
