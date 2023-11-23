<?php

class Dispatcher extends DispatcherCore
{
    protected function setRequestUri()
    {
        parent::setRequestUri();
        $remove_enabled = Configuration::get('FSAU_REMOVE_DEFAULT_LANG');
        $current_iso_lang = Tools::getValue('isolang');
        if ($this->use_routes && Language::isMultiLanguageActivated() && !$current_iso_lang && $remove_enabled) {
            $_GET['isolang'] = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
        }
    }
}
