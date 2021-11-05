<?php

namespace Flexycms\AssetManager;

class FileDispatcher implements IFileDispatcher
{
    public function isFile(string $file): bool
    {
        return is_file($file);
    }

    public function readDir(string $dir): array
    {
        $files = [];
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry === '.') continue;
                if ($entry === '..') continue;
                if (!is_file($dir . '/' . $entry)) continue;
                $files[] = $entry;
            }
        }
        return $files;
    }

    public function unlink(string $file): void
    {
        unlink($file);
    }

    public function save(string $file, string $content): void
    {
        file_put_contents($file, $content);
    }

    public function read(string $file): string
    {
        return file_get_contents($file);
    }
}