<?php

namespace RPI\Framework\App;

// TODO: additional localization work required:
//			1. implement gettext for ad-hoc localization strings
//			2. provide an XSL extension for gettext so that XSL can get localized strings

/**
 * Locale support
 */
class Locale implements \RPI\Framework\App\DomainObjects\ILocale
{
    private $locale = null;
    private $dateFormat = null;

    /**
     * 
     * @param string $timeZone
     * @param string $defaultLocale
     * @param string $dateFormat
     */
    public function __construct($timeZone, $defaultLocale = null, $dateFormat = null)
    {
        $this->setTimezone($timeZone);

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

        if (!isset($defaultLocale)) {
            $this->set("EN");
        } else {
            $this->set($defaultLocale);
        }

        if (!isset($dateFormat)) {
            $this->setDateFormat("dd-mm-yyyy");
        } else {
            $this->setDateFormat($dateFormat);
        }
    }

    /**
     * Get the default locale
     * 
     * @return string Get the default locale
     */
    public function get()
    {
        return $this->locale;
    }

    /**
     * Set the default locale
     * 
     * @param string $locale
     * 
     * @return boolean
     */
    public function set($locale)
    {
        $this->locale = strtoupper($locale);

        // setlocale(LC_ALL, 'en_US.UTF-8');
        setlocale(LC_ALL, str_replace("-", "_", $locale));

        // TODO: windows locale support?
        // TODO: allow configuration of the time zone
        //putenv ("TZ=GMT");
        // TODO: workout what locale string to use depending on OS...? need to
        // be able to convert the locale from the browser into something
        // suitable...
        // setlocale(LC_ALL, "en_GB", "en-US", "english", "english-gb"); // WINDOWS then UNIX LOCALE STRINGS
        
        return true;
    }

    /**
     * 
     * @param string $timezone
     * 
     * @return boolean
     */
    public function setTimezone($timezone)
    {
        date_default_timezone_set($timezone);
        
        return true;
    }

    /**
     * 
     * @return string
     */
    public function getTimezone()
    {
        return date_default_timezone_get();
    }

    /**
     * 
     * @param string $format
     */
    public function setDateFormat($format)
    {
        $this->dateFormat = $format;
    }

    /**
     * 
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }
}
