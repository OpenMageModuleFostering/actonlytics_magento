<?php
namespace Itembase\Psdk\Core;

use Itembase\Psdk\Container\ContainerAwareInterface;
use Itembase\Psdk\Container\ServiceContainer;
use Itembase\Psdk\Core;
use Itembase\Psdk\Http\HttpClient;
use Itembase\Psdk\Platform\StorageInterface;

/**
 * Class OAuthClient
 *
 * Simple OAuth client designed to communicate with itembase server to get/verify access and one-time tokens.
 *
 * @package       Itembase\Psdk\Core
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class OAuthClient implements ContainerAwareInterface
{
    const STORAGE_API_KEY        = "api_key";
    const STORAGE_API_SECRET     = "api_secret";
    const STORAGE_API_TOKEN      = "api_token";
    const STORAGE_API_TBCS_TOKEN = "api_tbcs_token";

    /** @var ServiceContainer $serviceContainer */
    protected $serviceContainer;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ServiceContainer $container)
    {
        $this->serviceContainer = $container;
    }

    /**
     * Method is trying to authenticate towards OAuth itembase server using API key and secret which must be stored
     * already in storage. As a result - token is received from itembase server, stored in shop storage and returned by
     * method.
     *
     * If token already exists in the storage - method will check if it's still valid (check if it's not expired) and
     * will return it instead of requesting new one.
     *
     * Operation is happening per shop so "shopId" parameter is passed. Same as a StorageInterface OAuthClient is
     * supporting "shopId" to be NULL.
     * Method throws exception:
     * - if api key is not set
     * - if secret is not set
     * - if some issue happened during request to itembase server
     * - if some issue happened during storing received from itembase server access token
     *
     * It's used by validateToken method to make an authorized request to itembase server.
     *
     * @param string $shopId
     *
     * @return array
     *
     * @throws \Exception
     */
    public function authenticateClient($shopId)
    {
        /** @var StorageInterface $storage */
        $storage = $this->serviceContainer->getService(Core::SERVICE_STORAGE);
        $token   = $storage->get(self::STORAGE_API_TOKEN, $shopId);

        if (!empty($token)) {
            $token = json_decode($token, true);
            if ($token['expires_at'] > time()) {
                return $token;
            }
        }

        $clientId = $storage->get(self::STORAGE_API_KEY, $shopId);
        if (empty($clientId)) {
            throw new \Exception('Api key is not set');
        }

        $clientSecret = $storage->get(self::STORAGE_API_SECRET, $shopId);
        if (empty($clientSecret)) {
            throw new \Exception('Secret key is not set');
        }

        $data = array(
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'response_type' => 'token',
            'grant_type'    => 'http://oauth.itembase.com/grants/plugin'
        );

        $header       = array("Content-type: application/x-www-form-urlencoded");
        $client       = new HttpClient();
        $jsonResponse = $client->sendData(ITEMBASE_OAUTH_SERVER_URL . '/oauth/v2/token', $data, $header);
        $token        = json_decode($jsonResponse, true);

        if (isset($token['error'])) {
            throw new \Exception(
                sprintf(
                    'Error during OAuth authorization: %s',
                    isset($token['error_description']) ? $token['error_description'] : $token['error']
                )
            );
        }

        $token['expires_at'] = time() + intval($token['expires_in']);
        unset($token['expires_in'], $token['scope']);

        $storage->save(self::STORAGE_API_TOKEN, json_encode($token), $shopId);

        return $token;
    }

    /**
     * Validate access token
     * Method is used to validate token received in the request to plugin. As a result method will store token in the
     * storage (with expire date) and returns an array with the actual token and expire date.
     *
     * Later when request will be done with the same token method will check if the storage has already such token and
     * instead of sending request to itembase server will just return that token.
     *
     * Method may throw exception:
     * - if api key is not set
     * - if secret is not set
     * - if some issue happened during request to itembase server
     * - if some issue happened during storing received from itembase server access token
     *
     * @param string $tokenToCheck
     * @param string $shopId
     *
     * @return array
     *
     * @throws \Exception
     */
    public function validateToken($tokenToCheck, $shopId)
    {
        /** @var StorageInterface $storage */
        $storage = $this->serviceContainer->getService(Core::SERVICE_STORAGE);
        $tbcs    = json_decode(
            $storage->get(self::STORAGE_API_TBCS_TOKEN, $shopId), true
        );

        if (isset($tbcs['tbcs_token']) && $tokenToCheck == $tbcs['tbcs_token']
            && isset($tbcs['expires_at']) && $tbcs['expires_at'] > time()
        ) {
            return $tbcs;
        }

        $accessToken = $this->authenticateClient($shopId);

        $header[]     = 'Authorization: Bearer ' . $accessToken['access_token'];
        $client       = new HttpClient();
        $jsonResponse = $client->sendData(ITEMBASE_OAUTH_SERVER_URL . '/tbcs/v1/tokens/' . $tokenToCheck, null, $header);
        $response     = json_decode($jsonResponse, true);

        $tbcs = array(
            'tbcs_token' => $tokenToCheck,
            'expires_at' => $response['expires_at']
        );

        $storage->save(self::STORAGE_API_TBCS_TOKEN, json_encode($tbcs), $shopId);

        return $tbcs;
    }
}
