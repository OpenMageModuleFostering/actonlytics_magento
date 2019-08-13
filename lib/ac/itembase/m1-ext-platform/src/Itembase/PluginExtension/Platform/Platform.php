<?php
namespace Itembase\PluginExtension\Platform;

use Itembase\Psdk\Platform\PlatformInterface;

/**
 * Class Platform
 *
 * @package       Itembase\PluginExtension\Platform
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2015 itembase GmbH
 */
class Platform implements PlatformInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return PlatformInterface::MAGENTO;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        $m = new \Mage();
        return $m->getVersion();
    }
}
