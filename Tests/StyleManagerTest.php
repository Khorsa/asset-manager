<?php

namespace Flexycms\AssetManager;

use PHPUnit\Framework\TestCase;

class StyleManagerTest extends TestCase
{
    private ?StyleManager $manager;

    protected function setUp(): void
    {
        $mock = $this->getMockBuilder(IFileDispatcher::class)->getMock();

        $mock
            ->method('isFile')
            ->willReturn(true);

        $mock
            ->method('readDir')
            ->willReturn(['file1.css', 'file2.scss', 'file3.css']);

        $this->manager = new StyleManager($mock);
    }

    protected function tearDown(): void
    {
        $this->manager = null;
    }


    public function testAddFile()
    {
        $this->manager->addFile("test0.css");
        $data = $this->manager->get();

        $this->assertEquals(1, count($data));
        $this->assertEquals('test0.css', $data[0]);
    }

    public function testEnableSourcemap()
    {
        $this->manager->enableSourcemap();
        $this->assertEquals(true, $this->manager->isSourcemapEnabled());
    }
    public function testDisableSourcemap()
    {
        $this->manager->disableSourcemap();
        $this->assertEquals(false, $this->manager->isSourcemapEnabled());
    }



}
