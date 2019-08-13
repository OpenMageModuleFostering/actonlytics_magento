<?php
namespace Itembase\Psdk\Core;

/**
 * Class Logger
 *
 * Logger class is a simple implementation for logging.
 * It doesn't log anything to filesystem. Everything is stored inside the Logger instance in an array and will be
 * returned with response in case of exception or when development mode is enabled.
 *
 * Class contains the following log levels:
 * IB_LOG_ERR - represents error.
 * IB_LOG_INFO - represents information log entry.
 * IB_LOG_DEBUG - some message which might help during debugging.
 *
 * @package       Itembase\Psdk\Core
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class Logger
{
    // Logger log levels
    const IB_LOG_ERR   = 0;
    const IB_LOG_INFO  = 1;
    const IB_LOG_DEBUG = 2;

    /** @var array $messages */
    protected $messages;

    /** @var array $errorLevels */
    protected $errorLevels = array(
        self::IB_LOG_ERR   => 'error',
        self::IB_LOG_INFO  => 'info',
        self::IB_LOG_DEBUG => 'debug'
    );

    /**
     * Stores log entry in internal messages array. Log level and actual message must be provided.
     * $message can be instance of \Exception class.
     *
     * @param int               $level
     * @param string|\Exception $message
     */
    public function log($level, $message)
    {
        $msg = array(
            'level'   => $this->errorLevels[$level],
            'date'    => date('c'),
            'message' => $message
        );

        if ($message instanceof \Exception) {
            $msg['file']    = $message->getFile();
            $msg['line']    = $message->getLine();
            $msg['message'] = $message->getMessage();
        }

        $this->messages[$level][] = $msg;
    }

    /**
     * Method returns all messages from "minLevel" (including minLevel messages as well).
     *
     * Log levels (from lowest to highest):
     * - IB_LOG_DEBUG
     * - IB_LOG_INFO
     * - IB_LOG_ERR
     *
     * @param int $minLevel
     *
     * @return array
     */
    public function getMessages($minLevel = self::IB_LOG_ERR)
    {
        $messages = array();

        for ($level = $minLevel; $level >= self::IB_LOG_ERR; $level--) {
            if (empty($this->messages[$level])) {
                continue;
            }

            $messages = array_merge($messages, $this->messages[$level]);
        }

        return $messages;
    }
}
