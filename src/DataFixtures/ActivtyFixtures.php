<?php
namespace App\DataFixtures;

use App\Entity\Activity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ActivityFixtures extends Fixture {
    public function load(ObjectManager $manager): void {
        for ($i = 1; $i <= 5; $i++) {
            $activity = new Activity();
            $activity->setTitle("Activité de test #{$i}");
            $activity->setSubtitle("Sous-titre {$i}");
            // $createdAt initialisé dans le constructeur
            $manager->persist($activity);
        }
        $manager->flush();
    }
}
