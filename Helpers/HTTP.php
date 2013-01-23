<?php

namespace RPI\Framework\Helpers;

class HTTP
{
    public static function parseContentType($contentType)
    {
        $mimeType = null;
        $charset = null;
        
        $contentTypeParts = explode(";", $contentType);
        if (count($contentTypeParts) > 0) {
            $mimeType = trim(strtolower($contentTypeParts[0]));
            if ($mimeType == "") {
                $mimeType = null;
            }
            $contentTypeParts = array_slice($contentTypeParts, 1);
            $parameter = array();
            foreach ($contentTypeParts as $contentTypePart) {
                $contentTypePartDetails = explode("=", $contentTypePart);
                $name = trim($contentTypePartDetails[0]);
                $value = null;
                if (count($contentTypePartDetails) > 1) {
                    $value = $contentTypePartDetails[1];
                }
                
                if (strtolower($name) == "charset") {
                    $charset = $value;
                } else {
                    $parameter[$name] = $value;
                }
            }
        }

        
        $mimeTypeDetails = array(
            "mimetype" => $mimeType
        );
        
        if (isset($mimeType)) {
            $mimeTypeParts = explode("/", strtolower($mimeType));
            $mimeTypeDetails["type"] = $mimeTypeParts[0];
            if (count($mimeTypeParts) > 1) {
                $mimeTypeDetails["subtype"] = $mimeTypeParts[1];
            }
        }
        
        return array(
            "contenttype" => $mimeTypeDetails,
            "charset" => $charset,
            "parameters" => $parameter
        );
    }
}
