<?php

namespace RPI\Framework\View\Php;

class Pagination implements \RPI\Framework\Views\Php\IView
{
    public function render($model, \RPI\Framework\Controller $controller, array $options, $viewType)
    {
        $rendition = "";
        
        if (isset($model["pagination"])) {
            $pagination = $model["pagination"];

            $paginationDetails = "";
            if ($pagination["start"] != $pagination["end"]) {
                $paginationDetails = "Showing items ".($pagination["start"] + 1).
                    " to ".($pagination["end"] + 1);
            } else {
                $paginationDetails = "Showing item ".($pagination["end"] + 1);
            }
            $paginationDetails .= " of {$pagination["totalItems"]}";

            $rendition = <<<EOT
                <div class="pagination">
                    <p class="details">
                        $paginationDetails
                    </p>
                    <ul>
                        {$this->renderPreviousPage($pagination)}
                        {$this->renderPages($pagination)}
                        {$this->renderNextPage($pagination)}
                    </ul>
                </div>
EOT;
        }
        
        return $rendition;
    }
    
    protected function renderPreviousPage($pagination)
    {
        $rendition = "";
        
        if (isset($pagination["pages"]["previous"])) {
            $rendition = <<<EOT
                <li class="previous">
                    <a href="{$pagination["pages"]["previous"]["url"]}">
                        Previous
                    </a>
                </li>
EOT;
        }
        
        return $rendition;
    }
    
    protected function renderNextPage($pagination)
    {
        $rendition = "";
        
        if (isset($pagination["pages"]["next"])) {
            $rendition = <<<EOT
                <li class="next">
                    <a href="{$pagination["pages"]["next"]["url"]}">
                        Next
                    </a>
                </li>
EOT;
        }
        
        return $rendition;
    }
    
    protected function renderPages($pagination)
    {
        $rendition = "";
        
        $totalPages = count($pagination["pages"]["page"]);
        foreach ($pagination["pages"]["page"] as $index => $page) {
            $className = "p";
       
            if ($index == 0) {
                $className .= " f";
            }
            if ($index == $totalPages - 1) {
                $className .= " l";
            }
            
            if ($page["selected"]) {
                $rendition .= <<<EOT
                    <li class="$className">
                        <strong>{$page["number"]}</strong>
                    </li>
EOT;
            } else {
                $rendition .= <<<EOT
                    <li class="$className">
                        <a href="{$page["url"]}">
                            {$page["number"]}
                        </a>
                    </li>
EOT;
            }
        }
        
        return $rendition;
    }
}
