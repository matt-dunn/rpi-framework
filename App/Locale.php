<?php

namespace RPI\Framework\App;

// TODO: additional localization work required:
//			1. implement gettext for ad-hoc localization strings
//			2. provide an XSL extension for gettext so that XSL can get localized strings

/**
 * Locale support
 */
class Locale
{
    private function __construct()
    {
    }

    private static $locale = null;
    private static $dateFormat = "dd-mm-yyyy";

    /**
     * Initialise localisation
     */
    public static function init()
    {
        self::setTimezone("Europe/London");

        // TODO: get available locales from the http head or querystring
        // (via rewrite) and try to match to available locales - need to somehow
        // know of a default...
        //$locales = ContentService::getInstance()->getAvailableLocales();
        //RPI_Framework_App_Locale::$locale = $locales[0];

        // 1. get available locales from the service layer
        // 2. get the available language(s) from the HTTP head
        // 3. attempt to match language/sub-language
        // 4. if match, set locale to match
        // 5. if no match, set locale to the default (possibly from the web.config?)

        self::setLocale("EN");
    }

    /**
     * Return an array of all languages in the browser accept header in order of 
     * quality. The default language will always be in the list.
     * @return <type>
     */
    // TODO: also check the querystring and cookie for local setting - this
    // should take priority over HTTP_ACCEPT_LANGUAGE
    public static function getAcceptLanguages()
    {
        static $languages = false;
        static $HTTP_ACCEPT_LANGUAGE = null;

        $langAccept = null;
        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            $langAccept = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
        }

        if ($languages === false || $langAccept !== $HTTP_ACCEPT_LANGUAGE) {
            $languages = array();
            $languagePrototypes = array();
            $HTTP_ACCEPT_LANGUAGE = null;

            if (isset($langAccept)) {
                $HTTP_ACCEPT_LANGUAGE = $_SERVER["HTTP_ACCEPT_LANGUAGE"];

                $locales = explode(",", $langAccept);
                foreach ($locales as $locale) {
                    $localeParts = explode(";", $locale);
                    $quality = "1";
                    if (count($localeParts) > 1) {
                        $quality = substr($localeParts[1], 2);
                    }
                    $language = strtoupper($localeParts[0]);
                    $languages[$language] = $quality;

                    $languageParts = explode("-", $language);
                    if (count($languageParts) > 1) {
                        if (!isset($languagePrototypes[$languageParts[0]])
                            || (isset($languagePrototypes[$languageParts[0]])
                            && $quality < $languagePrototypes[$languageParts[0]])) {
                            $languagePrototypes[$languageParts[0]] = $quality - 0.01;
                        }
                    }
                }
            }
            $languages = $languages + $languagePrototypes;
            arsort($languages);
            // Make sure the default set language is the final option in the list...
            $languages[self::getLocale()] = true;
            $languages = array_keys($languages);
        }

        return $languages;
    }

    /**
     * Get the default locale
     * @return <type> Get the default locale
     */
    public static function getLocale()
    {
        return self::$locale;
    }

    /**
     * Set the default locale
     * @param <type> $locale
     */
    public static function setLocale($locale)
    {
        self::$locale = strtoupper($locale);

        // setlocale(LC_ALL, 'en_US.UTF-8');
        setlocale(LC_ALL, str_replace("-", "_", $locale));

        // TODO: windows locale support?
        // TODO: allow configuration of the time zone
        //putenv ("TZ=GMT");
        // TODO: workout what locale string to use depending on OS...? need to
        // be able to convert the locale from the browser into something
        // suitable...
        // setlocale(LC_ALL, "en_GB", "en-US", "english", "english-gb"); // WINDOWS then UNIX LOCALE STRINGS
    }

    public static function setTimezone($timezone)
    {
        date_default_timezone_set($timezone);
    }

    public static function getTimezone()
    {
        return date_default_timezone_get();
    }

    public static function setDateFormat($format)
    {
        self::$dateFormat = $format;
    }

    public static function getDateFormat()
    {
        return self::$dateFormat;
    }
}
