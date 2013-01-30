<?php

namespace RPI\Framework\WebService;

/**
 * Web service base class
 */
abstract class Server extends \RPI\Framework\Controller
{
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
    
    private $clientEvents = array();

    protected function initController(array $options)
    {
        ob_start();
        
        if ($this->getConfig()->getValue("config/debug/@enabled", false) === true) {
            $this->alwaysIncludeExceptionMessage = true;
        }

        // Attempt to get the document location of the request:
        $documentLocation = $this->app->getRequest()->getHeaders()->get("Document-Location");
        if (!isset($documentLocation) && isset($_SERVER["HTTP_REFERER"])) {
            $documentLocation = $_SERVER["HTTP_REFERER"];
        }
        if (isset($documentLocation) && \RPI\Framework\Helpers\HTTP::isValidUrl($documentLocation)) {
            $this->app->getRequest()->setUrl($documentLocation);
            
            // Slightly nasty 'hack' to ensure any code requesting the current url gets the document location
            // of the service call:
            $_SERVER["REQUEST_URI"] = $documentLocation;
            $_SERVER["REDIRECT_URL"] = parse_url($documentLocation, PHP_URL_PATH);
        }
        
        \RPI\Framework\Event\Manager::addEventListener(
            "RPI\Framework\Event\IClientEvent",
            array($this, "clientEvent")
        );
    }
    
    public function clientEvent(\RPI\Framework\Event $event, $params)
    {
        unset($event->target);
        
        $this->clientEvents[] = array(
            "event" => $event,
            "params" => $params
        );
    }

    /**
     * Initialise the web service and invoke service method
     */
    protected function init()
    {
    }

    public function process()
    {
        $contentType = $this->getApp()->getRequest()->getContentType();
        $format = $contentType["contenttype"]["subtype"];

        try {
            $request = null;
            
            try {
                $startTime = microtime(true);

                $request = $this->getRequest(
                    $this->app->getRequest()->getBody(),
                    $contentType["contenttype"]["mimetype"]
                );
                
                $this->context = new Context($request->timestamp, $request->method->format);

                $action = $this->getAction();
                if (!isset($request->method->name) && isset($action)) {
                    $request->method->name = $action->method;
                    $request->method->params = $action->params;
                } else {
                    $this->processAction();
                }
        
                $this->response = $this->callMethod($request);
                
                $this->response->executionTime = (microtime(true) - $startTime) * 1000;
                $this->app->getResponse()->getHeaders()->set("Execution-Time", $this->response->executionTime);
            } catch (\Exception $ex) {
                \RPI\Framework\Exception\Handler::log($ex);

                ob_clean();

                if (!isset($request)) {
                    $request = new Request();
                }
                $response = new Response($request, ResponseStatus::ERROR, $format);
                unset($response->result);

                // If the format is not supported (e.g. form multi-part post) then revert back to the default
                $className = "\\RPI\Framework\\WebService\\Handler\\Application{$response->format}";
                if (!class_exists($className)) {
                    $response->format = "json";

                }
                
                if ($ex instanceof \RPI\Framework\WebService\Exceptions\WebService) {
                    $response->error = new \RPI\Framework\WebService\Error(
                        $ex->getCode(),
                        get_class($ex),
                        $ex->getLocalizedMessage()
                    );
                    
                    $this->app->getResponse()->setStatusCode($ex->httpCode);
                } elseif ($ex instanceof \RPI\Framework\Exceptions\InvalidParameter) {
                    $response->error = new \RPI\Framework\WebService\Error(
                        -32602,
                        get_class($ex),
                        $ex->getMessage()
                    );

                    $this->app->getResponse()->setStatusCode(500);
                } else {
                    $message = "Server error";
                    if ($this->alwaysIncludeExceptionMessage) {
                        $message = $ex->getMessage();
                    }
                    $response->error = new \RPI\Framework\WebService\Error(
                        -32200,
                        get_class($ex),
                        $message
                    );

                    $this->app->getResponse()->setStatusCode(500);
                }
                
                $this->response = $response;
            }
        } catch (\Exception $ex) {
            $this->app->getResponse()->setStatusCode(500);
        }
    }
    
