<?php
/**
 * PrestaShop SEO module ”Selefkos”
 * @author    tivuno.com
 * @copyright 2018 - 2023 © tivuno.com
 * @license   https://tivuno.com/blog/business/basic-license
 */
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
