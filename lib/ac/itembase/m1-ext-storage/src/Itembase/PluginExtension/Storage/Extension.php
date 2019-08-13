<?php
namespace Itembase\PluginExtension\Storage;

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
        $container->bindService(Core::SERVICE_STORAGE, new Storage());
    }

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return 'storage_extension';
    }
}
