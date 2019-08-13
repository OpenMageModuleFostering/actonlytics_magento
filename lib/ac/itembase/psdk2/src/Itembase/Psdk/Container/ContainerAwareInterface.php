<?php
namespace Itembase\Psdk\Container;

/**
 * Interface ContainerAwareInterface
 *
 * If some service requires service container object - he need to implement ContainerAwareInterface interface.
 * Using 'setContainer()' setter ServiceContainer will be injected to class.
 *
 * @package       Itembase\Psdk\Container
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
interface ContainerAwareInterface
{
    /**
     * Setter-based injection of the ServiceContainer object.
     *
     * @param ServiceContainer $container
     *
     * @return void
     */
    public function setContainer(ServiceContainer $container);
}
