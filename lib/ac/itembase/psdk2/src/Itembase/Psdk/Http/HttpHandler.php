<?php
namespace Itembase\Psdk\Http;

use Itembase\Psdk\Container\ServiceContainer;
use Itembase\Psdk\Core;
use Itembase\Psdk\Core\OAuthClient;
use Itembase\Psdk\Platform\Frontend\ControllerInterface;
use Itembase\Psdk\Platform\StorageInterface;

/**
 * Class HttpHandler
 *
 * HttpHandler class take care for detecting if request is aimed directly to itembase plugin, parsing that request and
 * make out of it Request object and prepare Response objects for each extension which needs to return some information
 * back to the request initiator.
 *
 * Class supports custom renderer via \Itembase\Psdk\Platform\Frontend implementation.
 *
 * It should not be considered as framework-quality class. It's specifically used for handling itembase server requests.
 *
 * @package       Itembase\Psdk\Http
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class HttpHandler
{
    const FRONTEND_RESPONSE_HANDLER = "frontend_response_handler";

    /** @var ServiceContainer $serviceContainer */
    protected $serviceContainer;

    /** @var Request $request */
    protected $request;

    /** @var array $responses */
    protected $responses;

    /**
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainer $container)
    {
        $this->request          = null;
        $this->serviceContainer = $container;
    }

    /**
     * Method is going through all instances in ServiceContainer and for the objects which are implementing
     * RequestAwareInterface interface will call handleRequest() method with Request object and Response.
     *
     * Response object is created per service/instance found in ServiceContainer which implements RequestAwareInterface.
     *
     * @return void
     */
    public function initRequestAwareServices()
    {
        if (null === $this->request) {
            return;
        }

        $tags = $this->serviceContainer->listServices();

        foreach ($tags as $tag) {
            $service = $this->serviceContainer->getService($tag);

            if (!$service instanceof RequestAwareInterface) {
                continue;
            }

            $this->initRequestAwareService($service);
        }
    }

    /**
     * Method is going through all instances in ServiceContainer and for the objects which are implementing
     * ResponseAwareInterface interface will call setResponse() method with Response object.
     *
     * Response object is created per service/instance found in ServiceContainer which implements RequestAwareInterface.
     * Because services/extensions can evolve and change it's output format each response has version and type. That way
     * itembase backend knows how to handle response correctly.
     *
     * @return void
     */
    public function initResponseAwareServices()
    {
        $tags = $this->serviceContainer->listServices();

        foreach ($tags as $tag) {
            $service = $this->serviceContainer->getService($tag);

            if (!$service instanceof ResponseAwareInterface) {
                continue;
            }

            $this->initResponseAwareService($service);
        }
    }

    /**
     * During method call HttpHandler check if request is actually is done directly for the plugin. It's checking it by
     * GET parameters: "ib_method" and "ib_token". If they are not presented - request considered as not for plugin and
     * method stop it's execution so no Request object is created.
     *
     * If these parameters are presented method will check if "ib_api_key" and "ib_token" parameters are set.  If they
     * are not passed - request considered as unauthorized.
     *
     * If parameters are presented - method will validate "ib_token" using OAuthClient class, so in the end token is
     * verified using itembase server. If token passed validation - request marked as authorized,
     * otherwise - unauthorized.
     *
     * Method may throw exception:
     * - if api key is not set
     * - if secret is not set
     * - if some issue happened during request to itembase server
     * - if some issue happened during storing received from itembase server access token
     *
     * @throws \Exception
     * @return void
     */
    public function initRequest()
    {
        if (empty($_GET['ib_method']) || empty($_GET['ib_token'])) {
            return;
        }

        $shopId       = null;
        $isAuthorized = false;

        if (!empty($_GET['ib_api_key']) && !empty($_GET['ib_token'])) {
            /** @var StorageInterface $storage */
            $storage = $this->serviceContainer->getService(Core::SERVICE_STORAGE);

            /** @var OAuthClient $oauthClient */
            $oauthClient = $this->serviceContainer->getService(Core::SERVICE_OAUTH_CLIENT);
            $shopId      = $storage->getShopIdBy(OAuthClient::STORAGE_API_KEY, $_GET['ib_api_key']);

            if (!empty($shopId)) {
                $token = $oauthClient->validateToken($_GET['ib_token'], $shopId);

                if (!empty($token)) {
                    $isAuthorized = true;
                }
            }
        }

        $this->request = new Request($isAuthorized, $shopId);
    }

    /**
     * Method used to output all the responses.
     *
     * Because method is sending header - before doing anything it checks if some content was already sent. If that is
     * the case - nothing will be sent and method will stop it's execution.
     *
     * Otherwise It's going by each response in responses array and format responses array as:
     * - response - type of the response returned by getType() method of the Response object
     * - version- version of the response returned by getVersion() method of the Response object
     * - payload - actual response data from the Response object
     *
     * When all Response objects are processed method send application/json header and output JSON serialized responses
     * array.
     *
     * @throw \Exception
     * @return void
     */
    public function sendResponses()
    {
        // we don't want to cause warnings if something was echo/print etc. already
        if (headers_sent()) {
            return;
        }

        if ($this->serviceContainer->hasService(self::FRONTEND_RESPONSE_HANDLER)) {
            $frontend = $this->serviceContainer->getService(self::FRONTEND_RESPONSE_HANDLER);
            if ($frontend instanceof ControllerInterface) {
                $frontend->renderResponse($this->request, $this->responses);

                return;
            }
        }

        $data = array();

        /** @var Response $response */
        foreach ($this->responses as $response) {
            if (!$response->isEmpty()) {
                $data[] = array(
                    'response' => $response->getType(),
                    'version'  => $response->getVersion(),
                    'payload'  => $response->getData()
                );
            }
        }

        if (!empty($data)) {
            header("Content-type: application/json");
            echo json_encode($data);
        }
    }

    /**
     * Inject to object implemented ResponseAwareInterface interface Response object via setResponse() method.
     * A new response object is created each time for RequestAwareInterface object.
     *
     * @param ResponseAwareInterface $service
     */
    public function initResponseAwareService(ResponseAwareInterface $service)
    {
        $response          = new Response($service->getResponseType(), $service->getResponseVersion());
        $this->responses[] = $response;
        $service->setResponse($response);
    }

    /**
     * Inject to object implemented RequestAwareInterface interface Request and Response objects via handleRequest()
     * method. A new response object is created each time for RequestAwareInterface object.
     *
     * @param RequestAwareInterface $service
     */
    public function initRequestAwareService(RequestAwareInterface $service)
    {
        $response          = new Response($service->getResponseType(), $service->getResponseVersion());
        $this->responses[] = $response;
        $service->handleRequest($this->request, $response);
    }
}
