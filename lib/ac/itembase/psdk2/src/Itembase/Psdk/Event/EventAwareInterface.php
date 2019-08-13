<?php
namespace Itembase\Psdk\Event;

/**
 * Interface EventAwareInterface
 *
 * If some instance requires EventDispatcher object - it needs to implement EventAwareInterface interface.
 * Using 'setEventDispatcher()' setter EventDispatcher will be injected into instance.
 *
 * @package       Itembase\Psdk\Event
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
interface EventAwareInterface
{
    /**
     * Setter for injecting EventDispatcher object.
     *
     * @param EventDispatcher $dispatcher
     *
     * @return void
     */
    public function setEventDispatcher(EventDispatcher $dispatcher);
}
