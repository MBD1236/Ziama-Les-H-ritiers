<?php

namespace App\EventListener;

use App\Repository\ProduitRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class)]
class LoginSuccessListener
{
    public function __construct(
        private ProduitRepository $produitRepository,
        private RequestStack $requestStack
    ) {}

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $roles = $user->getRoles();

        // Notification uniquement pour l'admin
        if (!in_array('ROLE_ADMIN', $roles)) {
            return;
        }

        $nbRupture = $this->produitRepository->countProduitsEnRupture();



        if ($nbRupture > 0) {
            $request = $this->requestStack->getCurrentRequest();
            if ($request) {
                $request->getSession()->set(
                    'rupture_notification',
                    '⚠️ Attention : ' . $nbRupture . ' produit(s) sont en rupture ou stock faible !'
                );
            }
        }
    }
}
