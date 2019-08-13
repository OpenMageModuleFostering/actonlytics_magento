<?php

namespace Itembase\Psdk\Platform;

/**
 * Interface StorageInterface
 *
 * StorageInterface is an interface to abstract storage manipulation for different platforms.
 * It's a necessary to implement that interface, because SDK core is rely on having it implemented and all storing
 * operation will use it.
 *
 * It should be available in ServiceContainer by tag "storage".
 *
 * @package       Itembase\Psdk\Platform
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
interface StorageInterface
{
    /**
     * Method allows developer to store new or update existing value in the platform storage. Implementation should take
     * in consideration "shopId" parameter which distinguish values for different shops.
     *
     * So it's possible to have same "key" for the "value" for different "shopId".
     *
     * Also it's possible that some values are global for all shops or shop system doesn't support multi-shop. So
     * "shopId" in these cases can be NULL, so implementation should be done keeping that in mind.
     *
     * @param string      $key
     * @param null|string $shopId
     *
     * @return null|string
     */
    public function get($key, $shopId);

    /**
     * Method returns existing value in the platform storage. Implementation should take in consideration "shopId"
     * parameter which distinguish values for different shops.
     *
     * So it's possible to have same "key" for the "value" for different "shopId".
     *
     * Also it's possible that some values are global for all shops or shop system doesn't support multi-shop. So
     * "shopId" in these cases can be NULL, so implementation should be done keeping that in mind.
     *
     * @param string $key
     * @param string $value
     * @param string $shopId
     *
     * @throws \Exception
     */
    public function save($key, $value, $shopId);

    /**
     * Method returns shop id in platform by some key and it's value which will make sure it's assign only for the
     * specific shop, e.g. client_id of the credentials
     *
     * Also it's possible that some values are global for all shops or shop system doesn't support multi-shop. So
     * "shopId" in these cases can be NULL, so implementation should be done keeping that in mind.
     *
     * @param string $key
     * @param string $value
     *
     * @return int|string $shopId
     *
     * @throws \Exception
     */
    public function getShopIdBy($key, $value);

    /**
     * Method sets assoc array with simple key as a key of array and platform specific key as a value. Handy to use
     * when platform system has key-spaces or more complex storage.
     *
     * @param array $map Key to platform key mapping array
     */
    public function setKeyMapping($map);

    /**
     * Method returns assoc array with simple key as a key of array and platform specific key as a value.
     *
     * @return array Key to platform key mapping array
     */
    public function getKeyMapping();

    /**
     * Method sets keyspace for values inside platform storage. Please keep in mind - if set used for both: get and set
     *
     * @param string $keyspace Keyspace for storage values
     */
    public function setKeyspace($keyspace);

    /**
     * Method returns keyspace value which was set previously
     *
     * @return string Keyspace for storage values
     */
    public function getKeyspace();

    /**
     * Method allows developer to delete existing value in the platform storage. Implementation should take
     * in consideration "shopId" parameter which distinguish values for different shops.
     *
     * So it's possible to have same "key" for the "value" for different "shopId".
     *
     * Also it's possible that some values are global for all shops or shop system doesn't support multi-shop. So
     * "shopId" in these cases can be NULL, so implementation should be done keeping that in mind.
     *
     * @param string      $key
     * @param null|string $shopId
     *
     * @return void
     */
    public function delete($key, $shopId);
}
