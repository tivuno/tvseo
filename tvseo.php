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

class Tvseo extends Module
{
    public function __construct()
    {
        $this->name = 'tvseo';
        $this->tab = 'seo';
        $this->version = '1.0.1';
        $this->author = 'tivuno.com';
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->displayName = $this->l('Selefkos - SEO PrestaShop module');
        $this->description = $this->l('Climb the great wall of Google search engine results page (SERP)');

        parent::__construct();
    }
}
