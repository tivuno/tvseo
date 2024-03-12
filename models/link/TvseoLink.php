<?php
class TvseoLink
{
    protected $routes = [];

    //private TvseoLinkCategory $category;

    public function __construct($module)
    {

        //$this->module = $module;
        //$this->general = new ArSeoProURLGeneral($module, null, $this);
        //$this->product = new ArSeoProURLProduct($module, null, $this);
        $this->category = new TvseoLinkCategory($module);

        //$this->context = Context::getContext();
    }

    public function dispatcherLoadRoutes($routes, $dispatcher = null)
    {
        $context = Context::getContext();
        //echo rand();
        $language_ids = Language::getLanguages(false, false, true);

        if (Tools::isCallable([$dispatcher, 'getRoutes'])) {
            $routes = $dispatcher->getRoutes();

            foreach ($routes as $id_shop => $shop_routes) {
                foreach ($shop_routes as $id_lang => $lang_routes) {
                    foreach ($lang_routes as $route_name => $one_lang_routes) {
                        if (in_array($route_name, [
                            'product_rule',
                            'category_rule',
                            'layered_rule',
                            'manufacturer_rule',
                            'supplier_rule',
                            'cms_rule',
                            'cms_category_rule',
                        ])) {
                            $route_data = $dispatcher->default_routes[$route_name];
                            $route_data['rule'] = $one_lang_routes['rule'];

                            if (TvseoHelper::endWith(trim($route_data['rule']), '/')) {
                                $route_data['rule'] = Tools::substr($route_data['rule'], 0, -1);
                                $dispatcher->addRoute(
                                    $route_name . '_2',
                                    $route_data['rule'],
                                    $route_data['controller'],
                                    $id_lang,
                                    $route_data['keywords'],
                                    isset($route_data['params']) ? $route_data['params'] : [],
                                    $id_shop
                                );
                                $this->addRoute($route_name . '_2');
                            }
                        }

                        if (in_array($route_name, [
                            'product_rule',
                            'category_rule',
                            'layered_rule',
                            'manufacturer_rule',
                            'supplier_rule',
                            'cms_rule',
                            'cms_category_rule',
                        ])) {
                            $route_data = $dispatcher->default_routes[$route_name];
                            $route_data['rule'] = $one_lang_routes['rule'];

                            if (TvseoHelper::endWith(trim($route_data['rule']), '}')) {
                                if (!$dispatcher->hasKeyword($route_name, $id_lang, 'categories', $id_shop)) {
                                    $route_data['rule'] = $route_data['rule'] . '/';
                                    $dispatcher->addRoute(
                                        $route_name . '_2',
                                        $route_data['rule'],
                                        $route_data['controller'],
                                        $id_lang,
                                        $route_data['keywords'],
                                        isset($route_data['params']) ? $route_data['params'] : [],
                                        $id_shop
                                    );
                                    $this->addRoute($route_name . '_2');
                                }
                            }
                        }
                    }
                }
            }

            $routes = $dispatcher->getRoutes();

            //var_dump($routes);

            foreach ($routes as $id_shop => $shop_routes) {
                foreach ($shop_routes as $id_lang => $lang_routes) {
                    foreach ($lang_routes as $route_id => $one_lang_routes) {
                        $module = null;
                        if (isset($one_lang_routes['params']['ars_pre_dispatcher_module']) &&
                            $one_lang_routes['params']['ars_pre_dispatcher_module']) {
                            $module = $one_lang_routes['params']['ars_pre_dispatcher_module'];
                            unset($routes[$id_shop][$id_lang][$route_id]['params']['ars_pre_dispatcher_module']);
                        }

                        $function = null;
                        if (isset($one_lang_routes['params']['ars_pre_dispatcher_function']) &&
                            $one_lang_routes['params']['ars_pre_dispatcher_function']) {
                            $function = $one_lang_routes['params']['ars_pre_dispatcher_function'];
                            unset($routes[$id_shop][$id_lang][$route_id]['params']['ars_pre_dispatcher_function']);
                        }

                        if ($module && $function) {
                            $this->addPreDispatcher($route_id, $module, $function);
                            $this->addRoute($route_id);
                        }
                    }
                }
            }
        }

        $id_shop = (int) $context->shop->id;
        foreach ($language_ids as $id_lang) {
            $tmp = [];
            if (isset($routes[$id_shop]) && isset($routes[$id_shop][$id_lang])) {
                if ($route_name = Configuration::get('ARS_ROUTE_FRONT')) {
                    $tmp[$route_name] = $routes[$id_shop][$id_lang][$route_name];
                    unset($routes[$id_shop][$id_lang][$route_name]);
                }
                foreach ($routes[$id_shop][$id_lang] as $route_name => $route) {
                    if (!TvseoHelper::startWith(trim($route['rule']), '{')) {
                        $tmp[$route_name] = $route;
                        unset($routes[$id_shop][$id_lang][$route_name]);
                    }
                }

                $routes[$id_shop][$id_lang] = $tmp + $routes[$id_shop][$id_lang];

                if (Configuration::get('ARS_MODULE_ROUTE_END')) {
                    $route = $routes[$id_shop][$id_lang]['module'];
                    unset($routes[$id_shop][$id_lang]['module']);
                    $routes[$id_shop][$id_lang]['module'] = $route;
                }
            }
        }

        return $routes;
    }

