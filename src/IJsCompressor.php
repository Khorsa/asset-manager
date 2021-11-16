<?php


namespace Flexycms\AssetManager;


interface IJsCompressor
{
    public function compress(string $jsContent): string;
}