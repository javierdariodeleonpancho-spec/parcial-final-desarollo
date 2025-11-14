<?php

declare(strict_types=1);

if (!extension_loaded('soap')) {
    throw new RuntimeException('La extensión SOAP debe estar habilitada para utilizar NuSOAP.');
}

if (!class_exists('nusoap_client')) {
    /**
     * Minimal adapter that exposes the most common NuSOAP classes but delegates the heavy
     * lifting to PHP's built-in SOAP extension. The goal is to provide a drop-in replacement
     * for the NuSOAP client API used in the academic exercises without depending on the
     * legacy library.
     */
    class nusoap_client
    {
        /** @var SoapClient */
        private SoapClient $client;

        /** @var array|null */
        public $fault = null;

        /** @var string|null */
        private ?string $error = null;

        /**
         * @param string $wsdl       WSDL URL or path when $wsdl_mode is true.
         * @param bool   $wsdl_mode  When true, the first parameter is treated as WSDL.
         * @param string $proxyhost  Ignored, kept for compatibility.
         * @param string $proxyport  Ignored, kept for compatibility.
         * @param string $proxyusername Ignored.
         * @param string $proxypassword Ignored.
         * @param int    $timeout    Ignored.
         * @param int    $response_timeout Ignored.
         * @param array  $curl_options Ignored.
         */
        public function __construct(
            string $wsdl,
            bool $wsdl_mode = true,
            $proxyhost = false,
            $proxyport = false,
            $proxyusername = false,
            $proxypassword = false,
            $timeout = 0,
            $response_timeout = 30,
            $curl_options = null,
            $use_curl = false
        ) {
            $options = [
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ];

            if ($wsdl_mode) {
                $this->client = new SoapClient($wsdl, $options);
            } else {
                if (!isset($curl_options['location']) && !isset($options['location'])) {
                    throw new InvalidArgumentException('Debe especificarse la URL de destino cuando no se usa WSDL.');
                }

                $location = $curl_options['location'] ?? $options['location'];
                unset($curl_options['location'], $options['location']);

                $nonWsdlOptions = array_merge(
                    $options,
                    $curl_options ?? [],
                    [
                        'location' => $location,
                        'uri' => $curl_options['uri'] ?? ($options['uri'] ?? 'http://tempuri.org')
                    ]
                );

                $this->client = new SoapClient(null, $nonWsdlOptions);
            }
        }

        /**
         * Sends a SOAP request and returns the decoded result as an associative array.
         *
         * @param string $method
         * @param array  $params
         * @return mixed
         */
        public function call(string $method, array $params = [])
        {
            try {
                $this->fault = null;
                $this->error = null;

                $response = $this->client->__soapCall($method, [$params]);

                return json_decode(json_encode($response, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
            } catch (SoapFault $fault) {
                $this->fault = [
                    'faultcode' => $fault->faultcode,
                    'faultstring' => $fault->getMessage(),
                ];
                $this->error = $fault->getMessage();

                return null;
            } catch (JsonException $jsonException) {
                $this->fault = null;
                $this->error = 'Error al interpretar la respuesta del servicio: ' . $jsonException->getMessage();

                return null;
            }
        }

        /**
         * Returns the last error message (if any).
         */
        public function getError(): ?string
        {
            return $this->error;
        }
    }
}

if (!class_exists('nusoap_server')) {
    /**
     * Minimal server that simply proxies to the built-in SoapServer implementation.
     */
    class nusoap_server
    {
        /** @var SoapServer */
        private SoapServer $server;

        /**
         * Creates a new server instance.
         *
         * @param string|null $wsdl
         * @param array       $options
         */
        public function __construct($wsdl = null, array $options = [])
        {
            if ($wsdl !== null && !is_file($wsdl) && filter_var($wsdl, FILTER_VALIDATE_URL) === false) {
                throw new InvalidArgumentException('El archivo WSDL proporcionado no existe.');
            }

            $defaultOptions = [
                'trace' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'exceptions' => true,
            ];

            $this->server = new SoapServer($wsdl, array_merge($defaultOptions, $options));
        }

        /**
         * Registers a PHP class whose public methods are exposed as SOAP operations.
         *
         * @param string $className
         * @param mixed  ...$ctorArgs
         */
        public function setClass(string $className, ...$ctorArgs): void
        {
            $this->server->setClass($className, ...$ctorArgs);
        }

        /**
         * Adds one or more standalone functions to the SOAP server.
         *
         * @param callable|array $functions
         */
        public function addFunction($functions): void
        {
            $this->server->addFunction($functions);
        }

        /**
         * Handles the incoming SOAP request.
         *
         * @param string|null $request
         */
        public function service(?string $request = null): void
        {
            $this->server->handle($request);
        }
    }
}
