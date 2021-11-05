<?php

namespace Flexycms\AssetManager;

use Exception;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

class StyleManager
{
    private array $styles;
    private IFileDispatcher $fileDispatcher;

    public function __construct(?IFileDispatcher $fileDispatcher = null)
    {
        $this->styles = [];
        if ($fileDispatcher === null) {
            $this->fileDispatcher = new FileDispatcher();
        } else {
            $this->fileDispatcher = $fileDispatcher;
        }
    }

    /**
     * Add CSS or SCSS file to list
     * @param string|string[] $styles
     */
    public function addFile($styles)
    {
        if (gettype($styles) === 'string') {
            $styles = [$styles];
        }

        if (gettype($styles) === 'array') {
            foreach($styles as $style) {
                $this->styles[] = $style;
            }
        }
    }

    private function checkDocumentRoot(?string $DOCUMENT_ROOT = null): string
    {
        if ($DOCUMENT_ROOT === null) {
            if (!isset($_SERVER)) throw new Exception("Method ::getRefs without parameters used only in web environment");
            if (!isset($_SERVER['DOCUMENT_ROOT'])) throw new Exception("Method ::getRefs without parameters used only in web environment");
            $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        }
        return $DOCUMENT_ROOT;
    }



    public function get(?string $DOCUMENT_ROOT = null): array
    {
        $DOCUMENT_ROOT = $this->checkDocumentRoot($DOCUMENT_ROOT);
        $this->compile($DOCUMENT_ROOT);
        return $this->styles;
    }


    public function getRefs(?string $DOCUMENT_ROOT = null): array
    {
        $DOCUMENT_ROOT = $this->checkDocumentRoot($DOCUMENT_ROOT);

        $fileData = $this->get($DOCUMENT_ROOT);
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


    private function compile(?string $DOCUMENT_ROOT = null): void
    {
        $DOCUMENT_ROOT = $this->checkDocumentRoot($DOCUMENT_ROOT);

        $data = [];
        foreach($this->styles as $style) {
            if (substr($style, -5) === '.scss') {
                // Compile SCSS
                $scssFile = $style;

                $dirname = dirname($scssFile);
                $basename = basename($scssFile, ".scss");

                $scss = new Compiler();
                $scss->addImportPath(dirname($scssFile));
                $scss->setOutputStyle(OutputStyle::COMPRESSED);
                $scss->setSourceMap(Compiler::SOURCE_MAP_INLINE);
                $scss->setSourceMapOptions([
                    'sourceMapWriteTo'  => "{$dirname}/{$basename}.map",
                    'sourceMapURL'      => substr("{$dirname}/{$basename}.map", strlen($DOCUMENT_ROOT)),
                    'sourceMapFilename' => "{$basename}.scss",
                    'sourceMapBasepath' => $DOCUMENT_ROOT,
                    'sourceRoot'        => '/',
                ]);
                $scssContent = $this->fileDispatcher->read($scssFile);
                $style = $scss->compileString($scssContent, $scssFile);
                if ($this->fileDispatcher->isFile("{$dirname}/{$basename}.min.css")) {
                    $this->fileDispatcher->unlink("{$dirname}/{$basename}.min.css");
                }
                $this->fileDispatcher->save("{$dirname}/{$basename}.min.css", $style->getCss());
                $data[] = "{$dirname}/{$basename}.min.css";
            } else {
                $data[] = $style;
            }
        }

        $this->styles = $data;
    }
}