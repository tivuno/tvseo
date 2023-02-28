<?php
/**
 * PrestaShop SEO module ”Selefkos”
 * @author    tivuno.com
 * @copyright 2018 - 2023 © tivuno.com
 * @license   https://tivuno.com/blog/business/basic-license
 */
class Link extends LinkCore
{
    public function getCategoryLink(
        $category,
        $alias = null,
        $idLang = null,
        $selectedFilters = null,
        $idShop = null,
        $relativeProtocol = false
    )
    {
        $dispatcher = Dispatcher::getInstance();
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }
        $url = $this->getBaseLink($idShop, null, $relativeProtocol) . $this->getLangLink($idLang, null, $idShop);
        $params = [];
        if (!is_object($category)) {
            $params['id'] = $category;
        } else {
            $params['id'] = $category->id;
        }
        $selectedFilters = is_null($selectedFilters) ? '' : $selectedFilters;
        if (empty($selectedFilters)) {
            $rule = 'category_rule';
        } else {
            $rule = 'layered_rule';
            $params['selected_filters'] = $selectedFilters;
        }
        if (!is_object($category)) {
            $category = new Category($category, $idLang);
        }
        $params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
        if ($dispatcher->hasKeyword($rule, $idLang, 'meta_keywords', $idShop)) {
            $params['meta_keywords'] = Tools::str2url($category->getFieldByLang('meta_keywords'));
        }
        if ($dispatcher->hasKeyword($rule, $idLang, 'meta_title', $idShop)) {
            $params['meta_title'] = Tools::str2url($category->getFieldByLang('meta_title'));
        }
        if (Dispatcher::getInstance()->hasKeyword('category_rule', $idLang, 'categories', $idShop)) {
            $p = [];
            foreach ($category->getParentsCategories($idLang) as $c) {
                if (!$c['is_root_category'] && $c['id_category'] != $category->id)
                    $p[$c['level_depth']] = $c['link_rewrite'];
            }
            $params['categories'] = implode('/', array_reverse($p));
        }

        return $url . Dispatcher::getInstance()->createUrl($rule, $idLang, $params, $this->allow, '', $idShop);
    }
}
