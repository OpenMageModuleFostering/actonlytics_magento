<?php
namespace Itembase\Psdk\Http;

/**
 * Interface RequestAwareInterface
 *
 * When some service/extension need to handle request from itembase server and send back  some data/response back to
 * itembase server it should implement RequestAwareInterface interface.
 *
 * HttpHandler use these implementations to prepare Response object for each service/extension which implements
 * RequestAwareInterface interface and also pass the actual Request object.
 *
 * @package       Itembase\Psdk\Http
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
interface RequestAwareInterface
{
    /**
     * Method will be called when the request will be done from itembase server to the plugin.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return void
     */
    public function handleRequest(Request $request, Response $response);

    /**
     * Method is called to get response type name. It's necessary for backend to know what extension/service is replying
     * to request. It's possible that several extensions/services reply to one request.
     *
     * @return string
     */
    public function getResponseType();

    /**
     * Method is called to get response version. It's necessary for backend to know what output version extension/service
     * is replying to request. It's possible that several extensions/services reply to one request with different
     * versions.
     *
     * (!) IMPORTANT. Please change version for ANY changes (even not BC ones). So if you added a new field (which is
     * not BC) in output - version must be changed.
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
}
