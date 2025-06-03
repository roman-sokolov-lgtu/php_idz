<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private $urlGenerator;
    private $tokenStorage;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->tokenStorage = $tokenStorage;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        // Если пользователь не аутентифицирован, перенаправляем на страницу входа
        if (null === $this->tokenStorage->getToken() || !$this->tokenStorage->getToken()->getUser()) {
            return new RedirectResponse($this->urlGenerator->generate('app_login'));
        }

        // Если пользователь аутентифицирован, но не имеет нужных прав,
        // перенаправляем на главную страницу с сообщением об ошибке
        $request->getSession()->getFlashBag()->add('error', 'У вас недостаточно прав для доступа к этой странице.');
        
        return new RedirectResponse($this->urlGenerator->generate('app_order_index'));
    }
} 