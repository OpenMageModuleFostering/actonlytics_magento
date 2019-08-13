<?php
/**
 * Copyright (C) 2016 itembase GmbH - All Rights Reserved
 */

namespace Itembase\PsdkExtension\Data;

/**
 * Interface FetcherInterface
 *
 * @author Paweł Kubasiak <pk@itembase.biz>
 */
interface FetcherInterface
{
    /**
     * Method to fetching all available raw shop profile data from the shop
     *
     * Function should return array with ALL available shop profile data which you can fetch from the shop.
     * Fetched data must include (but not limited to) information about:
     * - shop name
     * - shop url
     *
     * @param string $shopId         id of the shop if shop has multi-store options.
     * @param int    $page           pagination parameter
     * @param int    $limit          how many profiles fetch from database
     * @param string $minDateCreated minimal date when the profile was created
     * @param string $minDateUpdated minimal date when the profile was updated
     *
     * @return array
     */
    public function getProfileData($shopId, $page, $limit, $minDateCreated, $minDateUpdated);

    /**
     * Method to fetching all available raw buyer data from the shop
     *
     * Function should return array with ALL available shop buyer data which you can fetch from the shop.
     * Fetched data must include (but not limited to) information about:
     * - id (unique buyer id number)
     * - email address
     *
     * @param string $shopId         id of the shop if shop has multi-store options.
     * @param int    $page           pagination parameter
     * @param int    $limit          how many profiles fetch from database
     * @param string $minDateCreated minimal date when the profile was created
     * @param string $minDateUpdated minimal date when the profile was updated
     *
     * @return array
     */
    public function getBuyersData($shopId, $page, $limit, $minDateCreated, $minDateUpdated);

    /**
     * Method to fetching all available raw shop transaction data from the shop
     *
     * Function should return array with ALL available shop transaction data which you can fetch from the shop.
     * Fetched data must include (but not limited to) information about:
     * - id
     * - created date
     * - currency
     * - total price
     * - total price net
     * - total tax price
     * - transaction status
     * - information about product: product url, created date, name, currency, price per unit,
     *   tax rate, product description, product categories, product images urls, stock information,
     * - information about buyer: id, address email
     *
     * @param string $shopId         id of the shop if shop has multi-store options.
     * @param int    $page           pagination parameter
     * @param int    $limit          how many profiles fetch from database
     * @param string $minDateCreated minimal date when the profile was created
     * @param string $minDateUpdated minimal date when the profile was updated
     *
     * @return array
     */
    public function getTransactionsData($shopId, $page, $limit, $minDateCreated, $minDateUpdated);

    /**
     * Method to fetching all available raw shop product data from the shop
     *
     * Function should return array with ALL available shop product data which you can fetch from the shop.
     * Fetched data must include (but not limited to) information about:
     * - product url
     * - created date
     * - currency
     * - price per unit
     * - tax rate
     * - description
     * - product categories
     * - product images urls
     * - stock information
     *
     * @param string $shopId         id of the shop if shop has multi-store options.
     * @param int    $page           pagination parameter
     * @param int    $limit          how many profiles fetch from database
     * @param string $minDateCreated minimal date when the profile was created
     * @param string $minDateUpdated minimal date when the profile was updated
     *
     * @return array
     */
    public function getProductsData($shopId, $page, $limit, $minDateCreated, $minDateUpdated);
}
