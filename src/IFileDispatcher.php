<?php


namespace Flexycms\AssetManager;


interface IFileDispatcher
{
    public function isFile(string $file): bool;
    public function readDir(string $dir): array;
    public function unlink(string $file): void;
    public function save(string $file, string $content): void;
    public function read(string $file): string;
}