<?php

use App\Controller\Auth\LoginGoogleCallbackAction;
use App\Queue\AbstractProcessor;
use App\Queue\ProcessorInterface;
use App\Service\Settings;
use App\Service\SettingsInterface;
use App\Util\MailgunHandler;
use App\Util\SimpleLogger;
use App\Util\Twig\EmailExtension;
use App\Util\Twig\PriceFormattingExtension;
use App\Util\Twig\TranslationAdapterExtension;
use App\Util\Twig\ValidationResultExtension;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Enqueue\SimpleClient\SimpleClient;
use Google\Client;
use HaydenPierce\ClassFinder\ClassFinder;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Session\SessionInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\MoFileLoader;
use Symfony\Component\Translation\Translator;
use Twig\Error\LoaderError;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;

/**
 * Settings container
 *
 * @return Settings
 */
$container[SettingsInterface::class] = static function () {
    $settings = require __DIR__ . '/../config.php';

    return new Settings($settings);
};

/**
 * App container
 *
 * @param ContainerInterface $container
 *
 * @return App
 * @throws InvalidArgumentException
 */
$container[App::class] = static function (ContainerInterface $container) {
    AppFactory::setContainer($container);
    $app = AppFactory::create();

    $basepath = $container->get(SettingsInterface::class)->get('basepath');
    if ($basepath !== '/' && str_ends_with($basepath, '/')) {
        // otherwise, the paths in slim will be //route/sub-route
        throw new InvalidArgumentException('Basepath cannot end with / [basepath_error]');
    }

    // this will allow to have the basepath set per default to /
    if ($basepath !== '/') {
        $app->setBasePath($basepath);
    }

    return $app;
};

/**
 * Get the route parser
 *
 * @param App $app
 *
 * @return RouteParserInterface
 */
$container[RouteParserInterface::class] = function (App $app): RouteParserInterface {
    return $app->getRouteCollector()->getRouteParser();
};

/**
 * Response factory container
 *
 * @param App $app
 *
 * @return ResponseFactoryInterface
 */
$container[ResponseFactoryInterface::class] = static function (App $app) {
    return $app->getResponseFactory();
};

/**
 * Route collector container
 *
 * @param App $app
 *
 * @return RouteCollectorInterface
 */
$container[RouteCollectorInterface::class] = static function (App $app) {
    return $app->getRouteCollector();
};

/**
 * Session middleware container
 *
 * @param FilesystemInterface $filesystem
 * @param SettingsInterface   $settings
 *
 * @return SessionMiddleware
 * @throws FileNotFoundException
 */
$container[SessionMiddleware::class] = static function (FilesystemInterface $filesystem, SettingsInterface $settings) {
    $config = $settings->get(SessionInterface::class);
    $key = $filesystem->read($config['key']);

    return SessionMiddleware::fromSymmetricKeyDefaults(InMemory::plainText($key), $config['timeout']);
};

/**
 * Twig container.
 *
 * @param SettingsInterface $settings
 * @param App               $app
 * @param Translator        $translator
 *
 * @return Twig
 * @throws LoaderError
 */
$container[Twig::class] = static function (
    SettingsInterface $settings,
    App $app,
    Translator $translator
): Twig {
    $twigSettings = $settings->get('twig');
    $twig = new Twig(
        new FilesystemLoader($twigSettings['path']),
        [
            'cache' => $twigSettings['cache']['enabled'] ? $twigSettings['cache']['path'] : false,
            'auto_reload' => $twigSettings['autoReload'],
            'debug' => $twigSettings['debug'] ?: false,
        ]
    );
    $loader = $twig->getLoader();
    if ($loader instanceof FilesystemLoader) {
        $loader->addPath($settings->get('public'), 'templates');
    }
    $environment = $twig->getEnvironment();
    // Add relative base url
    $basePath = $app->getBasePath();
    $environment->addGlobal('base_path', $basePath . '/');

    // Add Twig extensions
    $translationExtension = new TranslationExtension($translator);
    $twig->addExtension($translationExtension);
    $twig->addExtension(new TranslationAdapterExtension());
    $twig->addExtension(new PriceFormattingExtension());
    $twig->addExtension(new StringExtension());
    $twig->addExtension(new ValidationResultExtension());
    $twig->addExtension(new EmailExtension($environment));

    return $twig;
};

/**
 * Translator container.
 *
 * @param SettingsInterface $settings
 *
 * @return Translator $translator
 */
$container[Translator::class] = static function (SettingsInterface $settings): Translator {
    $config = $settings->get(Translator::class);
    $translator = new Translator($config['locale']);
    $translator->addLoader('mo', new MoFileLoader());

    return $translator;
};

