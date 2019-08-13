<?php

namespace Itembase\Psdk\Platform\MultiShop;

/**
 * Interface MultiShopInterface
 *
 * That abstract class is used to have a collection to store available shops.
 *
 * Itembase\Psdk\Core class requires implementation of that abstract class to be presented in ServiceContainer as
 * "multishop".
 *
 * @package       Itembase\Psdk\MultiShop
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
abstract class MultishopAbstract
{
    const REGISTERED_STORAGE_FLAG = "registered";

    /** @var array $list */
    private $list = null;

    /** @var Shop $currentShop */
    private $currentShop = null;

    /** @var boolean $initialized */
    private $initialized = false;

    /**
     * Abstract method which developer need to implement. It should return indexed array of Shop objects.
     * If platform doesn't support multi-shop system - at least one Shop object should be returned.
     *
     * @return array|null
     */
    abstract protected function getShops();

    /**
     * Return true if multi-shop is supported by platform and false otherwise.
     *
     * @return boolean
     */
    abstract public function isMultiShop();

    /**
     * Getting currently active shop. If multi-shop is not supported main shop information is returned. If getList()
     * will not return currently active shop for some reason - first one will be picked as current shop.
     *
     * @return Shop|null
     *
     * @throws \Exception
     */
    public function getCurrentShop()
    {
        $this->init();

        return $this->currentShop;
    }

    /**
     * Returns whole list of all available Shop objects.
     *
     * @return array
     */
    public function getList()
    {
        $this->init();

        return $this->list;
    }

    /**
     * Internal method of the class. Used to fill list array with the shops returned by implemented getShops() method,
     * initialize class properties.
     *
     * In case when multi-shop is available and if there is no Shop object from the getShops() method has
     * currentlyActive = true - first shop from the list is considered as active.
     *
     * Method throw exception is case if getShops() returns empty array, or returns non-null or non-array value and
     * item from the list is not Shop object.
     *
     * @throws \Exception
     */
    private function init()
    {
        if (true === $this->initialized) {
            return;
        }

        $this->initialized = true;
        $this->list        = $this->getShops();

        if (null == $this->list) {
            return;
        }

        if (!is_array($this->list)) {
            throw new \Exception("getShops() should return array or null");
        }

        if (count($this->list) == 0) {
            throw new \Exception("At least one shop must be returned or null in case if multi-shop is not supported");
        }

        /** @var Shop $item */
        foreach ($this->list as $item) {
            if (!$item instanceof Shop) {
                throw new \Exception("Shop object is expected to be returned");
            }

            if ($item->currentlyActive) {
                $this->currentShop = $item;
            }
        }

        if (empty($this->currentShop)) {
            $this->currentShop = $this->list[0];
        }
    }
}
