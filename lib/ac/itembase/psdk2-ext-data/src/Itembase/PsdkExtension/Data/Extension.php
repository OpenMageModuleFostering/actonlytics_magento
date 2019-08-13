<?php
/**
 * Copyright (C) 2016 itembase GmbH - All Rights Reserved
 */

namespace Itembase\PsdkExtension\Data;

use Itembase\Psdk\Container\ContainerAwareInterface;
use Itembase\Psdk\Container\ServiceContainer;
use Itembase\Psdk\Extension\ExtensionInterface;
use Itembase\Psdk\Http\Request;
use Itembase\Psdk\Http\RequestAwareInterface;
use Itembase\Psdk\Http\Response;

/**
 * Class Extension
 *
 * @package Itembase\PsdkExtension\Data
 */
class Extension implements ExtensionInterface, ContainerAwareInterface, RequestAwareInterface
{
    const DATA_FETCH_FETCHER = "data_fetch_fetcher";

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
        return 'data_fetch';
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
        $supportedActions = array("getProfileData", "getBuyersData", "getTransactionsData", "getProductsData");
        if (!$request->matchActions($supportedActions)) {
            return;
        }

        $this->container->verifyServices(array(
            array(self::DATA_FETCH_FETCHER => 'Itembase\PsdkExtension\Data\FetcherInterface')
        ));

        $fetcher = $this->container->getService(self::DATA_FETCH_FETCHER);

        ini_set('max_execution_time', 0);

        $params          = $request->getIbParams();
        $page            = isset($params['ib_page']) ? (int)$params['ib_page'] : 1;
        $limit           = isset($params['ib_limit']) ? (int)$params['ib_limit'] : 50;
        $minDateModified = isset($params['ib_min_date_modified']) ? (int)$params['ib_min_date_modified'] : null;
        $minDateCreated  = isset($params['ib_min_date_created']) ? (int)$params['ib_min_date_created'] : null;
        $method          = $request->getAction();
        $rawData         = call_user_func(
            array($fetcher, $method),
            $request->getShopId(),
            $page,
            $limit,
            $minDateCreated,
            $minDateModified
        );

        $response->setData($rawData);
    }
}
