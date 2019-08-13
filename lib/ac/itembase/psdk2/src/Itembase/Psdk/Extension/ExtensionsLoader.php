<?php
namespace Itembase\Psdk\Extension;

use Itembase\Psdk\Container\ServiceContainer;

/**
 * Class ExtensionsLoader
 *
 * Class is responsible for loading SDK and plugin extensions. Used by Itembase\Psdk\Core class and not exposed in
 * ServiceContainer instance.
 *
 * Plugin developer should not use it directly.
 *
 * Requires defined constant - ITEMBASE_VENDOR_DIR. By default Core class set it to 6 levels previous from it's position.
 * In case it must be changed - please define correct path for ITEMBASE_VENDOR_DIR before calling init() of the Core
 * class.
 *
 * Class requires Composer autoloader class map to be available in <vendor-dir>/composer/autoload_classmap.php
 *
 * In order to be loaded extension must have Extension class and it must be in Itembase\PsdkExtension or
 * Itembase\PluginExtension namespace and it should implement ExtensionInterface interface.
 *
 * Keep in mind that ExtensionLoading is checking if provided name is already registered in ServiceContainer. If that is
 * the case - exception will be thrown.
 *
 * Please check out the loadExtension diagram to see how ExtensionLoader is loading extensions.
 *
 * @package       Itembase\Psdk\Extension
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class ExtensionsLoader
{
    /** @var ServiceContainer $serviceContainer */
    protected $serviceContainer;

    /**
     * ExtensionsLoader constructor.
     *
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainer $container)
    {
        $this->serviceContainer = $container;
    }

    /**
     * Main logic of the loading extensions is implemented in that method and used internally by loadExtensions method.
     * loadExtensions method call once that method with SDK extensions folder path and then second time with plugin
     * extensions folder path.
     *
     * Method throws exception:
     * - if ITEMBASE_VENDOR_DIR is not defined
     * - if composer autoload classmap was not found or it's array is empty
     * - if extension name already exists in ServiceContainer
     *
     * @throws \Exception
     */
    public function loadExtensions()
    {
        if (!defined('ITEMBASE_VENDOR_DIR')) {
            throw new \Exception("Extensions dir path was not defined");
        }

        $classMapFilePath = ITEMBASE_VENDOR_DIR . '/composer/autoload_classmap.php';
        if (!file_exists($classMapFilePath)) {
            throw new \Exception("No classmap file found! Maybe composer is not used?");
        }

        $classMap = include $classMapFilePath;
        if (empty($classMap)) {
            throw new \Exception("Classmap is empty!");
        }

        foreach ($classMap as $className => $path) {
            if (false === strpos($className, "Itembase\\PsdkExtension")
                &&
                false === strpos($className, "Itembase\\PluginExtension")) {
                continue;
            }

            $implements = class_implements($className);
            if (false === $implements) {
                continue;
            }

            if (!in_array("Itembase\\Psdk\\Extension\\ExtensionInterface", array_keys($implements))) {
                continue;
            }

            $ext = new $className();
            if (!$ext instanceof ExtensionInterface) {
                continue;
            }

            if ($this->serviceContainer->hasService($ext->getExtensionName())) {
                throw new \Exception('Extension with the tag ' . $ext->getExtensionName() . ' already exists in container!');
            }

            $this->serviceContainer->bindService($ext->getExtensionName(), $ext);
        }
    }
}
