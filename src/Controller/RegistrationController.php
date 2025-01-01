<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{

    #[Route('/admin/employes/index', name: 'app_employe_index')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        return $this->render('registration/index.html.twig', [
            'employes' => $userRepository->paginateUsers($page)
        ]);
    }

    #[Route('/admin/employe/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $type = $form->get('type')->getData();
            if($type == 'Administrateur'){
                $user->setRoles(["ROLE_ADMIN"]);
            }elseif($type == 'Producteur'){
                $user->setRoles(["ROLE_PRODUCTEUR"]);
            }elseif($type == 'Livreur'){
                $user->setRoles(["ROLE_LIVREUR"]);
            }elseif($type == 'Comptable'){
                $user->setRoles(["ROLE_COMPTABLE"]);
            }

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            $this->addFlash('success', 'L\'employé a été bien enregistré.');
            return $this->redirectToRoute('app_employe_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/admin/employe/{id}/register', name: 'app_register_edit')]
    public function edit(User $user, Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $type = $form->get('type')->getData();
            if($type == 'Administrateur'){
                $user->setRoles(["ROLE_ADMIN"]);
            }elseif($type == 'Producteur'){
                $user->setRoles(["ROLE_PRODUCTEUR"]);
            }elseif($type == 'Livreur'){
                $user->setRoles(["ROLE_LIVREUR"]);
            }elseif($type == 'Comptable'){
                $user->setRoles(["ROLE_COMPTABLE"]);
            }

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->flush();

            // do anything else you need here, like send an email

            $this->addFlash('success', 'L\'employé a été bien modifié.');
            return $this->redirectToRoute('app_employe_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_register_delete', methods:['DELETE'])]
    public function delete(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'L\'employé a bien été supprimée.');
        return $this->redirectToRoute('app_employe_index');
    }

    #[Route('/search', name: 'app_register_search', methods:['GET'])]
    public function search(Request $request, UserRepository $userRepository): Response
    {
        $query = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        if ($query or $query == '')
        {
            $employes = $userRepository->paginateWithSearch($query, $page);
        }else{
            $emoloyes = [];
        }
        return $this->render('registration/index.html.twig', [
            'employes' => $employes,
            'query' => $query
        ]);
    }
}
