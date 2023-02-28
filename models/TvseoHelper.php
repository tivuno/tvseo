<?php

class TvseoHelper
{
    public static function endWith($string, $needle)
    {
        return preg_match("|{$needle}$|is", $string);
    }

    public static function startWith($string, $needle)
    {
        return preg_match("|^{$needle}|is", $string);
    }

    public static function contains($haystack, $needle)
    {
        if (strpos($haystack, $needle) !== false) {
            return true;
        }

        return false;
    }

    public static function getCurrentUrl()
    {
        return Tools::getShopDomainSsl(true) . $_SERVER['REQUEST_URI'];
    }

    public static function getResponseHeader($code)
    {
        $headers = [
            404 => 'HTTP/1.1 404 Not Found',
            301 => 'HTTP/1.1 301 Moved Permanently',
            302 => 'HTTP/1.1 302 Moved Temporarily'
        ];

        return $headers[$code];
    }

    public static function getIsset($param)
    {
        $value = Tools::getValue($param, null);
        if (!is_null($value)) {
            return true;
        }

        return false;
    }
}
