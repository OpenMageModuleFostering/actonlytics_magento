<?php
namespace Itembase\PluginExtension\Platform;

use Itembase\Psdk\Container\ContainerAwareInterface;
use Itembase\Psdk\Container\ServiceContainer;
use Itembase\Psdk\Core;
use Itembase\Psdk\Extension\ExtensionInterface;

/**
 * Class Extension
 *
 * @package       Itembase\PluginExtension\Storage
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2015 itembase GmbH
 */
class Extension implements ExtensionInterface, ContainerAwareInterface
{
    /**
     * @param ServiceContainer $container
     */
    public function setContainer(ServiceContainer $container)
    {
        $container->bindService(Core::SERVICE_PLATFORM, new Platform());
    }

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return 'platform_extension';
    }
}
