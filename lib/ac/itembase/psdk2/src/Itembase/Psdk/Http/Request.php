<?php
namespace Itembase\Psdk\Http;

/**
 * Class Request
 *
 * Represents request form itembase server.
 *
 * Request class should not be treated like framework-quality class for any requests. It's only to represent request
 * data from itembase server.
 *
 * @package       Itembase\Psdk\Http
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class Request
{
    const GET_REQUEST_TOKEN  = 'ib_token';
    const GET_REQUEST_METHOD = 'ib_method';

    /** @var string $action */
    protected $action;

    /** @var string $token */
    protected $token;

    /** @var string $apiKey */
    protected $apiKey;

    /** @var string $shopId */
    protected $shopId;

    /** @var array $ibParams */
    protected $ibParams;

    /** @var boolean $authorized */
    protected $authorized;

    /**
     * ItembaseRequest constructor.
     *
     * @param boolean    $authorized
     * @param string|int $shopId
     */
    public function __construct($authorized, $shopId)
    {
        $this->authorized = $authorized;

        $this->token  = $_GET[self::GET_REQUEST_TOKEN];
        $this->action = $_GET[self::GET_REQUEST_METHOD];

        if (!empty($shopId)) {
            $this->shopId = $shopId;
        }

        foreach ($_GET as $key => $value) {
            if ($key == "ib_method" || $key == "ib_token") {
                continue;
            }

            if (strpos($key, "ib_") === 0) {
                $this->ibParams[$key] = $value;
            }
        }

        if (!empty($this->ibParams['ib_api_key'])) {
            $this->apiKey = $this->ibParams['ib_api_key'];
        }
    }

    /**
     * Getter for action value passed via GET parameter ib_method
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Getter for token value passed via GET parameter ib_token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Getter for action value passed via GET parameter ib_api_key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Getter for shop ID value passed via GET parameter ib_shop_id
     *
     * @return string
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Getter for all GET parameters which has ib_ prefix
     *
     * @return array
     */
    public function getIbParams()
    {
        return $this->ibParams;
    }

    /**
     * Getter for returning the authorization status of the request.
     *
     * @return boolean
     */
    public function isAuthorized()
    {
        return $this->authorized;
    }

    /**
     * Helper method allowing to match passed actions with the current requested and specify if request needs to be
     * authorized.
     *
     * @param array     $actions
     * @param bool|true $authRequired
     *
     * @return bool
     * @throws \Exception
     */
    public function matchActions($actions, $authRequired = true)
    {
        if (in_array($this->getAction(), $actions)) {
            if ($authRequired and !$this->isAuthorized()) {
                throw new \Exception("Unauthorized access");
            }

            return true;
        }

        return false;
    }
}
