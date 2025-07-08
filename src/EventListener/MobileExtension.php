<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class MobileExtension extends AbstractExtension implements GlobalsInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getGlobals(): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return ['isMobile' => false];
        }

        $ua = $request->headers->get('User-Agent', '');
        $isMobile = preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $ua) === 1;

        return ['isMobile' => $isMobile];
    }
}