    public function preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop)
    {
        $return = $this->getEmptyPreDispatcherResponse();

        $dispatcher = Dispatcher::getInstance();
        if (!Tools::isCallable([$dispatcher, 'getRoutes']) ||
            !Tools::isCallable([$dispatcher, 'getRequestUri'])) {
            return $return;
        }

        switch ($route_id) {
            case 'product_rule':
            case 'product_rule_2':
                $return = $this->product->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;

            case 'category_rule':
            case 'category_rule_2':
            case 'layered_rule':
            case 'layered_rule_2':
                $return = $this->category->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;

            case 'manufacturer_rule':
            case 'manufacturer_rule_2':
                $return = $this->manufacturer->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;

            case 'supplier_rule':
            case 'supplier_rule_2':
                $return = $this->supplier->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;

            case 'cms_rule':
            case 'cms_rule_2':
                $return = $this->cms->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;

            case 'cms_category_rule':
            case 'cms_category_rule_2':
                $return = $this->cmsCategory->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;
        }

        return $return;
    }

    public function addRoute($id)
    {
        if (!$this->isRouteExists($id)) {
            $this->routes[] = $id;
        }
    }

    public function isRouteExists($id)
    {
        return in_array($id, $this->routes);
    }

    protected static $basic = [
        '/[ἀἁἈἉᾶἄἅἌἍἆἇἎἏἂἃἊἋᾳᾼᾴᾲᾀᾈᾁᾉᾷᾆᾎᾇᾏᾂᾊᾃᾋὰαάΑΆᾄᾅᾌᾍᾺᾰᾱᾸᾹ]/u' => 'a',
        '/[βΒ]/u' => 'v',
        '/[γΓ]/u' => 'g',
        '/[δΔ]/u' => 'd',
        '/[ἐἑἘἙἔἕἜἝἒἓἚἛὲεέΕΈ]/u' => 'e',
        '/[ζΖ]/u' => 'z',
        '/[ἠἡἨἩἤἥἬἭῆἦἧἮἯἢἣἪἫῃῌῄῂᾐᾑᾘᾙᾖᾗᾞᾟᾒᾚᾛὴηήΗΉᾓᾔᾕῇᾜᾝῊ]/u' => 'i',
        '/[θΘ]/u' => 'th',
        '/[ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻῒῗὶιίϊΐΙΊΪΐῐῑῚῘῙ]/u' => 'i',
        '/[κΚ]/u' => 'k',
        '/[λΛ]/u' => 'l',
        '/[μΜ]/u' => 'm',
        '/[νΝ]/u' => 'n',
        '/[ξΞ]/u' => 'x',
        '/[ὀὁὈὉὄὅὌὍὂὃὊὋὸοόΟΌῸ]/u' => 'o',
        '/[πΠ]/u' => 'p',
        '/[ρΡ]/u' => 'r',
        '/[σςΣ]/u' => 's',
        '/[τΤ]/u' => 't',
        '/[ὐὑὙὔὕὝῦὖὗὒὓὛὺῒῧυύϋΰΥΎΫῢΰῠῡὟῪῨῩ]/u' => 'y',
        '/[φΦ]/iu' => 'f',
        '/[χΧ]/u' => 'ch',
        '/[ψΨ]/u' => 'ps',
        '/[ὠὡὨὩὤὥὬὭῶὦὧὮὯὢὣὪὫῳῼᾠᾡᾨᾩᾤᾥᾬᾭᾦᾧᾮᾯᾢᾣᾪᾫὼωώῲῷῴ]/iu' => 'o',
    ];
    protected static $diphthongs = [
        '/[αΑ][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u' => 'ai',
        '/[οΟ][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u' => 'oi',
        '/[Εε][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u' => 'ei',
        '/[αΑ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'af$1',
        '/[αΑ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]/u' => 'av',
        '/[εΕ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'ef$1',
        '/[εΕ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]/u' => 'ev',
        '/[οΟ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]/u' => 'ou',
        '/(^|\s)[μΜ][πΠ]/u' => '$1b',
        '/[μΜ][πΠ](\s|$)/u' => 'b$1',
        '/[μΜ][πΠ]/u' => 'b',
        '/[νΝ][τΤ]/u' => 'nt',
        '/[τΤ][σΣ]/u' => 'ts',
        '/[τΤ][ζΖ]/u' => 'tz',
        '/[γΓ][γΓ]/u' => 'ng',
        '/[γΓ][κΚ]/u' => 'gk',
        '/[ηΗ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυΥ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'if$1',
        '/[ηΗ][υΥ]/u' => 'iu',
    ];
    protected static $simplified = [
        'before' => [
            // Diphthongs
            '/[αΑ][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u' => 'e',
            '/[οΟ][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u' => 'i',
            '/[Εε][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u' => 'i',
            '/[αΑ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'af$1',
            '/[αΑ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]/u' => 'av',
            '/[εΕ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'ef$1',
            '/[εΕ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]/u' => 'ev',
            '/[οΟ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]/u' => 'ou',
            '/(^|\s)[μΜ][πΠ]/u' => '$1b',
            '/[μΜ][πΠ](\s|$)/u' => 'b$1',
            '/[μΜ][πΠ]/u' => 'b',
            '/[νΝ][τΤ]/u' => 'nt',
            '/[τΤ][σΣ]/u' => 'ts',
            '/[τΤ][ζΖ]/u' => 'tz',
            '/[γΓ][γΓ]/u' => 'ng',
            '/[γΓ][κΚ]/u' => 'gk',
            '/[ηΗ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυΥ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'if$1',
            '/[ηΗ][υΥ]/u' => 'iu',
        ],
        'after' => [
            // Regular letters
            '/[ὐὑὙὔὕὝῦὖὗὒὓὛὺῒῧυύϋΰΥΎΫῢΰῠῡὟῪῨῩ]/u' => 'i',
        ],
    ];

    /**
     * @param string $string
     * @param int $level
     * @param bool $slug
     * @param bool $uppercase
     * @return string
     */
    public static function convert(string $string, int $level = 0, bool $slug = true, bool $uppercase = false,): string
    {
        if ($level == 0) {
            $expressions = self::$basic;
        } elseif ($level == 1) {
            $expressions = array_merge(self::$diphthongs, self::$basic);
        } else {
            $rules = self::$simplified;
            $expressions = array_merge($rules['before'], self::$basic, $rules['after']);
        }
        $string = preg_replace(array_keys($expressions), array_values($expressions), $string);
        if ($uppercase === true) {
            $string = mb_strtoupper($string, 'UTF-8');
        } else {
            $string = mb_strtolower($string, 'UTF-8');
        }
        if ($slug === true) {
            return self::toSlug($string);
        }

        return $string;
    }

    /**
     * @param string $string
     * @return string
     */
    public static function toSlug(string $string): string
    {
        $string = preg_replace('/[^\p{L}\p{N}\s]/u', '', $string);
        $string = preg_replace('/[\s-]+/', ' ', $string);

        return preg_replace('/[\s_]/', '-', $string);;
    }
}
