<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?RedirectResponse
    {
        // Récupérer l'utilisateur
        $user = $token->getUser();

        // Logique de redirection en fonction du rôle
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $targetPath = $this->urlGenerator->generate('app_admin_accueil_index');
        } elseif (in_array('ROLE_EMPLOYE', $user->getRoles())) {
            $targetPath = $this->urlGenerator->generate('app_admin_vente_index');
        } else {
            $targetPath = $this->urlGenerator->generate('app_login');
        }

        return new RedirectResponse($targetPath);
    }
}
