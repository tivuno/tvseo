<?php
class Link extends LinkCore
{
    protected function getLangLink($idLang = null, Context $context = null, $idShop = null)
    {
        if ($idLang == Configuration::get('PS_LANG_DEFAULT')) {
            return '';
        }

        return Language::getIsoById($idLang) . '/';
    }
}
