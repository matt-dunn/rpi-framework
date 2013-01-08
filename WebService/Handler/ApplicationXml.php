<?php

namespace RPI\Framework\WebService\Handler;

/**
 * Handle XML requests and responses
 */
class ApplicationXml implements \RPI\Framework\WebService\Handler\IHandler
{
    public static function getRequest($content, $request)
    {
        $requestData = null;
        try {
            $serializer = new \XML_Unserializer(
                array(
                    "addDecl" => false,
                    "rootName" => "request",
                    "defaultTagName" => "item",
                    XML_SERIALIZER_OPTION_TYPEHINTS => true
                )
            );
            $status = $serializer->unserialize($content);
            $request = $serializer->getUnserializedData();
            $requestData = new \RPI\Framework\WebService\Request((object) array("request" =>$request));
        } catch (\Exception $ex) {
            throw new \RPI\Framework\WebService\Exceptions\InvalidRequest($content);
        }

        return $requestData;

        // TODO: deserialize xml into request object (currently using restful qs)
        //$requestData = new Request();
        //if (isset($request["timestamp"])) {
        //    $requestData->timestamp = $request["timestamp"];
        //}
        //$method = null;
        //$params = $request;
        //
        //if (isset($request["method"])) {
        //    $method = $request["method"];
        //}
        //
        //$requestData->method = new RequestMethod($method, "xml", array($params));
        //return $requestData;
    }

    public static function render(\RPI\Framework\WebService\Response $response, array $params = null)
    {
        $data = \RPI\Framework\Helpers\Dom::serializeToDom(
            $response,
            array(
                "addDecl" => false,
                "rootName" => "response",
                "defaultTagName" => "item"
            )
        );

        if ($params != null && isset($params["xsltFilename"])) {//$this->xsltFilename != null) {
            $xsl = new \RPI\Framework\Views\Xsl\View($params["xsltFilename"]);
            // TODO: this call will not currently work as $data is not the correct type...
            $xsl->render($data, "php://output");
        } else {
            echo $data->saveXml();
        }
    }
}
