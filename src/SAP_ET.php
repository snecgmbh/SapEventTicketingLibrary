<?php

namespace snec\SapEventTicketingLibrary;

use Milo\XmlRpc\Converter;
use Milo\XmlRpc\MethodCall;
use Milo\XmlRpc\MethodResponse;

/**
 * Class for SAP EventTicketing XMLRPC API
 */
class SAP_ET
{
    private static $API_ENDPOINT = "/api/xmlrpc";

    private $etXmlrpcApiUrl;


    private $xmlSessionId = null;

    private $etHost;

    /**
     * Should a Exception be thrown if an error occurs?
     *
     * @var bool
     */
    private $throwExceptionOnFailure = true;

    /**
     * @param $etHost
     * @param $xmlSessionId
     */
    public function __construct($etHost, $xmlSessionId = null)
    {
        $this->etHost = $etHost;
        $this->xmlSessionId = $xmlSessionId;
        $this->etXmlrpcApiUrl = (str_starts_with(strtolower($etHost), "http") ? ""  : "https://") . $etHost . SAP_ET::$API_ENDPOINT;
    }


    /**
     * @param $name
     * @param $phpArguments
     * @return array|string
     * @throws SapEtException
     */
    public function __call($name, $phpArguments)
    {
        $arguments = [];

        if(isset($phpArguments[0]) && is_array($phpArguments[0]))
        {
            foreach ($phpArguments[0] as $key => $value) {
                $arguments[$key] = $value;
            }
        }


        if($this->xmlSessionId != null && !isset($arguments["sessionid"]))
            $arguments["sessionid"] = $this->xmlSessionId;

        $converter = new Converter();

        $call = new MethodCall($name, [$arguments]);


        $ch = curl_init($this->etXmlrpcApiUrl);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $converter->toXml($call));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Sadly some certificates are not recognized by windows...
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


        $xml = curl_exec($ch);

        curl_close($ch);

        if($xml===false)
        {
            throw new SapEtException(-1, 'Curl Error '.curl_errno($ch)." ".curl_error($ch), []);
        }





        $response = $converter->fromXml($xml);
        if (!$response instanceof MethodResponse) {
            throw new SapEtException(-1, 'Internal technical XMLRPC Error', []);
        }



        $xmlResult = $response->getReturnValue();

        if($this->throwExceptionOnFailure && $xmlResult["errorcode"]!="0")
        {
            throw new SapEtException($xmlResult["errorcode"], $xmlResult["errormessage"], $xmlResult["errorfields"]);
        }

        return $xmlResult;
    }

    /**
     * @param $sessionid
     * @return void
     */
    public function setSession($sessionid)
    {
        $this->xmlSessionId = $sessionid;
    }

    /**
     * @return mixed|null
     */
    public function getSession()
    {
        return $this->xmlSessionId;
    }

    /**
     * @param $company
     * @param $user
     * @param $password
     * @return bool
     */
    public function login($company, $user, $password)
    {
        $result = $this->tickets_session_init(["company" => $company, "user" => $user, "password" => $password, "encrypted" => "n", "languageid" => 0]);
        $this->xmlSessionId = $result["sessionid"];

        return true;
    }

    /**
     * @return void
     */
    public function logout()
    {
        $this->tickets_session_close();
    }

    /**
     * @return mixed
     */
    public function getEtHost()
    {
        return $this->etHost;
    }

    /**
     * @return bool
     */
    public function isThrowExceptionOnFailure(): bool
    {
        return $this->throwExceptionOnFailure;
    }

    /**
     * @param bool $throwExceptionOnFailure
     */
    public function setThrowExceptionOnFailure(bool $throwExceptionOnFailure): void
    {
        $this->throwExceptionOnFailure = $throwExceptionOnFailure;
    }





}