<?php

class Dispatcher extends DispatcherCore
{
    protected function setRequestUri()
    {
        parent::setRequestUri();
        $current_iso_lang = Tools::getValue('isolang');
        if ($this->use_routes && Language::isMultiLanguageActivated() && !$current_iso_lang) {
            $_GET['isolang'] = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
        }
    }
}
