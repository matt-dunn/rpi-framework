<?php

namespace RPI\Framework\App\DomainObjects;

interface ILocale
{
    /**
     * Get the default locale
     * 
     * @return string Get the default locale
     */
    public function get();
    
    /**
     * Set the default locale
     * 
     * @param string $locale
     * 
     * @return boolean
     */
    public function set($locale);
    
    /**
     * 
     * @param string $timezone
     * 
     * @return boolean
     */
    public function setTimezone($timezone);
    
    /**
     * 
     * @return string
     */
    public function getTimezone();
    
    /**
     * 
     * @param string $format
     */
    public function setDateFormat($format);
    
    /**
     * 
     * @return string
     */
    public function getDateFormat();
}
