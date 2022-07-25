<?php

namespace snec\SapEventTicketingLibrary;

class XMLRPC_Client
{
    /** @var string XMLRPC Endpoint */
    private $endpoint;

    function __construct( $endpoint ) {
        $this->endpoint = $endpoint;
    }

    /**
     * Call the XML-RPC method named $method and return the results, or die trying!
     *
     * @param string $method XML-RPC method name
     * @param mixed ... optional variable list of parameters to pass to XML-RPC call
     *
     * @return array result of XML-RPC call
     */
    public function call() {
        $params = func_get_args();
        $method = array_shift( $params );

        $post = xmlrpc_encode_request( $method, $params );

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL,            $this->url );
        curl_setopt( $ch, CURLOPT_POST,           true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS,     $post );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);


        // Support for Proxy (non-k8s ET Env)
        if(isset($GLOBALS['global_http_proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $GLOBALS['global_http_proxy']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $GLOBALS['global_http_proxyport']);
        }

        $response = curl_exec( $ch );
        $response_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $curl_errorno = curl_errno( $ch );
        $curl_error   = curl_error( $ch );
        curl_close( $ch );

        if ( $curl_errorno != 0 ) {
            throw new \RuntimeException("CURL-Error: " . $curl_errorno." - ".$curl_error);
        }

        return self::convert_from_latin1_to_utf8_recursively(xmlrpc_decode( $response ));

    }
    /**
     * Encode array from latin1 to utf8 recursively
     *
     * Thanks to stackoverflow https://stackoverflow.com/questions/31115982/malformed-utf-8-characters-possibly-incorrectly-encoded-in-laravel
     *
     * @param $dat
     * @return array|string
     */
    public static function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);

            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

            return $dat;
        } else {
            return $dat;
        }
    }
}