<?php
namespace Itembase\Psdk\Http;

/**
 * Interface ResponseAwareInterface
 *
 * When some service/extension need to just return  some data to itembase server (without handling request) it should
 * implement ResponseAwareInterface interface.
 *
 * HttpHandler use these implementations to prepare Response object for each service/extension.
 *
 * @package       Itembase\Psdk\Http
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
interface ResponseAwareInterface
{
    /**
     * Returns name of the response type. Most of the time it will be service name.
     * Used by backend to correctly parse response of the service.
     *
     * @return string
     */
    public function getResponseType();

    /**
     * Returns response version of the service. It's vital to have it so backend can use correct mappings for the
     * response.
     *
     * @return string
     */
    public function getResponseVersion();

    /**
     * Method is called to get request version. It's necessary for backend to know what input version extension/service
     * is replying to request. It's possible that several extensions/services reply to one request with different
     * versions.
     *
     * (!) IMPORTANT. Please change version for ANY changes (even not BC ones). So if you added a new field (which is
     * not BC) in output - version must be changed.
     *
     * @return string
     */
    public function getRequestVersion();

    /**
     * Setter to receive Response object for the specific service
     *
     * @param Response $response
     *
     * @return void
     */
    public function setResponse(Response $response);
}
