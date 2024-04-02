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

    protected function loadRoutes($id_shop = null)
    {
        $context = Context::getContext();

        if (isset($context->shop) && $id_shop === null) {
            $id_shop = (int) $context->shop->id;
        }

        $language_ids = Language::getIDs();

        if (isset($context->language) && !in_array($context->language->id, $language_ids)) {
            $language_ids[] = (int) $context->language->id;
        }

        foreach ($language_ids as $language_id) {
            // Load custom routes from modules
            $modules_routes = Hook::exec(
                'moduleRoutes',
                [
                    'id_lang' => $language_id,
                    'id_shop' => $id_shop,
                ],
                null,
                true,
                false
            );

            if (is_array($modules_routes) && count($modules_routes)) {
                foreach ($modules_routes as $module_route) {
                    if (is_array($module_route) && count($module_route)) {
                        foreach ($module_route as $route => $route_details) {
                            if (array_key_exists('controller', $route_details)
                                && array_key_exists('rule', $route_details)
                                && array_key_exists('keywords', $route_details)
                                && array_key_exists('params', $route_details)
                            ) {
                                if (!isset($this->default_routes[$route])) {
                                    $this->default_routes[$route] = [];
                                }
                                $this->default_routes[$route] = array_merge(
                                    $this->default_routes[$route],
                                    $route_details
                                );
                            }
                        }
                    }
                }
            }

            // Set default routes
            foreach ($this->default_routes as $id => $route) {
                $route = $this->computeRoute(
                    $route['rule'],
                    $route['controller'],
                    $route['keywords'],
                    $route['params'] ?? []
                );

                // We assign the routes to the respective language
                $this->routes[$id_shop][$language_id][$id] = $route;
            }

            // Load the custom routes prior the defaults to avoid infinite loops
            if ($this->use_routes) {
                // Load routes from meta table
                $sql = 'SELECT m.page, ml.url_rewrite, ml.id_lang
					FROM `' . _DB_PREFIX_ . 'meta` m
                    LEFT JOIN `' . _DB_PREFIX_ . 'meta_lang` ml ON (m.id_meta = ml.id_meta' .
                    Shop::addSqlRestrictionOnLang('ml', (int) $id_shop) . ')
					ORDER BY LENGTH(ml.url_rewrite) DESC';
                if ($results = Db::getInstance()->executeS($sql)) {
                    foreach ($results as $row) {
                        if ($row['url_rewrite']) {
                            $this->addRoute(
                                $row['page'],
                                $row['url_rewrite'],
                                $row['page'],
                                $row['id_lang'],
                                [],
                                [],
                                $id_shop
                            );
                        }
                    }
                }

                // Set default empty route if no empty route (that's weird I know)
                if (!$this->empty_route) {
                    $this->empty_route = [
                        'routeID' => 'index',
                        'rule' => '',
                        'controller' => 'index',
                    ];
                }

                // Load custom routes
                foreach ($this->default_routes as $route_id => $route_data) {
                    if ($custom_route = Configuration::get('PS_ROUTE_' . $route_id, null, null, $id_shop)) {
                        if (isset($context->language) && !in_array($context->language->id, $language_ids)) {
                            $language_ids[] = (int) $context->language->id;
                        }

                        $route = $this->computeRoute(
                            $custom_route,
                            $route_data['controller'],
                            $route_data['keywords'],
                            $route_data['params'] ?? []
                        );

                        // We assign the routes to the respective language
                        $this->routes[$id_shop][$language_id][$route_id] = $route;
                    }
                }
            }
        }
    }
}
