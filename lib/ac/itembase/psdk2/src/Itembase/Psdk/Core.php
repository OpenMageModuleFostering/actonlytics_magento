<?php
/**
 * Itembase\Psdk is a core namespace of plugin PluginSdk. It's developed and maintained by itembase GmbH developers ONLY!
 * During plugin development based on that PluginSdk developer MUST NOT modify PluginSdk!
 */
namespace Itembase\Psdk;

use Itembase\Psdk\Container\ServiceContainer;
use Itembase\Psdk\Core\ExceptionHandler;
use Itembase\Psdk\Core\Logger;
use Itembase\Psdk\Core\OAuthClient;
use Itembase\Psdk\Event\EventDispatcher;
use Itembase\Psdk\Extension\ExtensionsLoader;
use Itembase\Psdk\Http\HttpClient;
use Itembase\Psdk\Http\HttpHandler;
use Itembase\PsdkExtension\Registration\PingHandler;

/**
 * Class Core
 *
 * Core class is an entry point for the PluginSdk. It instantiate necessary classes like EventDispatcher, ServiceContainer,
 * ExceptionHandler, HttpHandler etc, checks required services to be presented in ServiceContainer, triggering events
 * and finalize PluginSdk lifecycle.
 *
 * The sequence of Core  class methods calls to fulfill PluginSdk lifecycle:
 *
 * 1. Itembase\Psdk\Core object creation
 * 2. init() - initialization of PluginSdk and extensions
 * 3. run() - run business logic of the extensions
 * 4. done() - finishing work of the plugin
 *
 * All 3 methods must be executed, they need to be called in listed order or it's possible to use the following:
 *
 * 1. Itembase\Psdk\Core object creation
 * 2. runAll() - executes init() run() and done() methods in right order at once
 *
 * Methods are splitted to give plugin developer possibility to do necessary actions between these calls if necessary.
 * There might be need to postpone, for example, init() call because of some platform architectural processes.
 *
 * Core requires some configuration. Necessary constants which needs to be defined are listed and described in
 * config.php.dist file. Plugin developer should decide what is the best place for defining these constants in platform.
 * PluginSdk doesn't force to specify where that file is stored. It's developer duty to load/define these constants in
 * appropriate place/way in platform specific implementation.
 *
 * Class also define (if previously was not defined) the following constants:
 *
 * - ITEMBASE_PLUGIN_BUILD: in order to request plugin service for configuration of the specific plugin (version,
 * platform etc); (!) IMPORTANT. MUST be defined in platform plugin!
 *
 * - ITEMBASE_VENDOR_DIR: set the vendor where all extensions and actual SDK is located after composer install/update;
 * developer can change it in case of custom place of the vendor dir (class assume standart composer behaviour of
 * placing current file 6 level deep from the actual vendor root folder)
 *
 * (!) IMPORTANT. If you want to change values of these constants you must define them BEFORE instantiating
 * Itembase\Psdk\Core object!
 *
 * @package       Itembase\Psdk
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class Core
{
    const OAUTH_SERVER_URL  = 'https://accounts.itembase.com';
    const PLUGIN_SERVER_URL = 'https://services.itembase.com/connectors/plugin';

    const EVENT_INITIALIZED = 'initialized';
    const EVENT_EXCEPTION   = 'exception';
    const EVENT_RUN         = 'run';
    const EVENT_DONE        = 'done';

    // constant list of the core/mandatory services in service container
    const SERVICE_STORAGE      = "storage";
    const SERVICE_MULTISHOP    = "multishop";
    const SERVICE_LOGGER       = "logger";
    const SERVICE_OAUTH_CLIENT = "oauth_client";
    const SERVICE_PLATFORM     = "platform";

    /** @var EventDispatcher $eventDispatcher */
    protected $eventDispatcher;

    /** @var ServiceContainer $serviceContainer */
    protected $serviceContainer;

    /** @var HttpHandler $httpHandler */
    protected $httpHandler;

    /** @var ExceptionHandler $exceptionHandler */
    protected $exceptionHandler;

    /** @var Logger $logger */
    protected $logger;

    /** @var callable $prevExceptionHandler */
    protected $prevExceptionHandler;

    /** @var callable $prevErrorHandler */
    protected $prevErrorHandler;

    /**
     * Itembase constructor.
     */
    public function __construct()
    {
        $this->serviceContainer = new ServiceContainer();
        $this->logger           = new Logger();
        $this->eventDispatcher  = new EventDispatcher($this->serviceContainer);
        $this->httpHandler      = new HttpHandler($this->serviceContainer);
        $exceptionHandler       = new ExceptionHandler($this->logger);

        $this->serviceContainer->bindService(self::SERVICE_LOGGER, $this->logger);
        $this->serviceContainer->bindService(self::SERVICE_OAUTH_CLIENT, new OAuthClient());

        // initialize exception handler with response
        $this->httpHandler->initResponseAwareService($exceptionHandler);

        // add exception handler to listen exception event
        $this->eventDispatcher->addListener(self::EVENT_EXCEPTION, $exceptionHandler);

        // set exception/error handlers
        $this->prevErrorHandler     = set_error_handler(array($this, 'errorHandler'));
        $this->prevExceptionHandler = set_exception_handler(array($this, 'exceptionHandler'));
    }

    /**
     * Destructor method for Core.
     * Resetting exception and error handler
     */
    public function __destruct()
    {
        set_error_handler($this->prevErrorHandler);
        set_exception_handler($this->prevExceptionHandler);
    }

    /**
     * Initialization of PluginSdk and extensions
     *
     * @throws \Exception
     * @return Core
     */
    public function init()
    {
        try {
            if (!defined('ITEMBASE_PLUGIN_BUILD')) {
                throw new \Exception("No build key is set!");
            }

            if (!defined('ITEMBASE_PLUGIN_SERVICE')) {
                define('ITEMBASE_PLUGIN_SERVICE', self::PLUGIN_SERVER_URL);
            }

            try {
                $httpClient = new HttpClient();
                $response   = $httpClient->sendData(ITEMBASE_PLUGIN_SERVICE . '/builds/' . ITEMBASE_PLUGIN_BUILD);
                $response   = json_decode($response, true);

                if (empty($response)) {
                    throw new \Exception("No configuration data is available for the plugin!");
                }

                if (!empty($response['constants'])) {
                    foreach ($response['constants'] as $constant => $value) {
                        if (!defined($constant)) {
                            define('ITEMBASE_' . strtoupper($constant), $value);
                        }
                    }
                }

                if (!empty($response['client_id']) && !defined('ITEMBASE_BRANDED_ID')) {
                    define('ITEMBASE_BRANDED_ID', $response['client_id']);
                }
            } catch (\Exception $ex) {
                define('ITEMBASE_OAUTH_SERVER_URL', self::OAUTH_SERVER_URL);
            }

            if (!defined('ITEMBASE_VENDOR_DIR')) {
                // according to composer (and PSR-4) folder structure vendor dir is the 6th level from current file
                define('ITEMBASE_VENDOR_DIR', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
            }

            $extensionLoader = new ExtensionsLoader($this->serviceContainer);
            $extensionLoader->loadExtensions();

            $this->eventDispatcher->initAwareServices();

            $this->serviceContainer->verifyServices(array(
                array(self::SERVICE_STORAGE => 'Itembase\Psdk\Platform\StorageInterface'),
                array(self::SERVICE_MULTISHOP => 'Itembase\Psdk\Platform\MultiShop\MultishopAbstract'),
                array(self::SERVICE_PLATFORM => 'Itembase\Psdk\Platform\PlatformInterface')
            ));

            // adding ping service to handle ping request
            $pingService = new PingHandler();
            $this->serviceContainer->bindService($pingService->getExtensionName(), $pingService);

            // we are ready to go
            $this->eventDispatcher->dispatch(self::EVENT_INITIALIZED);
        } catch (\Exception $ex) {
            $this->logger->log(Logger::IB_LOG_ERR, $ex);

            $this->eventDispatcher->dispatch(self::EVENT_EXCEPTION, $ex);
            $this->httpHandler->sendResponses();
            exit;
        }

        return $this;
    }

    /**
     * Execute all handlers
     *
     * @return Core
     */
    public function run()
    {
        try {
            $this->httpHandler->initRequest();
            $this->httpHandler->initResponseAwareServices();

            $this->eventDispatcher->dispatch(self::EVENT_RUN);
            $this->httpHandler->initRequestAwareServices();
        } catch (\Exception $ex) {
            $this->eventDispatcher->dispatch(self::EVENT_EXCEPTION, $ex);
            $this->httpHandler->sendResponses();
            exit;
        }

        return $this;
    }

    /**
     * Finalizing work
     */
    public function done()
    {
        try {
            $this->eventDispatcher->dispatch(self::EVENT_DONE);
            $this->httpHandler->sendResponses();

            // restore previous error/exception handler
            set_error_handler($this->prevErrorHandler);
            set_exception_handler($this->prevExceptionHandler);
        } catch (\Exception $ex) {
            $this->eventDispatcher->dispatch(self::EVENT_EXCEPTION, $ex);
            $this->httpHandler->sendResponses();
            exit;
        }
    }

    /**
     * Executes all method chain at once
     */
    public function runAll()
    {
        $this->init()->run()->done();
    }

    /**
     * @return ServiceContainer
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Custom error handler. In case of $errno == E_NOTICE || $errno == E_WARNING || $errno == E_USER_NOTICE ||
     * $errno == E_USER_WARNING - IB_LOG_DEBUG message will be wrote into the log. Otherwise exception event will be
     * dispatched and execution will be stopped.
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     *
     * @return bool
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $message = sprintf(
            "Error handler got message (%s) in file (%s) on line (%d)",
            $errstr,
            $errfile,
            $errline
        );

        if ($errno == E_NOTICE || $errno == E_WARNING || $errno == E_USER_NOTICE || $errno == E_USER_WARNING) {
            $this->logger->log(Logger::IB_LOG_DEBUG, $message);

            return true;
        }

        $this->eventDispatcher->dispatch(self::EVENT_EXCEPTION, new \Exception($message, $errno));
        $this->httpHandler->sendResponses();
        exit;
    }

    /**
     * Custom exception handler function in case somewhere try/catch block was missed.
     *
     * @param string|\Exception $exception
     */
    public function exceptionHandler($exception)
    {
        $this->eventDispatcher->dispatch(self::EVENT_EXCEPTION, $exception);
        $this->httpHandler->sendResponses();

        exit;
    }
}
