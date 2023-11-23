<?php
/**
 * PrestaShop SEO module ”Selefkos”
 * @author    tivuno.com
 * @copyright 2018 - 2023 © tivuno.com
 * @license   https://tivuno.com/blog/business/basic-license
 */
/*require_once _PS_MODULE_DIR_ . 'tvseo/models/TvseoHelper.php';
require_once _PS_MODULE_DIR_ . 'tvseo/models/link/TvseoLink.php';
require_once _PS_MODULE_DIR_ . 'tvseo/models/link/TvseoLinkAbstract.php';
require_once _PS_MODULE_DIR_ . 'tvseo/models/link/TvseoLinkCategory.php';*/
class Tvseo extends Module
{
    public TvseoLink $urlConfig;

    protected array $controller_map = [
        'product' => [
            'name' => 'Product',
            'field_rewrite' => 'link_rewrite',
            'bo_controller' => 'Products',
            'func' => 'getProductLink',
            'route_id' => 'product_rule',
            'id' => 'product',
        ],
        'category' => [
            'name' => 'Category',
            'field_rewrite' => 'link_rewrite',
            'bo_controller' => 'Categories',
            'func' => 'getCategoryLink',
            'route_id' => 'category_rule',
            'id' => 'category',
        ],
        'supplier' => [
            'name' => 'Supplier',
            'field_rewrite' => 'name',
            'bo_controller' => 'Suppliers',
            'func' => 'getSupplierLink',
            'route_id' => 'supplier_rule',
            'id' => 'supplier',
        ],
        'manufacturer' => [
            'name' => 'Manufacturer',
            'field_rewrite' => 'name',
            'bo_controller' => 'Manufacturers',
            'func' => 'getManufacturerLink',
            'route_id' => 'manufacturer_rule',
            'id' => 'manufacturer',
        ],
        'cms_category' => [
            'name' => 'CMS Category',
            'field_rewrite' => 'link_rewrite',
            'bo_controller' => 'CmsContent',
            'func' => 'getCMSCategoryLink',
            'route_id' => 'cms_category_rule',
            'id' => 'cms_category',
        ],
        'cms' => [
            'name' => 'CMS',
            'field_rewrite' => 'link_rewrite',
            'bo_controller' => 'CmsContent',
            'func' => 'getCMSLink',
            'route_id' => 'cms_rule',
            'id' => 'cms',
        ],
        'st_blog' => [
            'name' => 'Blog article',
            'field_rewrite' => 'link_rewrite',
            'bo_controller' => 'StBlog',
            'func' => 'getModuleLink',
            'route_id' => 'module-stblog-article',
            'id' => 'st_blog',
        ],
        'st_blog_category' => [
            'name' => 'Blog category',
            'field_rewrite' => 'link_rewrite',
            'bo_controller' => 'StBlogCategory',
            'func' => 'getModuleLink',
            'route_id' => 'module-stblog-category',
            'id' => 'st_blog_category',
        ],
    ];

    public $selected_controller = [];

    public function __construct()
    {
        $this->name = 'tvseo';
        $this->tab = 'seo';
        $this->version = '1.0.0';
        $this->author = 'tivuno.com';
        $this->ps_versions_compliancy = [
            'min' => '1.7.0',
            'max' => _PS_VERSION_,
        ];
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PrestaShop SEO module ”Selefkos”');
        $this->description = $this->l('Climb the great wall of Google search engine results page (SERP)');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        //$this->urlConfig = new TvseoLink($this);
    }

    public function install(): bool
    {
        return parent::install();// && $this->registerHooks();
    }

    public function registerHooks(): bool
    {
        $hooks = [
            'actionDispatcher',
            'actionDispatcherBefore',
            'actionObjectCategoryUpdateBefore',
            'actionObjectProductUpdateBefore',
            'moduleRoutes',
        ];
        foreach ($hooks as $h) {
            $this->registerHook($h);
        }

        return true;
    }

