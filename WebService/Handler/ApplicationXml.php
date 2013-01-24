<?php

namespace RPI\Framework\WebService\Handler;

/**
 * Handle XML requests and responses
 */
class ApplicationXml implements \RPI\Framework\WebService\Handler\IHandler
{
    public function getRequest($content)
    {
        $request = null;
        try {
            if (isset($content) && $content !== "") {
                $serializer = new \XML_Unserializer(
                    array(
                        "addDecl" => false,
                        "rootName" => "request",
                        "defaultTagName" => "item",
                        XML_SERIALIZER_OPTION_TYPEHINTS => true
                    )
                );
                if ($serializer->unserialize($content) === true) {
                    $data = (object)$serializer->getUnserializedData();
                
                    $request = new \RPI\Framework\WebService\Request(
                        $data->request->timestamp,
                        new \RPI\Framework\WebService\RequestMethod(
                            $data->request->method->name,
                            $data->request->method->format,
                            (isset($data->request->method->params) ? (array)$data->request->method->params : null)
                        ),
                        (isset($data->request->id) ? $data->request->id : null)
                    );
                }
            }
        } catch (\Exception $ex) {
            throw new \RPI\Framework\WebService\Exceptions\InvalidRequest($content, $ex);
        }

        return $request;
    }

    public function render(\RPI\Framework\WebService\Response $response, array $params = null)
    {
        $data = \RPI\Framework\Helpers\Dom::serializeToDom(
            $response,
            array(
                "addDecl" => false,
                "rootName" => "response",
                "defaultTagName" => "item"
            )
        );

        if ($params != null && isset($params["xsltFilename"])) {
            $xsl = new \RPI\Framework\Views\Xsl\View($params["xsltFilename"]);
            return $xsl->render($data);
        } else {
            return $data->saveXml();
        }
    }
}
