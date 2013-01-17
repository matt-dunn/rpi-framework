<?php

namespace RPI\Framework\WebService;

/**
 * Web service base class
 */
abstract class Server extends \RPI\Framework\Controller
{
    protected $firephp;
    
    protected $context;
    
    private $response;

    /**
     * This option can be used to always return any exception error message. Care must be taken when setting to true
     * as it can potentially be a security risk if detailed error messages are sent to a client. Only set to true if
     * for example the webservice is being called from behind a firewall.
     *
     * Set to true by default if running in debug mode.
     *
     * @var type boolean
     */
    private $alwaysIncludeExceptionMessage = false;

    protected function initController(array $options)
    {
        if (\RPI\Framework\App\Config::getValue("config/debug/@enabled", false) === true) {
            $this->alwaysIncludeExceptionMessage = true;
        }

        if (isset($_SERVER["HTTP_REFERER"])) {
            $_SERVER["REDIRECT_URL"] = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH);
        }
    }

    /**
     * Initialise the web service and invoke service method
     */
    protected function init()
    {
    }

    public function process()
    {
        ob_start();

        $this->processAction();
        
        $request = null;

        try {
            try {
                $startTime = microtime(true);

                $request = $this->getRequest(file_get_contents("php://input"), $_GET);
                $this->context = new Context($request->timestamp, $request->method->format);

                $response = $this->callMethod($request);
                $response->executionTime = (microtime(true) - $startTime) * 1000;

                header("Execution-Time:".$response->executionTime, true);

                $this->response = $response;
            } catch (\Exception $ex) {
                \RPI\Framework\Exception\Handler::log($ex);

                ob_clean();

                if (!isset($request)) {
                    $request = new Request();
                }
                $format = "xml";	// Default the format if it's unavailable (e.g. if the request was invalid)
                if (isset($_SERVER["CONTENT_TYPE"])) {
                    $contentType = explode(";", $_SERVER["CONTENT_TYPE"]);
                    if (count($contentType) > 0) {
                        $mimeType = explode("/", strtolower($contentType[0]));
                        if (count($mimeType) > 0) {
                            $format = $mimeType[1];
                        }
                    }
                }
                if (isset($request->method)) {
                    $format = $request->method->format;
                }
                $response = new Response($request, ResponseStatus::ERROR, $format);
                unset($response->result);

                if ($ex instanceof \RPI\Framework\WebService\Exceptions\WebService) {
                    $response->error =
                        (object)(array(
                            "code" => $ex->getCode(),
                            "type" => get_class($ex),
                            "message" => $ex->getLocalizedMessage()
                        ));
                    header("HTTP/1.1 ".$ex->httpCode, true);
                } elseif ($ex instanceof \RPI\Framework\Exceptions\InvalidParameter
                    || $this->alwaysIncludeExceptionMessage) {
                    $response->error =
                        (object)(array("code" => -32602, "type" => get_class($ex), "message" => $ex->getMessage()));
                    header("HTTP/1.1 500", true);
                } else {
                    $response->error =
                        (object)(array("code" => -32400, "type" => get_class($ex), "message" => "Server error"));
                    header("HTTP/1.1 500", true);
                }
                
                $this->response = $response;
            }
        } catch (\Exception $ex) {
            header("HTTP/1.1 500", true);
        }
    }
    
    /**
     * Render the response
     * @param Response $response
     */
    public function render()
    {
        $buffer = ob_get_clean();
        
        if (\RPI\Framework\App\Config::getValue("config/debug/@enabled", false) === true
            && $buffer !== false
            && $buffer != ""
            && isset($GLOBALS["RPI_FRAMEWORK_FIREPHP"])) {
            $GLOBALS["RPI_FRAMEWORK_FIREPHP"]->log($buffer, "Output buffer");
        }

        $contentType = "application_{$this->response->format}";
        $className = "\\RPI\Framework\\WebService\\Handler\\".\RPI\Framework\Helpers\Utils::toCamelCase($contentType);

        if (class_exists($className)) {
            // charset=".mb_detect_encoding($responseString, "UTF-8, ISO-8859-1"));
            header("Content-type: application/{$this->response->format}; charset=UTF-8", true);

            $params = $this->response->params;
            unset($this->response->params);
            
            return call_user_func_array($className."::render", array($this->response, $params));
        } else {
            header("Content-type: text/plain", true);
            if (isset($this->response->data)) {
                echo $this->response->data;
            }
            
            if ($buffer !== false) {
                echo $buffer;
            }
        }
    }

    
    
    private function callMethod(Request $requestData)
    {
        if (isset($requestData->method)
            && $requestData->method->name
            && method_exists($this, $requestData->method->name)) {
            $data = null;
            try {
                if (isset($requestData->method->params)) {
                    $params = $requestData->method->params;
                } else {
                    $params = array();
                }

                // Check to see if the method has enough params passed - this avoids the general
                // RuntimeException exception...
                $reflect = new \ReflectionMethod($this, $requestData->method->name);
                $paramCount = 0;
                foreach ($reflect->getParameters() as $param) {
                    if (!$param->isDefaultValueAvailable()) {
                        $paramCount++;
                    }
                }

                if (count($params) < $paramCount) {
                    throw new \RPI\Framework\WebService\Exceptions\Method(
                        "Missing argument $paramCount for ".$requestData->method->name
                    );
                }

                $data = call_user_func_array(array($this, $requestData->method->name), $params);
            } catch (\RPI\Framework\Exceptions\Authentication $ex) {
                throw new \RPI\Framework\WebService\Exceptions\Authentication();
            } catch (\RPI\Framework\Exceptions\Authorization $ex) {
                throw new \RPI\Framework\WebService\Exceptions\Authorization();
            } catch (\RPI\Framework\Exceptions\Forbidden $ex) {
                throw new \RPI\Framework\WebService\Exceptions\Forbidden();
            } catch (\Exception $ex) {
                throw $ex;
            }

            if (!isset($data)) {
                // If the webservice method does not return anything then create a new ResponseMethod
                $data = new ResponseMethod();
            } elseif (!$data instanceof ResponseMethod) {
                // Wrap the webservice method result if not type of ResponseMethod
                $data = new ResponseMethod($data);
            }

            if ($data->format != $requestData->method->format) {
                // Attempt to convert the response data into the requested format...
                // TODO: call a convert method on the handler for this to support any handler format
                try {
                    if ($data->format == "json" && $requestData->method->format == "xml") {
                        //require_once(__DIR__."/../../Vendor/PEAR/XML/Serializer.php");
                        //$serializer = new \XML_Serializer(
                        //    array(
                        //        "addDecl" => false,
                        //        "defaultTagName" => "value"
                        //        XML_SERIALIZER_OPTION_TYPEHINTS => true
                        //    )
                        //);
                        //$status = $serializer->serialize($data->data);
                        //$data->data = $serializer->getSerializedData();
                        
                        $data->format = $requestData->method->format;
                    } elseif ($data->format == "xml" && $requestData->method->format == "json") {
                        require_once(__DIR__."/../../Vendor/PEAR/XML/Unserializer.php");
                        $serializer = new \XML_Unserializer(
                            array(
                                "addDecl" => false,
                                "defaultTagName" => "value",
                                XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE => true
                            )
                        );
                        
                        if ($serializer->unserialize($data->data) !== false) {
                            $data->data = json_encode($serializer->getUnserializedData());
                            $data->format = $requestData->method->format;
                        } else {
                            throw new \Exception("Unable to unserialize XML to JSON");
                        }
                    } else {
                        throw new \RPI\Framework\WebService\Exceptions\UnsupportedFormat();
                    }
                } catch (\Exception $ex) {
                    throw new \RPI\Framework\WebService\Exceptions\UnsupportedFormat($ex);
                }
            }

            return new Response($requestData, ResponseStatus::SUCCESS, $data->format, $data->data, $data->params);
        } else {
            throw new \RPI\Framework\WebService\Exceptions\MissingMethod($requestData);
        }
    }
    
    /**
     * Process the request
     * @param string $content Request body
     * @param array  $request Request GET parameters
     */
    private function getRequest($content, $request)
    {
        $contentType = null;
        if (isset($_SERVER["CONTENT_TYPE"])) {
            $contentType = explode(";", $_SERVER["CONTENT_TYPE"]);
            $contentType = strtolower(str_replace("/", "_", $contentType[0]));
        }

        $className = "\\RPI\Framework\\WebService\\Handler\\".\RPI\Framework\Helpers\Utils::toCamelCase($contentType);

        if (class_exists($className)) {
            return call_user_func_array($className."::getRequest", array($content, $request));
        } else {
            // If there is content sent in the request and no handler throw an exception
            if (isset($content) && strlen($content) > 0) {
                throw new \RPI\Framework\WebService\Exceptions\InvalidMimeType(str_replace("_", "/", $contentType));
            }

            $requestData = new Request();
            if (isset($request["timestamp"])) {
                $requestData->timestamp = $request["timestamp"];
            }

            $method = null;
            $params = null;

            if (isset($request["method"])) {
                $method = $request["method"];
            }

            if (isset($request["format"])) {
                $format = $request["format"];
            } else {
                $format = null;
            }

            if (isset($request["params"])) {
                $params = explode(",", $request["params"]);
            } else {
                $params = null;
            }

            $requestData->method = new RequestMethod($method, $format, $params);
            return $requestData;
        }
    }
}
