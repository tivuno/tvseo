<?php
/**
 * Selefkos - SEO PrestaShop module
 *
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
 * @license   https://tivuno.com/blog/bp/business-news/2-basic-license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Link extends LinkCore
{
    protected function getLangLink($idLang = null, $context = null, $idShop = null)
    {
        if ($idLang == Configuration::get('PS_LANG_DEFAULT')) {
            return '';
        }

        return Language::getIsoById($idLang) . '/';
    }
}