    public function hookActionDispatcher($params)
    {
        $module = Module::getInstanceByName('ps_mainmenu');
        // Clear menu cache
        $module->hookActionCategoryUpdate($params);
        //var_dump($_GET);
        //var_dump($params);

        //echo Dispatcher::getInstance()->getController();

        if ($params['controller_type'] == 1 && !$params['is_module']) {
            $controller = Dispatcher::getInstance()->getController();

            if ($controller == 'index') {
                return;
            }

            $id_lang = (int) $this->context->shop->id;
            $rewrite = Tools::getValue('rewrite');
            //echo $rewrite;
            $table = $controller;
            $field = $this->controller_map[$controller]['field_rewrite'];
            if (in_array($controller, ['category', 'product'])) {
                if ($id = $this->getInstanceId($table, $rewrite, $field)) {
                    $_GET['id_' . $table] = $id;

                    return;
                }
            }
        }
    }

    public function hookActionDispatcherBefore($params)
    {
        return;
    }

    public function hookModuleRoutes($params)
    {
        $routers = [
            'module' => [
                'controller' => null,
                'rule' => 'module/{module}{/:controller}',
                'keywords' => [
                    'module' => ['regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'],
                    'controller' => ['regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'],
                ],
                'params' => [
                    'fc' => 'module',
                ],
            ],
            'category_rule' => [
                'controller' => 'category',
                'rule' => '{categories:/}{rewrite}/',
                'keywords' => [
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'rewrite'],
                    'meta_keywords' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'meta_title' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'id' => ['regexp' => '[0-9]+'],
                ],
                'params' => [
                ],
            ],
            'manufacturer_rule' => [
                'controller' => 'manufacturer',
                'rule' => 'm/{rewrite}',
                'keywords' => [
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'rewrite'],
                    'meta_keywords' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'meta_title' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'id' => ['regexp' => '[0-9]+'],
                ],
                'params' => [
                ],
            ],
            'module' => [
                'controller' => null,
                'rule' => 'module/{module}{/:controller}',
                'keywords' => [
                    'module' => ['regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'],
                    'controller' => ['regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'],
                ],
                'params' => [
                    'fc' => 'module',
                ],
            ],
            'product_rule' => [
                'controller' => 'product',
                'rule' => '{categories:/}{rewrite}',
                'keywords' => [
                    'id' => ['regexp' => '[0-9]+'],
                    'id_product_attribute' => ['regexp' => '[0-9]+'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*?', 'param' => 'rewrite'],
                    'ean13' => ['regexp' => '[0-9\pL]*'],
                    'category' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'categories' => ['regexp' => '[/_a-zA-Z0-9-\pL]*'],
                    'reference' => ['regexp' => '[a-zA-Z0-9\pL\pS-]*'],
                    'meta_keywords' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'meta_title' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'manufacturer' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'supplier' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'price' => ['regexp' => '[0-9\.,]*'],
                    'tags' => ['regexp' => '[a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                ],
            ],
            'layered_rule' => [
                'controller' => 'category',
                'rule' => '{rewrite}/{/:selected_filters}',
                'keywords' => [
                    'id' => ['regexp' => '[0-9]+'],
                    /* Selected filters is used by the module blocklayered */
                    'selected_filters' => ['regexp' => '.*', 'param' => 'selected_filters'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]+', 'param' => 'rewrite'],
                    'meta_keywords' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'meta_title' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                ],
            ],
        ];
        $default_routes = [
            'module-stblog-category' => [
                'controller' => 'category',
                'rule' => 'blog/{id_st_blog_category}-{rewrite}',
                'keywords' => [
                    'id_st_blog_category' => ['regexp' => '[0-9]+', 'param' => 'id_st_blog_category'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => 'stblog',
                ],
            ],
            'module-stblog-article' => [
                'controller' => 'article',
                'rule' => 'blog/{id_st_blog}_{rewrite}.html',
                'keywords' => [
                    'id_st_blog' => ['regexp' => '[0-9]+', 'param' => 'id_st_blog'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => 'stblog',
                ],
            ],
            'category_rule' => [
                'controller' => 'category',
                'rule' => '{id}-{rewrite}',
                'keywords' => [
                    'id' => ['regexp' => '[0-9]+', 'param' => 'id_category'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*'],
                    'meta_keywords' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'meta_title' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                ],
            ],
            'supplier_rule' => [
                'controller' => 'supplier',
                'rule' => '{id}__{rewrite}',
                'keywords' => [
                    'id' => ['regexp' => '[0-9]+', 'param' => 'id_supplier'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*'],
                    'meta_keywords' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'meta_title' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                ],
            ],
            'manufacturer_rule' => [
                'controller' => 'manufacturer',
                'rule' => '{id}_{rewrite}',
                'keywords' => [
                    'id' => ['regexp' => '[0-9]+', 'param' => 'id_manufacturer'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*'],
                    'meta_keywords' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'meta_title' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                ],
            ],
            'cms_rule' => [
                'controller' => 'cms',
                'rule' => 'content/{id}-{rewrite}',
                'keywords' => [
                    'id' => ['regexp' => '[0-9]+', 'param' => 'id_cms'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*'],
                    'meta_keywords' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'meta_title' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                ],
            ],
            'cms_category_rule' => [
                'controller' => 'cms',
                'rule' => 'content/category/{id}-{rewrite}',
                'keywords' => [
                    'id' => ['regexp' => '[0-9]+', 'param' => 'id_cms_category'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*'],
                    'meta_keywords' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'meta_title' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                ],
            ],
            'module' => [
                'controller' => null,
                'rule' => 'module/{module}{/:controller}',
                'keywords' => [
                    'module' => ['regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'],
                    'controller' => ['regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'],
                ],
                'params' => [
                    'fc' => 'module',
                ],
            ],
            'product_rule' => [
                'controller' => 'product',
                'rule' => '{category:/}{id}{-:id_product_attribute}-{rewrite}{-:ean13}.html',
                'keywords' => [
                    'id' => ['regexp' => '[0-9]+', 'param' => 'id_product'],
                    'id_product_attribute' => ['regexp' => '[0-9]+', 'param' => 'id_product_attribute'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'rewrite'],
                    'ean13' => ['regexp' => '[0-9\pL]*'],
                    'category' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'categories' => ['regexp' => '[/_a-zA-Z0-9-\pL]*'],
                    'reference' => ['regexp' => '[_a-zA-Z0-9-\pL\pS-]*'],
                    'meta_keywords' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'meta_title' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'manufacturer' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'supplier' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'price' => ['regexp' => '[0-9\.,]*'],
                    'tags' => ['regexp' => '[a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                ],
            ],
            /* Must be after the product and category rules in order to avoid conflict */
            'layered_rule' => [
                'controller' => 'category',
                'rule' => '{id}-{rewrite}{/:selected_filters}',
                'keywords' => [
                    'id' => ['regexp' => '[0-9]+', 'param' => 'id_category'],
                    /* Selected filters is used by the module blocklayered */
                    'selected_filters' => ['regexp' => '.*', 'param' => 'selected_filters'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*'],
                    'meta_keywords' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                    'meta_title' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                ],
            ],
        ];
        // To be compatible with the smartblog module.
        // If the smartblog module enaled, need to remove routers for our blog modules.
        if (Module::isInstalled('smartblog') && Module::isEnabled('smartblog')) {
            $smartblog = Module::getInstanceByName('smartblog');
            $sb_routes = $smartblog->hookModuleRoutes([]);
            $routers_new = [];
            if ($sb_routes) {
                foreach ($routers as $key => $route) {
                    if (strpos($key, 'module-stblog') !== false) {
                        continue;
                    }
                    $routers_new[$key] = $route;
                    if ($key == 'module') {
                        $routers_new = array_merge($routers_new, $sb_routes);
                    }
                }
            }
            $routers = $routers_new;
            unset($routers_new);
        }

        $routers['product_rule']['rule'] = '{categories:/}{rewrite}';
        $routers['product_rule']['keywords']['categories'] = ['regexp' => '[/_a-zA-Z0-9-\pL]*'];

        $routers['category_rule']['rule'] = 'c/{categories:/}{rewrite}/';
        $routers['category_rule']['keywords']['categories'] = ['regexp' => '[/_a-zA-Z0-9-\pL]*'];
        $routers['layered_rule']['rule'] = '{categories:/}{rewrite}/{/:selected_filters}';
        $routers['layered_rule']['keywords']['categories'] = ['regexp' => '[/_a-zA-Z0-9-\pL]*'];

        //if (Configuration::get($this->_prefix_st . 'ADD_REFERENCE')) {
        //$routers['product_rule']['keywords']['reference']['param'] = 'reference';
        //$routers['product_rule']['rule'] = str_replace('{rewrite}', '{rewrite}-p-{reference}',
        // $routers['product_rule']['rule']);
        //}
        // Change route via the selected pages.

        return $routers;
    }

    public function getControllerMap()
    {
        foreach ($this->controller_map as $k => $v) {
            // Add some filters here
            //if (Configuration::get($this->_prefix_st . strtoupper('advanced_' . $v['id']))) {
            $this->selected_controller[$k] = $v;
            //}
        }

        //var_dump($this->selected_controller);

        return $this->selected_controller;
    }

    public function getInstanceId($table, $rewrite, $field)
    {
        if (!$table || !$rewrite || !$field) {
            return false;
        }
        if (Tools::getValue('id_' . $table)) {
            return false;
        }
        $file_contents = [];
        if ($table == 'category') {
            $file = _PS_MODULE_DIR_ . $this->name . '/cache/categories.json';
            if (!is_file($file)) {
                // We create the cache
                $this->setCategoryJson($file, $file_contents, $rewrite);
            } else {
                // We update the cache
                $json = file_get_contents($file);
                $file_contents = json_decode($json, true);
                if (!isset($file_contents[$rewrite])) {
                    $this->setCategoryJson($file, $file_contents, $rewrite);
                }
            }
        } elseif ($table == 'product') {
            $file = _PS_MODULE_DIR_ . $this->name . '/cache/products.json';
            if (!is_file($file)) {
                // We create the cache
                $this->setProductJson($file, $file_contents, $rewrite);
            } else {
                // We update the cache
                $json = file_get_contents($file);
                $file_contents = json_decode($json, true);
                if (!isset($file_contents[$rewrite])) {
                    $this->setProductJson($file, $file_contents, $rewrite);
                }
            }
        }

        return $file_contents[$rewrite];
    }

    public function setCategoryJson($file, &$file_contents, $rewrite)
    {
        $q = 'SELECT DISTINCT c.`id_category`
            FROM `' . _DB_PREFIX_ . 'category_lang` cl
            LEFT JOIN `' . _DB_PREFIX_ . 'category` c
            ON (c.`id_category` = cl.`id_category`)
            WHERE `link_rewrite` = "' . pSQL($rewrite) . '"
            AND c.`active` = 1
            AND `id_lang` = ' . (int) $this->context->language->id;
        $file_contents[$rewrite] = Db::getInstance()->getValue($q);
        file_put_contents($file, json_encode($file_contents));
    }

    public function setProductJson($file, &$file_contents, $rewrite)
    {
        $q = 'SELECT DISTINCT p.`id_product`
            FROM `' . _DB_PREFIX_ . 'product_lang` pl
            LEFT JOIN `' . _DB_PREFIX_ . 'product` p
            ON (p.`id_product` = pl.`id_product`)
            WHERE `link_rewrite` = "' . pSQL($rewrite) . '"
            AND p.`active` = 1
            AND `id_lang` = ' . (int) $this->context->language->id;
        $file_contents[$rewrite] = Db::getInstance()->getValue($q);
        file_put_contents($file, json_encode($file_contents));
    }

    public function hookActionObjectCategoryUpdateBefore($params)
    {
        foreach (Language::getLanguages(false, false, true) as $id) {
            $params['object']->link_rewrite[$id] = TvseoLink::convert(
                $params['object']->name[$id],
                2
            );
        }
        //self::debug($params);
    }

    public function hookActionObjectProductUpdateBefore($params)
    {
        foreach (Language::getLanguages(false, false, true) as $id) {
            $params['object']->link_rewrite[$id] = TvseoLink::convert(
                $params['object']->name[$id],
                2
            );
        }
        //self::debug($params);
    }

    public static function debug($array = [])
    {
        print('<pre>' . print_r($array, true) . '</pre>');
        $hell();
    }
}
