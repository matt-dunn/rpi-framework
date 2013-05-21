<?php

namespace RPI\Framework\WebService;

// TODO: this need to use the handlers for mime types
class Client
{
    private $serviceUrl;
    private $requestFormat;
    private $responseFormat;
    private $idenfificationToken;
    private $authenticationToken;

    public $lastResponse;

    public function __construct($serviceUrl, $responseFormat = "json", $requestFormat = "json")
    {
        $this->serviceUrl = $serviceUrl;
        $this->requestFormat = $requestFormat;
        $this->responseFormat = $responseFormat;
    }

    public function authenticate($idenfificationToken, $authenticationToken)
    {
        $this->idenfificationToken = $idenfificationToken;
        $this->authenticationToken = $authenticationToken;
    }

    public function __call($name, $arguments)
    {
        return $this->invoke($name, $arguments);
    }

    private function invoke($method, array $params)
    {
        $ch = false;

        try {
            $data = null;
            
            $requestMethod = new RequestMethod($method, $this->responseFormat, $params);
            $request = new Request();
            $request->method = $requestMethod;

            if ($this->requestFormat == "json") {
                $data = json_encode(array("request" =>$request));
            }

            if (isset($data)) {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $this->serviceUrl);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_COOKIESESSION, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                if (isset($this->authenticationToken)) {
                    curl_setopt($ch, CURLOPT_COOKIE, $this->authenticationToken);
                    curl_setopt(
                        $ch,
                        CURLOPT_COOKIE,
                        "a=".urlencode($this->authenticationToken).";u=".urlencode($this->idenfificationToken)
                    );
                }

                curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    array(
                        "Content-Type: application/".$this->requestFormat."; charset=utf-8"
                    )
                );

                $response = curl_exec($ch);

                curl_close($ch);
                $ch = false;

                $responseData = false;
                $status = false;
                $exception = false;
                $data = false;

                if ($this->responseFormat == "json") {
                    $responseData = json_decode($response);
                    if (isset($responseData->status)) {
                        $status = $responseData->status;
                    } else {
                        $status = ResponseStatus::SUCCESS;
                    }
                    if (isset($responseData->exception)) {
                        $exception = $responseData->exception;
                    }
                    $data = $responseData->result;
                }

                if ($status !== ResponseStatus::SUCCESS) {
                    if (isset($exception) && $exception!== false && $exception != "" && class_exists($exception)) {
                        // TODO: only throw an exception like this if there are no parameters for the constructor
                        throw new $exception;
                    } else {
                        throw new \RPI\Framework\WebService\Exceptions\Method();
                    }
                }

                $this->lastResponse = $responseData;

                return $data;
            } else {
                throw new \RPI\Framework\WebService\Exceptions\Generic("Unsupported mime type");
            }
        } catch (\Exception $ex) {
            if ($ch !== false) {
                curl_close($ch);
            }
            throw $ex;
        }
    }
}
