<?php
namespace Itembase\Psdk\Http;

/**
 * Class Response
 *
 * Class represent response for itembase server request. Response class should not be treated like framework-quality.
 * It's only to represent response data for itembase server.
 *
 * @package       Itembase\Psdk\Http
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class Response
{
    /** @var array $body */
    protected $body;

    /** @var string $type */
    protected $type;

    /** @var string $version */
    protected $version;

    /** @var string $isRedirect */
    protected $isRedirect;

    /**
     * Constructor accept response type and version value which HttpHandler is getting from extension implementing
     * RequestAwareInterface.
     *
     * @param string $responseType
     * @param string $version
     */
    public function __construct($responseType, $version)
    {
        $this->type    = $responseType;
        $this->version = $version;
        $this->body    = null;
    }

    /**
     * Add some data to response and set a key for it. If some data was set for that key previously - it will be replaced
     * with the new value.
     *
     * @param string $key
     * @param mixed  $data
     */
    public function add($key, $data)
    {
        $this->body[$key] = $data;
    }

    /**
     * Getter to get value stored in response associated for key.
     *
     * @param string $key
     *
     * @return null|mixed
     */
    public function get($key)
    {
        if (!empty($this->body[$key])) {
            return $this->body[$key];
        }

        return null;
    }

    /**
     * Setter allows to set complete response at once. It will overwrite all previous data in response. Data parameter
     * can be array or an object.
     *
     * @param array|mixed $data
     */
    public function setData($data)
    {
        $this->body = $data;
    }

    /**
     * Getter for response type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Getter for response version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Getter for the available data in response. Data can be array (if only add() method was used) or mixed if
     * setData() was used (because setData() allows to set objects as well). Also it's possible to get NULL if nothing
     * was set. You can check that by calling isEmpty() method.
     *
     * @return array|mixed|null
     */
    public function getData()
    {
        return $this->body;
    }

    /**
     * Checks if response contains any data or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        if (is_array($this->body) || is_string($this->body)) {
            return false;
        }

        return empty($this->body);
    }

    /**
     * @return string
     */
    public function isRedirect()
    {
        return $this->isRedirect;
    }

    /**
     * @param string $isRedirect
     */
    public function setRedirect($isRedirect)
    {
        $this->isRedirect = $isRedirect;
    }
}
