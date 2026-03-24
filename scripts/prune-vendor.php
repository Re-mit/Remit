<?php

declare(strict_types=1);

$vendorDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor';

if (!is_dir($vendorDir)) {
    fwrite(STDOUT, "vendor directory not found.\n");
    exit(0);
}

$targetNames = ['.git', 'tools'];
$paths = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($vendorDir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    if (!$item->isDir()) {
        continue;
    }

    if (!in_array($item->getFilename(), $targetNames, true)) {
        continue;
    }

    $paths[] = $item->getPathname();
}

usort(
    $paths,
    static fn (string $left, string $right): int => strlen($right) <=> strlen($left)
);

$removedCount = 0;

foreach ($paths as $path) {
    if (!is_dir($path)) {
        continue;
    }

    removeDirectory($path);
    $removedCount++;
}

fwrite(STDOUT, "Pruned {$removedCount} vendor directories.\n");

function removeDirectory(string $directory): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        $path = $item->getPathname();

        if ($item->isDir() && !$item->isLink()) {
            makeWritable($path);

            if (!rmdir($path)) {
                throw new RuntimeException("Failed to remove directory: {$path}");
            }

            continue;
        }

        makeWritable($path);

        if (!unlink($path)) {
            throw new RuntimeException("Failed to remove file: {$path}");
        }
    }

    makeWritable($directory);

    if (!rmdir($directory)) {
        throw new RuntimeException("Failed to remove directory: {$directory}");
    }
}

function makeWritable(string $path): void
{
    @chmod($path, 0777);
}
