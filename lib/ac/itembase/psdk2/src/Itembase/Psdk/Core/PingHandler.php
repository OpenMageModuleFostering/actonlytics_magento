<?php
namespace Itembase\PsdkExtension\Registration;

use Itembase\Psdk\Extension\ExtensionInterface;
use Itembase\Psdk\Http\Request;
use Itembase\Psdk\Http\RequestAwareInterface;
use Itembase\Psdk\Http\Response;

/**
 * Class PingHandler
 *
 * Build-in ping/pong handler for check if plugin is still presented on the connected to itembase shop
 *
 * @package       ItembaseExtension\Psdk\Registration
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class PingHandler implements ExtensionInterface, RequestAwareInterface
{
    /**
     * @return string
     */
    public function getExtensionName()
    {
        return 'ping';
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
     * {@inheritdoc}
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
        if (!$request->matchActions(array('ping'), false)) {
            return;
        }

        $response->setData('pong');
    }
}
