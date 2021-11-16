<?php

namespace Flexycms\AssetManager;

class AssetManager
{
    private ScriptManager $scriptManager;
    private StyleManager $styleManager;

    public function __construct()
    {
        $fileDispatcher = new FileDispatcher();
        $jsCompressor = new JsCompressor();
        $this->scriptManager = new ScriptManager($fileDispatcher, $jsCompressor);
        $this->styleManager = new StyleManager($fileDispatcher);
    }

    /**
     * @return ScriptManager
     */
    public function scripts(): ScriptManager
    {
        return $this->scriptManager;
    }

    /**
     * @return StyleManager
     */
    public function styles(): StyleManager
    {
        return $this->styleManager;
    }
}