<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('Administrateur')
             ->setEmail('admin@groupemandela.com')
             ->setPassword($this->hasher->hashPassword($user, 'admin'))
             ->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);
        $manager->flush();
    }
    
}
