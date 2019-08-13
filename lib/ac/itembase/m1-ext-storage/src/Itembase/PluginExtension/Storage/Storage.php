<?php
namespace Itembase\PluginExtension\Storage;

use Itembase\Psdk\Platform\StorageInterface;

/**
 * Class Storage
 *
 * Implementation of Itembase\Psdk\Platform\StorageInterface for Magento 1.7 - 1.9
 *
 * @package       ItembaseExtension\Plugin\Storage
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2015 itembase GmbH
 */
class Storage implements StorageInterface
{
    /** @var array $keyMap */
    protected $keyMap;

    /** @var string $keyspace */
    protected $keyspace = "itembase/";

    /**
     * {@inheritdoc}
     */
    public function get($key, $shopId)
    {
        if (null == $shopId) {
            $shopId = 0;
        }

        $key = $this->getConfigPath($key);

        return \Mage::getStoreConfig($key, $shopId);
    }

    /**
     * {@inheritdoc}
     */
    public function save($key, $value, $shopId)
    {
        if (null == $shopId) {
            $shopId = 0;
        }

        $key = $this->getConfigPath($key);

        \Mage::getConfig()->saveConfig($key, $value, 'stores', $shopId);
        \Mage::getConfig()->reinit();

        \Mage::app()->reinitStores();
    }

    /**
     * {@inheritdoc}
     */
    public function getShopIdBy($key, $value)
    {
        $key = $this->getConfigPath($key);

        foreach (\Mage::app()->getStores() as $shop) {
            $storeId = $shop->getId();

            if (\Mage::getStoreConfig($key, $storeId) === $value) {
                return $storeId;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setKeyMapping($map)
    {
        $this->keyMap = $map;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyMapping()
    {
        return $this->keyMap;
    }

    /**
     * {@inheritdoc}
     */
    public function setKeyspace($keyspace)
    {
        $this->keyspace = $keyspace;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyspace()
    {
        return $this->keyspace;
    }

    public function delete($key, $shopId)
    {
        if (null == $shopId) {
            $shopId = 0;
        }

        $key = $this->getConfigPath($key);

        \Mage::getConfig()->deleteConfig($key, 'stores' , $shopId);
        \Mage::getConfig()->reinit();

        \Mage::app()->reinitStores();
    }

    /**
     * Returns key according to keymaps
     *
     * @param string $key
     * @return string
     */
    protected function getConfigPath($key)
    {
        if (!empty($this->keyMap[$key])) {
            $key = $this->keyMap[$key];
        } else {
            $key = $this->keyspace . $key;
        }

        return $key;
    }
}
