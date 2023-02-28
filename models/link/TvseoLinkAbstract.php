<?php

abstract class TvseoLinkAbstract
{
    const REGEX_ALPHA_NUMERIC = '[_a-zA-Z0-9-\pL]*';
    private $module;

    public function __construct($module, $configPrefix = null, $owner = null)
    {
        $this->module = $module;
    }
}
