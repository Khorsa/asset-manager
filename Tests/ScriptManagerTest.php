<?php

namespace Flexycms\AssetManager;

use Exception;
use PHPUnit\Framework\TestCase;

class ScriptManagerTest extends TestCase
{
    private ?ScriptManager $manager;

    protected function setUp(): void
    {
        $mock = $this->getMockBuilder(IFileDispatcher::class)->getMock();

        $mock
            ->method('isFile')
            ->willReturn(true);

        $mock
            ->method('readDir')
            ->willReturn(['file1.js', 'file2.js', 'file3.php', 'compiled.js']);

        $this->manager = new ScriptManager($mock);
    }


    protected function tearDown(): void
    {
        $this->manager = null;
    }



    public function testAddFileWOSetCompiledFile()
    {
        $this->expectException(Exception::class);
        $this->manager->addFile("test", false);
    }


    public function testAddFile()
    {
        $this->manager->setCompiledFile("compiled.js");
        $this->manager->addFile("test0.js", false);
        $data = $this->manager->get();

        $this->assertEquals(1, count($data));
        $this->assertEquals('test0.js', $data[0]);
    }


    public function testAddDir()
    {
        $this->manager->setCompiledFile("dir/compiled.js");
        $this->manager->addDir("dir");
        $data = $this->manager->get();

        $this->assertEquals(1, count($data));
        $this->assertEquals('dir/compiled.js', $data[0]);
    }


}
