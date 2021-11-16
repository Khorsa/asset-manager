<?php

namespace Flexycms\AssetManager;

use Exception;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\OutputStyle;

class StyleManager
{
    private array $styles;
    private IFileDispatcher $fileDispatcher;
    private bool $needSourcemap = true;

    public function __construct(IFileDispatcher $fileDispatcher)
    {
        $this->styles = [];
        $this->fileDispatcher = $fileDispatcher;
    }

    /**
     * Enable inline sourcemap for SCSS
     */
    public function enableSourcemap()
    {
        $this->needSourcemap = true;
    }

    /**
     * Disable inline sourcemap for SCSS
     */
    public function disableSourcemap()
    {
        $this->needSourcemap = false;
    }

    /**
     * Return true if sourcemap enabled
     * @return bool
     */
    public function isSourcemapEnabled(): bool
    {
        return $this->needSourcemap;
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


    /**
     * Return style files array
     * @param string|null $DOCUMENT_ROOT
     * @return array
     * @throws Exception
     * @throws SassException
     */
    public function get(?string $DOCUMENT_ROOT = null): array
    {
        $DOCUMENT_ROOT = $this->checkDocumentRoot($DOCUMENT_ROOT);
        $this->compile($DOCUMENT_ROOT);
        return $this->styles;
    }

    /**
     * Return styles path array
     * @param ?string $DOCUMENT_ROOT Full path to site. If null, will be set to $_SERVER['DOCUMENT_ROOT']
     * @return array
     * @throws Exception
     * @throws SassException
     */
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


    /**
     * @param string|null $DOCUMENT_ROOT
     * @return string
     * @throws Exception
     */
    private function checkDocumentRoot(?string $DOCUMENT_ROOT = null): string
    {
        if ($DOCUMENT_ROOT === null) {
            if (!isset($_SERVER)) throw new Exception("Method ::getRefs without parameters used only in web environment");
            if (!isset($_SERVER['DOCUMENT_ROOT'])) throw new Exception("Method ::getRefs without parameters used only in web environment");
            $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        }
        return $DOCUMENT_ROOT;
    }


    /**
     * @param string|null $DOCUMENT_ROOT
     * @throws SassException
     * @throws Exception
     */
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
                if ($this->needSourcemap) $scss->setSourceMap(Compiler::SOURCE_MAP_INLINE);
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

                if (strlen($style->getCss()) !== 0) {
                    $this->fileDispatcher->save("{$dirname}/{$basename}.min.css", $style->getCss());
                    $data[] = "{$dirname}/{$basename}.min.css";
                }
            } else {
                $data[] = $style;
            }
        }
        $this->styles = $data;
    }
}