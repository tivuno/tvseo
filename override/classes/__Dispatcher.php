<?php
/**
 * PrestaShop SEO module ”Selefkos”
 * @author    tivuno.com
 * @copyright 2018 - 2023 © tivuno.com
 * @license   https://tivuno.com/blog/business/basic-license
 */

//require_once _PS_MODULE_DIR_ . 'tvseo/models/TvseoLink.php';

class Dispatcher extends DispatcherCore
{
    protected function __construct()
    {
        if (Module::isInstalled('tvseo')) {
            $module_inst = Module::getInstanceByName('tvseo');
            $this->default_routes = $module_inst->hookModuleRoutes([]);
        }
        parent::__construct();
    }

    public function setController($controller = null)
    {
        $this->controller = $controller;
    }

    public function setFrontController($mode = self::FC_MODULE)
    {
        $this->front_controller = $mode;
    }

    public function createUrl(
        $route_id,
        $id_lang = null,
        array $params = [],
        $force_routes = false,
        $anchor = '',
        $id_shop = null
    )
    {
        if (Module::isInstalled('sturls') && Configuration::get('ST_URL_REMOVE_ANCHOR')) {
            $anchor = '';
        }

        return parent::createUrl($route_id, $id_lang, $params, $force_routes, $anchor, $id_shop);
    }

    protected function loadRoutes($id_shop = null)
    {
        if ($id_shop === null) {
            $id_shop = (int) Context::getContext()->shop->id;
        }
        parent::loadRoutes($id_shop);
        if (Module::isInstalled('tvseo')) {
            // Multi lang support
            $module = Module::getInstanceByName('tvseo');
            $language_ids = Language::getIDs(true, $id_shop);
            foreach ($language_ids as $id_lang) {
                foreach ($module->lang_field as $k => $v) {
                    $route_lang = Configuration::get($module->_prefix_st . strtoupper($k), $id_lang);
                    if ($route_lang && $v != $route_lang) {
                        foreach ($this->default_routes as $route_id => $route) {
                            if (strpos($route['rule'], $v . '/') !== false || $route['rule'] == $v) {
                                if ($route['rule'] == $v) {
                                    $rule = str_replace($v, $route_lang, $route['rule']);
                                } else {
                                    $rule = str_replace($v . '/', $route_lang . '/', $route['rule']);
                                }

                                $this->addRoute(
                                    $route_id,
                                    $rule,
                                    $route['controller'],
                                    $id_lang,
                                    $route['keywords'],
                                    isset($route['params']) ? $route['params'] : [],
                                    $id_shop
                                );
                            }
                        }
                    }
                }
            }
            //if (Configuration::get($module->_prefix_st . 'ADVANCED')) {
            // Add default route to prevent infinite loop.
            if ($this->empty_route) {
                $this->addRoute(
                    $this->empty_route['routeID'],
                    $this->empty_route['rule'],
                    $this->empty_route['controller'],
                    Context::getContext()->language->id,
                    [],
                    [],
                    $id_shop
                );
            }
            // Move proudct_rule and category_rule to queue end.
            foreach ($this->routes[$id_shop] as &$routes) {
                foreach ($module->getControllerMap() as $c => $v) {
                    if (!key_exists($v['route_id'], $routes)) {
                        continue;
                    }
                    $rule = $routes[$v['route_id']];
                    unset($routes[$v['route_id']]);
                    $routes[$v['route_id']] = $rule;
                }
                if (key_exists('layered_rule', $routes)) {
                    $layered_rule = $routes['layered_rule'];
                    unset($routes['layered_rule']);
                    $routes['layered_rule'] = $layered_rule;
                }
            }
            //}
        }
    }
}
