<?php
namespace Itembase\Psdk\Container;

/**
 * Class ServiceContainer
 *
 * ServiceContainer class can be named as repository named (tagged) services. It allows to share class instances based
 * on tags which implements passive dependency injection - developer should take care of getting necessary service from
 * the container.
 *
 * @package       Itembase\Psdk\Container
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class ServiceContainer
{
    /** @var array $container */
    private $container;

    /**
     * ItembaseServiceContainer constructor.
     */
    public function __construct()
    {
        $this->container["service_container"] = $this;
    }

    /**
     * Adding/replacing some instance object with specific tag/alias
     *
     * @param string $tag
     * @param mixed  $object
     *
     * @throws \Exception
     */
    public function bindService($tag, $object)
    {
        // checking if someone is adding container aware object
        if (is_object($object) && $object instanceof ContainerAwareInterface) {
            $object->setContainer($this);
        }

        $this->container[$tag] = $object;
    }

    /**
     * Check if tag/alias exists in container
     *
     * @param string $tag
     *
     * @return bool
     */
    public function hasService($tag)
    {
        if (empty($this->container[$tag])) {
            return false;
        }

        return true;
    }

    /**
     * Extended and stricter version of hasService() method. Verifies if in container exists specific services (by tags)
     * and (if needed) they are instances of some specific class/interface.
     *
     * $tags should always be array, otherwise method will throw \Exception. Array can consist of strings (tags names)
     * or contains assoc arrays what will mean for verifyServices() to check specific service if it's instance of
     * specified class or interface. So $tags can be:
     *
     * array(
     *    'logger',
     *    array('storage' => 'Itembase\Psdk\StorageInterface')
     * )
     *
     * If one of the service wasn't found or doesn't implement necessary interface or is not instance of some class -
     * \Exception will thrown. Otherwise nothing will happened.
     *
     * @param string|array $tags
     *
     * @throws \Exception
     *
     * @return void
     */
    public function verifyServices($tags)
    {
        if (empty($tags)) {
            return;
        }

        if (!is_array($tags)) {
            throw new \Exception("Dependecy tags should be provided as an array!");
        }

        foreach ($tags as $tag) {
            if (is_string($tag)) {
                if (!$this->hasService($tag)) {
                    throw new \Exception("Dependency service " . $tag . " was not found!");
                }
            }

            if (is_array($tag)) {
                list($tagName, $interface) = each($tag);

                if (!interface_exists($interface) && !class_exists($interface)) {
                    throw new \Exception(
                        "Interface/class " . $interface . " cannot be found to validate service " . $tagName . "!"
                    );
                }

                if (!$this->hasService($tagName)) {
                    throw new \Exception("Dependency service " . $tagName . " was not found!");
                }

                $service = $this->getService($tagName);

                if (!$service instanceof $interface) {
                    throw new \Exception("Dependency service " . $tagName . " (class: " . get_class($service)
                        . ") is not implementing " . $interface);
                }
            }
        }
    }

    /**
     * Returns instance by tag
     *
     * @param string $tag
     *
     * @return null|mixed
     */
    public function getService($tag)
    {
        if ($this->hasService($tag)) {
            return $this->container[$tag];
        }

        return null;
    }

    /**
     * Get list of tags in container
     *
     * @return array
     */
    public function listServices()
    {
        return array_keys($this->container);
    }
}
