<?php
/**
 * Copyright (C) 2016 itembase GmbH - All Rights Reserved
 */
namespace Itembase\PluginExtension\Data;

use Itembase\Psdk\Extension\ExtensionInterface;
use Itembase\PsdkExtension\Data\FetcherInterface;

/**
 * Class Extension
 *
 * @package Itembase\PluginExtension\Data
 */
class Extension implements ExtensionInterface, FetcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExtensionName()
    {
        return \Itembase\PsdkExtension\Data\Extension::DATA_FETCH_FETCHER;
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileData($shopId, $page, $limit, $minDateCreated, $minDateUpdated)
    {
        $shop = \Mage::app()->getStore($shopId);

        $data[] = array(
            'store'    => $shop->getData(),
            'currency' => $shop->getCurrentCurrencyCode(),
            'timezone' => \Mage::getStoreConfig('general/locale/timezone', $shopId),
            'language' => $language = substr(\Mage::getStoreConfig('general/locale/code', $shopId), 0, 2),
            'email'    => \Mage::getStoreConfig('trans_email/ident_general/email', $shopId),
            'url'      => \Mage::getStoreConfig('web/unsecure/base_url', $shopId) !== null
                ? \Mage::getStoreConfig('web/unsecure/base_url', $shopId)
                : \Mage::getStoreConfig('web/unsecure/base_url'),
        );

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getBuyersData($shopId, $page, $limit, $minDateCreated, $minDateUpdated)
    {
        $minDateCreated = date('Y-m-d H:i:s', $minDateCreated);
        $minDateUpdated = date('Y-m-d H:i:s', $minDateUpdated);

        $customerCollections = \Mage::getModel('customer/customer')
                                    ->getCollection()
                                    ->addAttributeToSort('created_at', 'desc')
                                    ->addAttributeToFilter('created_at', array('from' => $minDateCreated))
                                    ->addAttributeToFilter('updated_at', array('from' => $minDateUpdated))
                                    ->addAttributeToFilter('store_id', array('eq' => $shopId))
                                    ->setCurPage($page)
                                    ->setPageSize($limit);

        $data = array();

        foreach ($customerCollections as $customerCollection) {
            $buyer_addresses = array();

            foreach ($customerCollection->getAddressesCollection() as $address) {
                $buyer_addresses[] = $address->getData();
            }

            $data[] = array(
                'buyer'           => $customerCollection->getData(),
                'buyer_addresses' => $buyer_addresses,
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionsData($shopId, $page, $limit, $minDateCreated, $minDateUpdated)
    {
        $minDateCreated = date('Y-m-d H:i:s', $minDateCreated);
        $minDateUpdated = date('Y-m-d H:i:s', $minDateUpdated);

        $orderCollections = \Mage::getModel('sales/order')
                                 ->getCollection()
                                 ->addAttributeToFilter('store_id', array('eq' => $shopId))
                                 ->addAttributeToSort('created_at', 'desc')
                                 ->addAttributeToFilter('created_at', array('from' => $minDateCreated))
                                 ->addAttributeToFilter('updated_at', array('from' => $minDateUpdated))
                                 ->setPageSize($limit)
                                 ->setCurPage($page);

        $data = array();
        $currency = \Mage::app()->getStore($shopId)->getCurrentCurrencyCode();

        foreach ($orderCollections as $orderCollection) {
            $items = $orderCollection->getAllVisibleItems();
            $products = array();

            foreach ($items as $item) {
                $product_collection = $item->getProduct();
                $category_array     = array();
                $categoryIds        = $product_collection->getCategoryIds();

                if (!empty($categoryIds)) {
                    foreach ($categoryIds as $value) {
                        $category = \Mage::getModel('catalog/category')->load($value);
                        $category_array[] = $category->getData();
                    }
                }

                /** @var Varien_Data_Collection $gallery */
                $images  = array();
                $gallery = $product_collection->getMediaGalleryImages();

                foreach ($gallery->getItems() as $img) {
                    $images[] = $img->getData();
                }

                $products[] = array(
                    'transaction_product' => $item->getData(),
                    'product_detail'      => $product_collection->getData(),
                    'product_categories'  => $category_array,
                    'images'              => $images,
                    'product_url'         => $product_collection->getProductUrl(),
                    'currency'            => $currency,
                );
            }

            $buyer_addresses = array();
            $buyer = array();

            if ($orderCollection->getCustomerId()) {
                $buyer = \Mage::getModel('customer/customer')->load($orderCollection->getCustomerId());

                foreach ($buyer->getAddressesCollection() as $address) {
                    $buyer_addresses[] = $address->getData();
                }

                $buyer = $buyer->getData();
            }

            $shiping = array();

            if ($orderCollection->getShippingAddress()) {
                $shiping = $orderCollection->getShippingAddress()->getData();
            }

            $billing = array();

            if ($orderCollection->getBillingAddress()) {
                $billing = $orderCollection->getBillingAddress()->getData();
            }

            $data[] = array(
                'transaction'     => $orderCollection->getData(),
                'products'        => $products,
                'tracks'          => $orderCollection->getTracksCollection()->getData(),
                'shipping'        => $shiping,
                'billing'         => $billing,
                'buyer'           => $buyer,
                'buyer_addresses' => $buyer_addresses,
                'currency'        => $currency,
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsData($shopId, $page, $limit, $minDateCreated, $minDateUpdated)
    {
        $minDateCreated = date('Y-m-d H:i:s', $minDateCreated);
        $minDateUpdated = date('Y-m-d H:i:s', $minDateUpdated);

        $productCollections = \Mage::getModel('catalog/product')
                                   ->getCollection()
                                   ->addStoreFilter($shopId)
                                   ->addAttributeToFilter('created_at', array('from' => $minDateCreated))
                                   ->addAttributeToFilter('updated_at', array('from' => $minDateUpdated))
                                   ->addAttributeToFilter('status', array('eq' => 1))
                                   ->setPageSize($limit)
                                   ->setCurPage($page);

        $data = array();

        foreach ($productCollections as $productCollection) {
            $categoryArray = array();
            $categoryIds   = $productCollection->getCategoryIds();

            if (!empty($categoryIds)) {
                foreach ($categoryIds as $value) {
                    $category        = \Mage::getModel('catalog/category')->load($value);
                    $categoryArray[] = $category->getData();
                }
            }

            $productCollectionData = $productCollection->getData();

            $item = \Mage::app()
                         ->getLayout()
                         ->helper('catalog/product')
                         ->getProduct($productCollectionData['entity_id'], $shopId);

            /** @var Varien_Data_Collection $gallery */
            $images  = array();
            $gallery = $item->getMediaGalleryImages();

            foreach ($gallery->getItems() as $img) {
                $images[] = $img->getData();
            }

            $productData = $item->getData();
            $_request    = \Mage::getSingleton('tax/calculation')->getRateRequest();

            $_request->setProductClassId($productData['tax_class_id']);
            $currentTax = \Mage::getSingleton('tax/calculation')->getRate($_request);

            $data[] = array(
                'product_detail'     => $productData,
                'images'             => $images,
                'product_url'        => $item->getProductUrl(),
                'product_categories' => $categoryArray,
                'currency'           => \Mage::app()->getStore($shopId)->getCurrentCurrencyCode(),
                'tax_rate'           => $currentTax,
            );
        }

        return $data;
    }
}
