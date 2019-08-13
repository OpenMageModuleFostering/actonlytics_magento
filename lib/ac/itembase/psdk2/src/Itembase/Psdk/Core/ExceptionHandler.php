<?php
namespace Itembase\Psdk\Core;

use Itembase\Psdk\Event\EventListenerInterface;
use Itembase\Psdk\Http\Response;
use Itembase\Psdk\Http\ResponseAwareInterface;

/**
 * Class ExceptionHandler
 *
 * ExceptionHandler class is responsible to react when exception happened during SDK based plugin work cycle.
 * It's used internally by SDK and can not be re-defined.
 * Doesn't accessible via ServiceContainer.
 *
 * @package       Itembase\Psdk\Core
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class ExceptionHandler implements EventListenerInterface, ResponseAwareInterface
{
    /** @var Response $response */
    protected $response;

    /** @var Logger $logger */
    protected $logger;

    /**
     * ExceptionHandler constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseType()
    {
        return 'exception';
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseVersion()
    {
        return '1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestVersion()
    {
        return '1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function onEvent($eventName, $exception)
    {
        $this->response->setData(array(
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'message' => $exception->getMessage(),
            'logs'    => $this->logger->getMessages(Logger::IB_LOG_DEBUG)
        ));
    }
}
