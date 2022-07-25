<?php

namespace snec\SapEventTicketingLibrary;

class SapEtException extends \Exception
{
    private $errorCode;
    private $errorMessage;
    private $errorFields;

    /**
     * Exception constructor.
     * @param $errorCode
     * @param $errorMessage
     * @param $errorFields
     */
    public function __construct($errorCode, $errorMessage, $errorFields)
    {
        parent::__construct($errorMessage, $errorCode);

        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->errorFields = $errorFields;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param mixed $errorCode
     */
    public function setErrorCode($errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param mixed $errorMessage
     */
    public function setErrorMessage($errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return mixed
     */
    public function getErrorFields()
    {
        return $this->errorFields;
    }

    /**
     * @param mixed $errorFields
     */
    public function setErrorFields($errorFields): void
    {
        $this->errorFields = $errorFields;
    }

}