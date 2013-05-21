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
     * 
     * @param \RPI\HTTP\IRequest $request
     * @param int $totalCount
     * @param int $itemsPerPage
     * @param int $offset
     * @param int $maxPages
     * @param array $parameters
     * 
     * @return array
     */
    public static function getPaginationData(
        \RPI\HTTP\IRequest $request,
        $totalCount,
        $itemsPerPage,
        $offset,
        $maxPages = null
    ) {
        $data = null;
        
        if ($itemsPerPage > 0 && $totalCount > $itemsPerPage) {
            if (!isset($maxPages)) {
                $maxPages = 10;
            } elseif ($maxPages < 5) {
                $maxPages = 5;
            }

            $url = $request->getUrlPath();
            $querystring = "";
            $parameters = $request->getParameters();
            
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

            // Safely remove the 'page' qs value:
            if (isset($parameters)) {
                foreach ($parameters as $name => $value) {
                    if (strtolower($name) !== "page" && strtolower($name) !== "id" && strtolower($name) !== "type") {
                        $querystring .= "&".$name."=".urlencode($value);
                    }
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
