<?php

namespace RPI\Framework\Helpers;

/**
 * HTML helpers
 * @author Matt Dunn
 */
class HTML
{
    private function __construct()
    {
    }

    public static function getSafeClassName($name)
    {
        return strtolower(str_replace(array("/", "\\", " ", "."), "-", $name));
    }
    
    /**
     * Sanitise HTML content
     * @param string $htmlSource
     * @return string
     */
    public static function sanitise($htmlSource)
    {
        $data = new \DOMDocument();
        $data->loadXML("<body xmlns=\"http://www.w3.org/1999/xhtml\">".$htmlSource."</body>");

        if (class_exists("xsltCache")) {
            $xp = new \xsltCache();
            $xp->importStyleSheet(__DIR__."/HTML/Xhtml.xsl");
        } else {
            $xp = new \XsltProcessor();
            $doc = new \DOMDocument();
            $doc->load(__DIR__."/HTML/Xhtml.xsl");
            $xp->importStylesheet($doc);
        }

        return $xp->transformToXML($data);
    }

    /**
     * Format a plain text string to XHTML
     * @param  string $text      String to convert
     * @param  string $namespace Optional XML namespace prefix
     * @return string XHTML string
     *
     * Anchors and emails are converted to anchors
     */
    public static function convertFromText($text, $namespace = null)
    {
        $lines = explode("\n", $text);
        $result = "";
        if (isset($namespace)) {
            $namespace = " xmlns=\"$namespace\"";
        }

        $re_host = "([a-z\d]*[a-z\d]\.)+[a-z][-a-z\d]*[a-z]";
        $re_port = "(:\d{1,})?";
        $re_path = "(\/[^)\]!?<>\#\"\s]+)?\/?";
        // $re_query = "(\?[^<>\#\"\s]+)?";

        $count = count($lines);
        for ($i = 0; $i < $count; $i++) {
            if (strlen(trim($lines[$i])) > 0) {
                $line = htmlspecialchars(trim($lines[$i]));

                $replacedLinks = preg_replace(
                    "#((ht|f)tps?:\/\/{$re_host}{$re_port}{$re_path})#i",
                    "<a href=\"$1\"$namespace>$1</a>",
                    $line
                );
                if ($replacedLinks != null) {
                    $line = $replacedLinks;
                }

                $replacedLinks = preg_replace(
                    "#([a-z\d]+@[a-z\d]+\.[a-z\d]+)#i",
                    "<a href=\"mailto:$1\"$namespace>$1</a>",
                    $line
                );
                if ($replacedLinks != null) {
                    $line = $replacedLinks;
                }

                $result .= "<p$namespace>".$line."</p>";
            }
        }

        return $result;
    }
}
