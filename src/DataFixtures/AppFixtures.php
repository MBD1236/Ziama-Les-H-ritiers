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
        // Admin
        $admin = new User();
        $admin->setUsername('Administrateur')
             ->setEmail('admin@ziama.com')
             ->setPassword($this->hasher->hashPassword($admin, 'admin123'))
             ->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        // Employé 1
        $employe1 = new User();
        $employe1->setUsername('kallo')
                ->setEmail('kallo@ziama.com')
                ->setPassword($this->hasher->hashPassword($employe1, 'password123'))
                ->setRoles(['ROLE_EMPLOYE']);
        $manager->persist($employe1);


        $manager->flush();
    }
    
}
