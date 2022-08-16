<?php

namespace App\Middleware;

use App\Service\Encoder\JSONEncoder;
use App\Type\Language;
use App\Util\ArrayReader;
use Moment\Moment;
use Moment\MomentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

/**
 * Class LanguageMiddleware.
 */
class LanguageMiddleware implements MiddlewareInterface
{
    private Translator $translator;
    private array $momentLocaleAliases;
    private JSONEncoder $encoder;
    private Twig $twig;

    /**
     * Constructor
     *
     * @param Translator  $translator
     * @param JSONEncoder $encoder
     * @param Twig        $twig
     */
    public function __construct(Translator $translator, JSONEncoder $encoder, Twig $twig)
    {
        $this->translator = $translator;
        $this->encoder = $encoder;
        $this->momentLocaleAliases = [
            'de_CH' => 'de_DE',
            'fr_CH' => 'fr_FR',
        ];
        $this->twig = $twig;
    }

    /**
     * The called method.
     *
     * This method will be invoked if a middleware is executed
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws MomentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        if (empty($route)) {
            return $handler->handle($request);
        }

        $language = $this->extractLanguage($request);
        $parsed = $this->parseLanguage($language);
        $this->twig->getEnvironment()->addGlobal('language', $parsed);

        $momentLocale = $parsed;
        if (isset($this->momentLocaleAliases[$parsed])) {
            $momentLocale = $this->momentLocaleAliases[$parsed];
        }
        Moment::setLocale($momentLocale);
        $this->setLocale($parsed);

        $request = $request->withAttribute('language', $parsed);

        $response = $handler->handle($request);

        $response = $response->withAddedHeader('X-Your-App-Language', $parsed);
        $contentType = $response->getHeader('Content-Type');
        if (!empty($contentType) && $contentType[0] === 'application/json') {
            $json = $response->getBody();
            $data = json_decode($json->__toString(), true);
            $data['language'] = $parsed;
            $response = $this->encoder->encode($response, $data);
        }

        return $response;
    }

    /**
     * Extract the language.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    protected function extractLanguage(ServerRequestInterface $request): string
    {
        $language = null;

        if (empty($language)) {
            $language = (new ArrayReader($request->getQueryParams()))->findString('lang');
            if (!empty($language)) {
                $this->twig->getEnvironment()->addGlobal('language_selected', true);
            }
        }

        if (empty($language) && $request->hasHeader('X-Language')) {
            $language = $request->getHeader('X-Language')[0];
            if (!empty($language)) {
                $this->twig->getEnvironment()->addGlobal('language_selected', true);
            }
        }

        $header = $request->getHeader('Accept-Language');
        if (empty($language) && !empty($header) && isset($header[0])) {
            $language = explode(',', $header[0])[0];
        }

        if (empty($language)) {
            $language = Language::fromString('default');
        }

        return $language;
    }

    /**
     * Verify if the language is allowed.
     *
     * @param string $language
     *
     * @return string The parsed language
     */
    protected function parseLanguage(
        string $language
    ): string {
        if (Language::fromString($language, true)) {
            return Language::fromString($language);
        }
        $simplified = explode('-', $language)[0];
        if (Language::fromString($simplified, true)) {
            return Language::fromString($simplified);
        }

        $oversimplified = explode('_', $simplified)[0];

        return Language::fromString($oversimplified);
    }

    /**
     * Set the locale.
     *
     * @param string $language
     */
    protected function setLocale(string $language): void
    {
        $locale = Language::fromString($language);

        $resource = __DIR__ . '/../../resources/locale/' . $locale . '_messages.mo';
        $this->translator->setLocale($locale);
        $this->translator->setFallbackLocales([Language::EN_GB]);
        $this->translator->addResource('mo', $resource, $locale);
    }
}
