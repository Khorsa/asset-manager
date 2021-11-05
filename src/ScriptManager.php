<?php

namespace Flexycms\AssetManager;

use Exception;

class ScriptManager
{
    private array $scripts;
    private ?string $compiledFile = null;
    private IFileDispatcher $fileDispatcher;

    public function __construct(?IFileDispatcher $fileDispatcher = null)
    {
        $this->scripts = [];
        if ($fileDispatcher === null) {
            $this->fileDispatcher = new FileDispatcher();
        } else {
            $this->fileDispatcher = $fileDispatcher;
        }
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
     * @param string $compiledFile filename of compiled scripts
     */
    public function setCompiledFile(string $compiledFile): void
    {
        $this->compiledFile = $compiledFile;
    }

    /**
     * Add all script files from directory
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
            if ($scriptsDir . '/' . $entry === $this->compiledFile) continue;
            $this->scripts[] = ['file' => $scriptsDir . '/' . $entry, 'compile' => true];
        }
    }

    /**
     * Return script array
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



    public function getRefs($DOCUMENT_ROOT = null): array
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

        // Remove comments
        $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/';
        $jsContent = preg_replace($pattern, '', $jsContent);

        // Remove double EOF
        $jsContent = str_replace("\r\n", PHP_EOL, $jsContent);
        for($i = 0; $i < 25; $i++) {
            $jsContent = str_replace(PHP_EOL.PHP_EOL, PHP_EOL, $jsContent);
            $jsContent = str_replace(PHP_EOL . "  ", PHP_EOL, $jsContent);
        }
        if (substr($jsContent, 0, strlen(PHP_EOL)) === PHP_EOL) {
            $jsContent = substr($jsContent, strlen(PHP_EOL));
        }

        // TODO Add mtime check??

        // Save compiled file
        if ($this->fileDispatcher->isFile($this->compiledFile)) {
            $this->fileDispatcher->unlink($this->compiledFile);
        }

        $this->fileDispatcher->save($this->compiledFile, $jsContent);
    }
}