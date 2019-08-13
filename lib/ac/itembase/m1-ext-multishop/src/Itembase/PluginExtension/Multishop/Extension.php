<?php
namespace Itembase\PluginExtension\Multishop;

use Itembase\Psdk\Container\ContainerAwareInterface;
use Itembase\Psdk\Container\ServiceContainer;
use Itembase\Psdk\Core;
use Itembase\Psdk\Extension\ExtensionInterface;

/**
 * Class Extension
 *
 * @package       Itembase\PluginExtension\Multishop
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2015 itembase GmbH
 */
class Extension implements ExtensionInterface, ContainerAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExtensionName()
    {
        return 'multishop_magento';
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ServiceContainer $container)
    {
        $container->bindService(
            Core::SERVICE_MULTISHOP, new Multishop($container)
        );
    }
}
