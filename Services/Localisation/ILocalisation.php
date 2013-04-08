<?php

namespace RPI\Framework\Services\Localisation;

/**
 * Localization services
 */
interface ILocalisation
{
    /**
     * Return a list of available locales
     * @return array Simple array list of locale codes as defined by xml:lang
     */
    public function getAvailableLocales();

    /**
     * Return the applied locale
     * @param bool $refresh - Force the locale to be recalculated
     */
    public function getLocale($refresh = false);

    /**
     * Return localized text
     * @param string $id
     */
    public function getText($id, $args = null);

    /**
     * Alias of getText
     * @param string $locale
     */
    public function t($id, $args = null);
}
