<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // User

        $users = [];

        $admin = new User();
        $admin->setPseudo('Adminidtrateur de TramStras')
              ->setEmail('adminTramStras@proton.me')
              ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
              ->setPassword('.123Abc456')
              ->setIsVerified('1')
              ->setStripeId('1'); 

        $users[] = $admin;
        $manager->persist($admin);
        $manager->flush();
    }
}
