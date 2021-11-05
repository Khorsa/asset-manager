<?php

namespace Flexycms\AssetManager;

class AssetManager
{
    private ScriptManager $scriptManager;
    private StyleManager $styleManager;

    public function __construct()
    {
        $this->scriptManager = new ScriptManager();
        $this->styleManager = new StyleManager();
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