<?php
namespace Itembase\Psdk\Event;

/**
 * Interface EventListenerInterface
 *
 * In case if class want to handle events it must implement EventListenerInterface interface
 *
 * @package       Itembase\Psdk\Event
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
interface EventListenerInterface
{
    /**
     * Method-handler for event
     *
     * @param string $eventName Name of the event
     * @param mixed  $extraData Associated data which was sent by event trigger
     *
     * @return void
     */
    public function onEvent($eventName, $extraData);
}
