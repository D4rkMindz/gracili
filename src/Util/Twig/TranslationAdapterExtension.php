<?php

namespace App\Util\Twig;


use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class TranslationAdapterExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('__', [$this, 'translate']),
        ];
    }

    /**
     * Translate
     *
     * @param string $message
     *
     * @return Markup
     */
    public function translate(string $message): Markup
    {
        $ctx = array_slice(func_get_args(), 1);
        if (!empty($ctx) && !empty($ctx[0])) {
            $translated = __($message, $ctx[0]);
        } else {
            $translated = __($message);
        }

        return new Markup(nl2br($translated), null);
    }
}
