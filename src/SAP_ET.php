<?php

namespace snec\SapEventTicketingLibrary;

use PhpXmlRpc\Client;
use PhpXmlRpc\Encoder;
use PhpXmlRpc\Helper\XMLParser;
use PhpXmlRpc\Request;
use PhpXmlRpc\Value;

/**
 * Class for SAP EventTicketing XMLRPC API
 */
class SAP_ET
{
    private static $API_ENDPOINT = "/api/xmlrpc";

    private $etXmlrpcApiUrl;

    /** @var Client */
    private $xmlrpc;

    private $xmlSessionId = null;

    private $etHost;

    /**
     * @param $etHost
     * @param $xmlSessionId
     */
    public function __construct($etHost, $xmlSessionId = null)
    {
        $this->etHost = $etHost;
        $this->xmlSessionId = $xmlSessionId;
        $this->etXmlrpcApiUrl = "https://" . $etHost . SAP_ET::$API_ENDPOINT;
        $this->xmlrpc = new Client($this->etXmlrpcApiUrl);

        $this->xmlrpc->setOption(Client::OPT_RETURN_TYPE, XMLParser::RETURN_PHP);
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


        if($this->xmlSessionId != null)
            $arguments["sessionid"] = $this->xmlSessionId;

        $encoder = new Encoder();
        $request = new Request($name, new Value($encoder->encode($arguments)));

        $response = $this->xmlrpc->send($request);

        $xmlResult = $response->value();

        if($xmlResult===0)
        {
            // Es gab einen HTTP Fehler, wir müssen es manuell interpretieren
            $temp = explode("\n", $response->httpResponse()["raw_data"]);
            $raw = array_pop($temp);
            $xmlResult = $encoder->decode($encoder->decodeXml($raw)->value());
        }


        if($xmlResult["errorcode"]!="0")
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



}