<?php


namespace Flexycms\AssetManager;


class JsCompressor implements IJsCompressor
{
    public function compress(string $jsContent): string
    {
        // Remove comments
        $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/';
        $jsContent = preg_replace($pattern, '', $jsContent);

        // Remove double EOF
        $jsContent = str_replace("\r\n", "\n", $jsContent);
        $jsContent = str_replace("\n", PHP_EOL, $jsContent);

        do {
            $count1 = 0;
            $count2 = 0;
            $count3 = 0;
            $jsContent = str_replace(PHP_EOL.PHP_EOL, PHP_EOL, $jsContent, $count1);
            $jsContent = str_replace(PHP_EOL . " ", PHP_EOL, $jsContent, $count2);
            $jsContent = str_replace(" ". PHP_EOL, PHP_EOL, $jsContent, $count3);
        }while ($count1 !== 0 || $count2 !== 0 || $count3 !== 0);

        //Remove first EOF
        if (substr($jsContent, 0, strlen(PHP_EOL)) === PHP_EOL) {
            $jsContent = substr($jsContent, strlen(PHP_EOL));
        }

        return $jsContent;
    }
}