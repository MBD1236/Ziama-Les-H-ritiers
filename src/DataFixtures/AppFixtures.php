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
        $admin
            ->setLastName('Kallo')
            ->setFirstName('Mohamed')
             ->setUsername('Administrateur')
             ->setEmail('admin@ziama.com')
             ->setPassword($this->hasher->hashPassword($admin, 'admin123'))
             ->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);
        $manager->flush();
    }
    
}
