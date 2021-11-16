<?php

namespace Flexycms\AssetManager;

use Exception;

class ScriptManager
{
    private array $scripts;
    private ?string $compiledFile = null;
    private IFileDispatcher $fileDispatcher;
    private IJsCompressor $jsCompressor;

    private array $ignoredFiles = [];

    public function __construct(IFileDispatcher $fileDispatcher, IJsCompressor $jsCompressor)
    {
        $this->scripts = [];
        $this->fileDispatcher = $fileDispatcher;
        $this->jsCompressor = $jsCompressor;
    }


    /**
     * Add script file to ignore array - to skip it when collect data from directory (look ::addDir() method)
     * Files, passed to ::setCompiledFile() will add to ignored automatically, they do not need to be added manually by this method
     * @param string|string[] $scripts file of files to ignore
     */
    public function addIgnoreFile($scripts): void
    {
        if (gettype($scripts) === 'string') {
            $scripts = [$scripts];
        }
        $this->ignoredFiles = array_merge($this->ignoredFiles, $scripts);
    }


    /**
     * Add script file or script files array to manager
     * @param string|string[] $scripts full name to script file
     * @param bool $compile is added scripts need to be compiled
     * @throws Exception
     */
    public function addFile($scripts, $compile = true)
    {
        $this->checkCompiledFile();
        if (gettype($scripts) === 'string') {
            $scripts = [$scripts];
        }

        if (gettype($scripts) === 'array') {
            foreach($scripts as $script) {
                if (!$this->fileDispatcher->isFile($script)) {
                    $this->scripts[] = ['file' => $script, 'compile' => false];
                } else {
                    $this->scripts[] = ['file' => $script, 'compile' => $compile];
                }
            }
        }
    }

    /**
     * Sets the file where all scripts will be collected
     * Files, passed to this method will automatically add to ignored, they do not need to be added manually by ::addIgnoreFile()
     * @param string $compiledFile filename of compiled scripts
     */
    public function setCompiledFile(string $compiledFile): void
    {
        $this->compiledFile = $compiledFile;
        $this->ignoredFiles[] = $compiledFile;
    }

    /**
     * Add all script files from directory, except files added by ::ignore() method
     * @param string $scriptsDir
     * @throws Exception
     */
    public function addDir(string $scriptsDir)
    {
        $this->checkCompiledFile();

        $files = $this->fileDispatcher->readDir($scriptsDir);

        foreach($files as $entry) {
            if (strlen($entry) < 3) continue;
            if (substr($entry, -2) !== 'js') continue;
            if (in_array($scriptsDir . '/' . $entry, $this->ignoredFiles)) continue;
            $this->scripts[] = ['file' => $scriptsDir . '/' . $entry, 'compile' => true];
        }
    }

    /**
     * Return script files array
     * @return array
     * @throws Exception
     */
    public function get(): array
    {
        $this->checkCompiledFile();
        $this->compile();

        $result = [];

        // Need to found last "compile" file
        $lastCompileFileIndex = false;
        for($i = 0; $i < count($this->scripts); $i++) {
            if ($this->scripts[$i]['compile']) {
                $lastCompileFileIndex = $i;
            }
        }

        $compiledFileAdded = false;
        for($i = 0; $i < count($this->scripts); $i++) {
            $script = $this->scripts[$i];
            if ($script['compile']) continue;

            // Add compiled file in last "compile" file place
            if ($lastCompileFileIndex !== false && !$compiledFileAdded && $i > $lastCompileFileIndex) {
                $result[] = $this->compiledFile;
                $compiledFileAdded = true;
            }
            $result[] = $script['file'];
        }
        if ($lastCompileFileIndex !== false && !$compiledFileAdded) {
            $result[] = $this->compiledFile;
        }
        return $result;
    }


    /**
     * Return scripts path array
     * @param ?string $DOCUMENT_ROOT Full path to site. If null, will be set to $_SERVER['DOCUMENT_ROOT']
     * @return array
     * @throws Exception
     */
    public function getRefs(?string $DOCUMENT_ROOT = null): array
    {
        if ($DOCUMENT_ROOT === null) {
            if (!isset($_SERVER)) throw new Exception("Method ::getRefs without parameters used only in web environment");
            if (!isset($_SERVER['DOCUMENT_ROOT'])) throw new Exception("Method ::getRefs without parameters used only in web environment");
            $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        }

        $fileData = $this->get();
        $data = [];
        foreach($fileData as $item) {
            if ($this->fileDispatcher->isFile($item)) {
                $data[] = substr($item, strlen($DOCUMENT_ROOT));
            } else {
                $data[] = $item;
            }
        }
        return $data;
    }



    /**
     * @throws Exception
     */
    private function checkCompiledFile() {
        if ($this->compiledFile === null) throw new Exception("You must call ::setCompiledFile before work with scripts");
    }

    private function compile()
    {
        // Collect js data
        $jsContent = '';
        foreach($this->scripts as $script) {
            if ($script['compile']) {
                $jsContent .= $this->fileDispatcher->read($script['file']) . PHP_EOL;
            }
        }

        $jsContent = $this->jsCompressor->compress($jsContent);

        // TODO Add mtime check??
        // Save compiled file
        if (strlen($jsContent) !== 0) {
            if ($this->fileDispatcher->isFile($this->compiledFile)) {
                $this->fileDispatcher->unlink($this->compiledFile);
            }
            $this->fileDispatcher->save($this->compiledFile, $jsContent);
        }
    }
}