<?php

namespace Flexycms\AssetManager;

use Exception;
use PHPUnit\Framework\TestCase;

class JsCompressorTest extends TestCase
{
    public function testEmpty()
    {
        $jsCompressor = new JsCompressor();

        $res = $jsCompressor->compress("");

        $this->assertEquals("", $res);
    }

    public function testEOL()
    {
        $jsCompressor = new JsCompressor();

        $res = $jsCompressor->compress("\r\n");
        $this->assertEquals("", $res);

        $res = $jsCompressor->compress(PHP_EOL . PHP_EOL . PHP_EOL);
        $this->assertEquals("", $res);

        $res = $jsCompressor->compress(PHP_EOL . "script();" . PHP_EOL);
        $this->assertEquals("script();" . PHP_EOL, $res);

        $res = $jsCompressor->compress(PHP_EOL . "script1();" . PHP_EOL . PHP_EOL . PHP_EOL . "script2();" . PHP_EOL);
        $this->assertEquals("script1();" . PHP_EOL . "script2();" . PHP_EOL, $res);
    }

    public function testComments()
    {
        $jsCompressor = new JsCompressor();

        $res = $jsCompressor->compress("/* comment */" . PHP_EOL . "script();" . PHP_EOL);
        $this->assertEquals("script();" . PHP_EOL, $res);

        $res = $jsCompressor->compress("//comment" . PHP_EOL . "script();" . PHP_EOL);
        $this->assertEquals("script();" . PHP_EOL, $res);

        $res = $jsCompressor->compress("script(); //comment" . PHP_EOL);
        $this->assertEquals("script();" . PHP_EOL, $res);
    }
}
