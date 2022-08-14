<?php

namespace App\Util\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class EmailExtension
 */
class EmailExtension extends AbstractExtension
{
    private Environment $env;

    public function __construct(Environment $env)
    {
        $this->env = $env;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('paragraph', [$this, 'paragraph'], ['is_safe' => ['html']]),
            new TwigFunction('unordered_list', [$this, 'ulist'], ['is_safe' => ['html']]),
            new TwigFunction('ordered_list', [$this, 'olist'], ['is_safe' => ['html']]),
            new TwigFunction('button', [$this, 'button'], ['is_safe' => ['html']]),
        ];
    }

    public function paragraph(string $text)
    {
        return $this->env->render('Extensions/Email/paragraph.html.twig', ['text' => $text]);
    }

    public function ulist(array $items)
    {
        return $this->env->render('Extensions/Email/unordered-list.html.twig', ['items' => $items]);
    }

    public function olist(array $items)
    {
        return $this->env->render('Extensions/Email/ordered-list.html.twig', ['items' => $items]);
    }

    public function button(string $text, string $link)
    {
        return $this->env->render('Extensions/Email/button.html.twig', ['text' => $text, 'link' => $link]);
    }
}