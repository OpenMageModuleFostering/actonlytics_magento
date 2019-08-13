<?php

namespace Itembase\Psdk\Platform;

/**
 * Interface PlatformInterface
 *
 * PlatformInterface is an interface to abstract platform information
 * It's a necessary to implement that interface, because SDK core is rely on it to get detailed information about
 * platform
 *
 * It should be available in ServiceContainer by tag "platform".
 *
 * It's important to use predefined list of platform names, because backend service is depending on correct platform
 * name
 *
 * @package       Itembase\Psdk\Platform
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
interface PlatformInterface
{
    const MAGENTO       = "magento";
    const PRESTASHOP    = "prestashop";
    const WOOCOMMERCE   = "woocommerce";
    const VIRTUEMART    = "virtuemart";
    const OPENCART      = "opencart";
    const GAMBIO        = "gambio";
    const SHOPWARE      = "shopware";
    const SPREECOMMERCE = "spreecommerce";

    /**
     * Method should return the name of the platform where plugin is running.
     * It should be one of the provided platform names constants.
     *
     * If necessary one is missing - please contact itembase developers.
     *
     * @return string
     */
    public function getName();

    /**
     * Method should return current version of the platform where plugin is running.
     *
     * @return string
     */
    public function getVersion();
}
