<?php

namespace RPI\Framework\Helpers;

/**
 * Pagination helpers
 * @author Matt Dunn
 */
class Pagination
{
    private function __construct()
    {
    }

    /**
     * Insert pagination XML into a DOMDocument
     * @param integer $totalCount   Total number of items
     * @param integer $itemsPerPage Number of items per page
     * @param integer $offset       Zero based offset of first item
     * @param void
     */
    public static function getPaginationData($totalCount, $itemsPerPage, $offset, $maxPages = 10)
    {
        $data = null;

        if ($itemsPerPage > 0 && $totalCount > $itemsPerPage) {
            $data = array();
            $data["totalItems"] = $totalCount;
            $data["itemsPerPage"] = $itemsPerPage;
            $data["offset"] = $offset;
            $data["start"] = $offset;
            $end = ($offset + $itemsPerPage > $totalCount ? $totalCount - 1 : $offset + $itemsPerPage - 1);
            $data["end"] = $end;
            $page = ($offset / $itemsPerPage) + 1;
            $totalPages = ceil($totalCount / $itemsPerPage);
            $data["totalPages"] = $totalPages;

            if (isset($_SERVER["REQUEST_URI"])) {
                $url = preg_replace("/page-[0-9]{1,}\//", "", parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
            }

            $querystring = "";

            // Safely remove the 'page' qs value:
            foreach ($_GET as $name => $value) {
                if (strtolower($name) !== "page" && strtolower($name) !== "id" && strtolower($name) !== "type") {
                    $querystring .= "&".$name."=".urlencode($value);
                }
            }

            if (strlen($querystring) > 0) {
                if (substr($querystring, 0, 1) == "&") {
                    $querystring = substr($querystring, 1);
                }
                $querystring = "?".$querystring;
            }

            $data["pages"] = array();

            if ($page > 1) {
                $pageUrl = $url."page-".($page - 1)."/".$querystring;
                $data["pages"]["previous"] = array("number" => $page - 1, "url" => $pageUrl);
            }

            if ($page < $totalPages) {
                $pageUrl = $url."page-".($page + 1)."/".$querystring;
                $data["pages"]["next"] = array("number" => $page + 1, "url" => $pageUrl);
            }

            if ($maxPages < 5) {
                $maxPages = 5;
            }
            $startPage = (int) ($page - 1 - (($maxPages / 2) - 1));
            if ($startPage + $maxPages > $totalPages) {
                $startPage = $totalPages - $maxPages;
            }
            if ($startPage < 0) {
                $startPage = 0;
            }
            $endPage = $startPage + $maxPages;
            if ($endPage > $totalPages) {
                $endPage = $totalPages;
            }
            $data["startPage"] = $startPage;
            $data["endPage"] = $endPage;
            $data["maxPages"] = $maxPages;

            $data["pages"]["page"] = array();
            for ($i = $startPage; $i < $endPage; $i++) {
                $pageUrl = $url."page-".($i + 1)."/".$querystring;
                $data["pages"]["page"][] = array("number" => $i + 1, "url" => $pageUrl, "selected" => $page == $i + 1);
            }
        }

        return $data;
    }
}