/**
 * Database connection container.
 *
 * @param SettingsInterface $settings
 *
 * @return Connection
 */
$container[Connection::class] = static function (SettingsInterface $settings): Connection {
    $config = $settings->get('db');
    $driver = new Mysql([
        'host' => $config['host'],
        'port' => $config['port'],
        'database' => $config['database'],
        'username' => $config['username'],
        'password' => $config['password'],
        'encoding' => $config['encoding'],
        'charset' => $config['charset'],
        'collation' => $config['collation'],
        'prefix' => '',
        'flags' => [
            // Enable exceptions
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Set default fetch mode
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ],
    ]);
    $driver->enableAutoQuoting(true);
    $db = new Connection([
        'driver' => $driver,
    ]);

    $db->connect();

    return $db;
};

/**
 * Provide a simple logger.
 *
 * THIS CLASS IS ONLY USED FOR SETTING THE LOGGER INTERFACE LOGGER
 * -> prevents circular dependency
 *
 * This logger does not contain the mailgun handler that sends emails.
 * Some Instances like SimpleClient or the MailerAdapterInterface should not use a logger that sends emails
 * -> call for emergency -> email -> uses mailer with logger that uses email -> circular dependency
 *
 * @param FilesystemInterface $filesystem
 * @param SettingsInterface   $settings
 *
 * @return SimpleLogger
 */
$container[SimpleLogger::class] = static function (FilesystemInterface $filesystem, SettingsInterface $settings) {
    // make sure that the file system is called and set up.
    // https://stackoverflow.com/questions/24271489/configure-php-monolog-to-log-to-amazon-s3-via-stream
    // https://stackoverflow.com/a/24272614/6805097
    // formerly it was $container->get(FileSystemInterface::class). This is to remove the container dependency
    $filesystem->has('/');

    $name = $settings->get('name');
    $path = $settings->get(LoggerInterface::class)['stream'];

    return SimpleLogger::factory($name, $path);
};

/**
 * Logger container
 *
 * @param SimpleLogger   $logger
 * @param MailgunHandler $mail
 *
 * @return Logger
 */
$container[LoggerInterface::class] = static function (
    SimpleLogger $logger,
    MailgunHandler $mail
) {
    $logger->pushHandler($mail);

    return $logger;
};

/**
 * Filesystem container.
 *
 * @param SettingsInterface $settings
 *
 * @return Filesystem
 */
$container[FileSystemInterface::class] = static function (SettingsInterface $settings) {
    $config = $settings->get(FileSystemInterface::class);

    return new FileSystem(new Local($config['root']));
};

/**
 * The simple client
 *
 * @see https://php-enqueue.github.io/laravel/quick_tour/#enqueue-simple-client
 *
 * @param SimpleLogger       $logger
 * @param SettingsInterface  $settings
 * @param ContainerInterface $container
 *
 * @return SimpleClient
 * @throws ReflectionException
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
$container[SimpleClient::class] = static function (
    SimpleLogger $logger,
    SettingsInterface $settings,
    ContainerInterface $container
) {
    $db = $settings->get('db');
    $dbConnector = 'mysql://' . $db['username'] . ':' . $db['password'] . '@' . $db['host'] . ':' . $db['port'] . '/' . $db['database'];
    $client = new SimpleClient($dbConnector, $logger);

    // automatically register commands
    $classes = ClassFinder::getClassesInNamespace('App\\Queue', ClassFinder::RECURSIVE_MODE);
    foreach ($classes as $class) {
        $reflect = new ReflectionClass($class);
        if ($reflect->implementsInterface(ProcessorInterface::class) && $reflect->isSubclassOf(AbstractProcessor::class)) {
            $logger->debug('Adding command to queue ' . $class);
            $callable = $container->get($class);

            // bind command needs to define a processor name
            // see https://github.com/php-enqueue/enqueue-dev/issues/807
            $client->bindCommand($class, $callable, $class);
        }
    }

    return $client;
};

/**
 * Create the google oauth client
 *
 * @return Client
 */
$container[Client::class] = static function (RouteParserInterface $routeParser, SettingsInterface $settings) {
    $config = $settings->get(Client::class);

    $host = $settings->get('baseUrl');
    $route = $routeParser->urlFor(LoginGoogleCallbackAction::NAME);
    $redirectUri = 'https://' . $host . $route;

    $client = new Client();
    $client->setApplicationName($config['application_name']);
    $client->setIncludeGrantedScopes(true);
    $client->setScopes($config['scopes']);
    $client->setAuthConfig($config['secret_file']);
    $client->setAccessType('offline'); // default
    $client->setPrompt('select_account consent'); // default
    $client->setRedirectUri($redirectUri);

    return $client;
};