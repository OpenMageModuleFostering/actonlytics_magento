<?php
namespace Itembase\PluginExtension\Multishop;

use Itembase\Psdk\Container\ServiceContainer;
use Itembase\Psdk\Core;
use Itembase\Psdk\Platform\MultiShop\MultishopAbstract;
use Itembase\Psdk\Platform\MultiShop\Shop;
use Itembase\Psdk\Platform\StorageInterface;

/**
 * Class Multishop
 *
 * @package       Itembase\PluginExtension\Multishop
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2015 itembase GmbH
 */
class Multishop extends MultishopAbstract
{
    /** @var ServiceContainer */
    private $container;

    /**
     * Multishop constructor.
     *
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function getShops()
    {
        $shopList = array();

        /** @var StorageInterface $storage */
        $storage = $this->container->getService(Core::SERVICE_STORAGE);

        foreach (\Mage::app()->getStores() as $shop) {
            $subShop = new Shop();
            $storeId = $shop->getId();

            $subShop->url = \Mage::getStoreConfig('web/unsecure/base_url', $storeId) !== null
                ? \Mage::getStoreConfig('web/unsecure/base_url', $storeId)
                : \Mage::getStoreConfig('web/unsecure/base_url');

            $subShop->url = rtrim($subShop->url, '/');

            $subShop->name = \Mage::getStoreConfig('general/store_information/name', $storeId)
                ? \Mage::getStoreConfig('general/store_information/name', $storeId)
                : $shop->getName();

            $subShop->id              = $storeId;
            $subShop->currency        = $shop->getCurrentCurrencyCode();
            $subShop->defaultLanguage = substr(\Mage::getStoreConfig('general/locale/code', $storeId), 0, 2);
            $subShop->timezone        = \Mage::getStoreConfig('general/locale/timezone', $storeId);

            $isRegistered = $storage->get(MultishopAbstract::REGISTERED_STORAGE_FLAG, $storeId);

            if (!empty($isRegistered)) {
                $subShop->registered = true;
            }

            $shopList[] = $subShop;
        }

        return $shopList;
    }

    /**
     * {@inheritdoc}
     */
    public function isMultiShop()
    {
        return true;
    }
}
