<?php
namespace Itembase\Psdk\Event;

use Itembase\Psdk\Container\ServiceContainer;

/**
 * Class EventDispatcher
 *
 * Class represents implementation of the event dispatcher functionality. An instance of the dispatcher is created by
 * Itembase\Psdk\Core class.
 *
 * Note: EventDispatcher instance is not available in ServiceContainer.
 *
 * @package       Itembase\Psdk\Event
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class EventDispatcher
{
    /** @var ServiceContainer $serviceContainer */
    protected $serviceContainer;

    /** @var array $events */
    protected $events;

    /**
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainer $container)
    {
        $this->serviceContainer = $container;
    }

    /**
     * Method allows to add event listener instance for  event. Listener must implement EventListenerInterface interface.
     *
     * @param string                 $event
     * @param EventListenerInterface $listener
     */
    public function addListener($event, EventListenerInterface $listener)
    {
        $this->events[$event][] = $listener;
    }

    /**
     * dispatch() method triggers event execution which means EventDispatcher will call all event listeners for the
     * specific "event". It's possible to pass additional data during event trigger using "extra" parameter.
     *
     * Event listeners will be called in the order they were added.
     *
     * @param string $event
     * @param mixed  $extra
     */
    public function dispatch($event, $extra = null)
    {
        if (empty($this->events[$event])) {
            return;
        }

        /** @var EventListenerInterface $listener */
        foreach ($this->events[$event] as $listener) {
            $listener->onEvent($event, $extra);
        }
    }

    /**
     * It's a helper method which is going through all instances inside ServerContainer instance and for those which
     * implements EventAwareInterface interface injecting instance of EventDispatcher.
     *
     * Is used by Itembase\Psdk\Core class during initialization. Ideally should not be used by developers directly.
     *
     * @return void
     */
    public function initAwareServices()
    {
        $tags = $this->serviceContainer->listServices();

        foreach ($tags as $tag) {
            $ext = $this->serviceContainer->getService($tag);

            if ($ext instanceof EventAwareInterface) {
                $ext->setEventDispatcher($this);
            }
        }
    }
}
