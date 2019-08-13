<?php
namespace Itembase\PsdkExtension\Signup;

use Itembase\Psdk\Container\ContainerAwareInterface;
use Itembase\Psdk\Container\ServiceContainer;
use Itembase\Psdk\Core;
use Itembase\Psdk\Core\OAuthClient;
use Itembase\Psdk\Extension\ExtensionInterface;
use Itembase\Psdk\Http\HttpClient;
use Itembase\Psdk\Http\Request;
use Itembase\Psdk\Http\RequestAwareInterface;
use Itembase\Psdk\Http\Response;
use Itembase\Psdk\Platform\MultiShop\Shop;
use Itembase\Psdk\Platform\StorageInterface;
use Itembase\Psdk\Platform\MultiShop\MultishopAbstract;

/**
 * Class Extension
 *
 * @package       Itembase\PsdkExtension\Signup
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class Extension implements ExtensionInterface, RequestAwareInterface, ContainerAwareInterface
{
    /**
     * @var array
     *
     * Shop information; in case of multi-shop - several elements will be in array
     */
    protected $shops = array();

    /** @var ServiceContainer */
    protected $container;

    /**
     * @param ServiceContainer $container
     */
    public function setContainer(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return 'signup';
    }

    /**
     * @return string
     */
    public function getResponseType()
    {
        return $this->getExtensionName();
    }

    /**
     * @return string
     */
    public function getResponseVersion()
    {
        return '1.0';
    }

    /**
     * @return string
     */
    public function getRequestVersion()
    {
        return '1.0';
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @throws \Exception
     */
    public function handleRequest(Request $request, Response $response)
    {
        if (!$request->matchActions(array('signup'), false)) {
            return;
        }
        $client      = new HttpClient();
        $details     = $client->sendData(ITEMBASE_PLUGIN_SERVICE . '/signup/client-details/' . $request->getToken());
        $credentials = json_decode($details, true);
        $keys = array(
            'api_key'    => $credentials['api_key'],
            'secret_key' => $credentials['secret'],
            'shop_id'    => $credentials['shop_id'],
        );
        if (empty($keys['api_key']) || empty($keys['secret_key']) || empty($keys['shop_id'])) {
            $response->add('status', 'failed');
            $response->add('reason', 'Keys are empty: ' . print_r($keys, true));

            return;
        }
        /** @var StorageInterface $storage */
        $storage = $this->container->getService(Core::SERVICE_STORAGE);
        $storage->save(MultishopAbstract::REGISTERED_STORAGE_FLAG, true, $keys['shop_id']);
        $storage->save(OAuthClient::STORAGE_API_KEY, $keys['api_key'], $keys['shop_id']);
        $storage->save(OAuthClient::STORAGE_API_SECRET, $keys['secret_key'], $keys['shop_id']);
        $response->add('status', 'success');
        $response->add('reason', sprintf("Shop was successfully registered"));
    }

    /**
     * Return html code of the registration page.
     *
     * @param string $redirectUri Full URL where itembase backend can redirect user after signup on server
     * @param string $language    ISO 639-1 language code (2-letter for example 'en')
     * @return string html code of the signup page to the itembase.com
     */
    public function htmlSignupPage($redirectUri, $language = 'en')
    {
        if (!defined('ITEMBASE_SELFSERVICE_URL')) {
            define('ITEMBASE_SELFSERVICE_URL', 'https://selfservice.itembase.com');
        }

        if (!defined('ITEMBASE_EMBEDDED_TEMPLATE')) {
            define('ITEMBASE_EMBEDDED_TEMPLATE', 'https://deliver-static-d1.itembase.com/embed/signup');
        }

        if (defined('ITEMBASE_RETURN_URL') && !empty(ITEMBASE_RETURN_URL)) {
            $redirectUri = ITEMBASE_RETURN_URL;
        }

        if (empty($language)) {
            $language = 'en';
        }

        $atLeastOne    = false;
        $tplComponents = array();
        $shops         = $this->container->getService(Core::SERVICE_MULTISHOP)->getList();
        $content       = "";
        $templateUrl   = sprintf(
            "%s?lang=%s&platform=%s",
            ITEMBASE_EMBEDDED_TEMPLATE,
            $language,
            $this->container->getService(Core::SERVICE_PLATFORM)->getName()
        );

        try {
            $client        = new HttpClient();
            $response      = $client->sendData($templateUrl);
            $tplComponents = json_decode($response, true);
        } catch (\Exception $ex) {
            $this->container->getService('logger')->log(\Itembase\Psdk\Core\Logger::IB_LOG_ERR, $ex);
        }

        if (!empty($shops)) {
            /** @var Shop $shop */
            foreach ($shops as $shop) {
                $backToShop = $redirectUri;

                if ($shop->registered) {
                    $atLeastOne = true;
                    $content .= str_replace('%name%', $shop->name, $tplComponents['shop_connected_tpl']);
                    continue;
                }

                $njordUrl = sprintf("%s/v1/tokens", ITEMBASE_SELFSERVICE_URL);
                $njordReq = array(
                    'component_ids'         => array(ITEMBASE_VARIANT_ID),
                    'action'                => 'create',
                    'additional_parameters' => array(
                        'build'      => ITEMBASE_PLUGIN_BUILD,
                        'shop_url'   => $shop->url,
                        'shop_id'    => $shop->id,
                        'shop_name'  => $shop->name,
                        'shop_lang'  => $shop->defaultLanguage,
                        'return_uri' => $backToShop,
                    ),
                );

                if (defined('ITEMBASE_BRANDED_ID')) {
                    $njordReq['client_id'] = ITEMBASE_BRANDED_ID;
                    $njordReq['enable_whisper_signup'] = true;
                }

                try {
                    $njordClient = new HttpClient();

                    $njordToken  = $njordClient->sendJsonData(
                        $njordUrl,
                        $njordReq,
                        array("Content-type: application/json")
                    );

                    $njordToken = json_decode($njordToken, true);

                    $backToShop .= (false === strpos($backToShop, '?')) ? '?' : '&';

                    if (!empty($njordToken['uri'])) {
                        $backToShop .= 'success=Connection+successful';
                        $backToShop = sprintf("%s?redirect_uri=%s", $njordToken['uri'], urlencode($backToShop));
                    } else {
                        $backToShop .= 'error=We+have+some+problem.+Please+try+again+later';
                    }
                } catch (\Exception $ex) {
                    $backToShop .= 'error=We+have+some+problem.+Please+try+again+later';
                }

                $content .= str_replace(
                    array(
                        '%signup_url%',
                        '%name%'
                    ),
                    array(
                        $backToShop,
                        $shop->name
                    ),
                    $tplComponents['shop_tpl']
                );
            }
        }

        return str_replace(
            array(
                '<shops_placeholder/>',
                '<button_placeholder/>'
            ),
            array(
                $content,
                ($atLeastOne) ? $tplComponents['button_tpl'] : $tplComponents['no_button_tpl']
            ),
            $tplComponents['base_tpl']
        );
    }
}