    /**
     * Render the response
     * @param Response $response
     */
    public function render()
    {
        try {
            $this->app->getResponse()->setMimeType("application/{$this->response->format}");

            if (count($this->clientEvents) > 0) {
                $this->response->events = $this->clientEvents;
            }

            $buffer = ob_get_clean();

            if ($this->getConfig()->getValue("config/debug/@enabled", false) === true) {
                if ($buffer !== false && $buffer != "") {
                    $this->app->getDebug()->log($buffer, "Output buffer");
                }
            }

            return $this->response->render();
        } catch (\Exception $ex) {
            \RPI\Framework\Exception\Handler::log($ex);
            $this->app->getResponse()->setStatusCode(500);
        }
    }

    
    
    /**
     * 
     * @param \RPI\Framework\WebService\Request $request
     * 
     * @return \RPI\Framework\WebService\Response
     * 
     * @throws \RPI\Framework\WebService\Exceptions\Authentication
     * @throws \RPI\Framework\WebService\Exceptions\Authorization
     * @throws \RPI\Framework\WebService\Exceptions\Forbidden
     * @throws \RPI\Framework\WebService\Authentication
     * @throws \RPI\Framework\WebService\Exceptions\Method
     * @throws \RPI\Framework\WebService\Exceptions\UnsupportedFormat
     * @throws \Exception
     * @throws \RPI\Framework\WebService\Exceptions\MissingMethod
     */
    private function callMethod(Request $request)
    {
        if (!isset($request->method)) {
            $request->method = "defaultMethod";
        }
        
        if (method_exists($this, $request->method->name)) {
            $responseMethod = null;
            
            try {
                if (isset($request->method->params)) {
                    $params = $request->method->params;
                } else {
                    $params = array();
                }

                // Check to see if the method has enough params passed - this avoids the general
                // RuntimeException exception...
                $reflect = new \ReflectionMethod($this, $request->method->name);
                $paramCount = 0;
                foreach ($reflect->getParameters() as $param) {
                    if (!$param->isDefaultValueAvailable()) {
                        $paramCount++;
                    }
                }

                if (count($params) < $paramCount) {
                    throw new \RPI\Framework\WebService\Exceptions\Method(
                        "Missing argument $paramCount for ".$request->method->name
                    );
                }

                $responseMethod = call_user_func_array(array($this, $request->method->name), $params);
            } catch (\RPI\Framework\Exceptions\Authentication $ex) {
                throw new \RPI\Framework\WebService\Exceptions\Authentication();
            } catch (\RPI\Framework\Exceptions\Authorization $ex) {
                throw new \RPI\Framework\WebService\Exceptions\Authorization();
            } catch (\RPI\Framework\Exceptions\Forbidden $ex) {
                throw new \RPI\Framework\WebService\Exceptions\Forbidden();
            } catch (\Exception $ex) {
                throw $ex;
            }

            if (!isset($responseMethod)) {
                // If the webservice method does not return anything then create a new ResponseMethod
                $responseMethod = new ResponseMethod();
            } elseif (!$responseMethod instanceof ResponseMethod) {
                // Wrap the webservice method result if not type of ResponseMethod
                $responseMethod = new ResponseMethod($responseMethod);
            }

            if ($responseMethod->format != $request->method->format) {
                // TODO: add conversion methods to the handlers to allow conversion
                throw new \RPI\Framework\WebService\Exceptions\UnsupportedFormat(
                    "'{$responseMethod->format}' cannot be converted to '{$request->method->format}'."
                );
            }

            return new Response(
                $request,
                ResponseStatus::SUCCESS,
                $responseMethod->format,
                $responseMethod->data,
                $responseMethod->params
            );
        } else {
            throw new \RPI\Framework\WebService\Exceptions\MissingMethod($request);
        }
    }
    
    /**
     * Process the request
     * @param string $content Request body
     * 
     * @return \RPI\Framework\WebService\Request
     */
    private function getRequest($content, $mimetype)
    {
        $className = "\\RPI\Framework\\WebService\\Handler\\".\RPI\Framework\Helpers\Utils::toCamelCase(
            strtolower(str_replace(array("/", "-"), "_", $mimetype))
        );

        if (class_exists($className)) {
            $handler = new $className();
            return $handler->getRequest($content);
        } else {
            throw new \RPI\Framework\WebService\Exceptions\InvalidMimeType($mimetype);
        }
    }
}
