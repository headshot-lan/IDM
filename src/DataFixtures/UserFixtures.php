<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use NumberToWords\NumberToWords;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly PasswordHasherFactoryInterface $hasherFactory
    )
    {}

    public function load(ObjectManager $manager): void
    {
        $hasher = $this->hasherFactory->getPasswordHasher(User::class);

        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('de');

        // create 20 Users
        for ($i = 0; $i < 20; ++$i) {
            $user = new User();
            $user->setNickname('User '.$i);
            $user->setEmail('user'.$i.'@localhost.local');
            $user->setFirstname('User');
            $user->setSurname(ucfirst($numberTransformer->toWords($i)));
            $user->setUuid(Uuid::fromInteger(strval($i)));
            $user->setStatus(1);
            $user->setPassword($hasher->hash('user'.$i));
            $user->setEmailConfirmed($i < 10 || 1 != $i % 4);
            $user->setInfoMails(1 == ($i + 1) % 5);
            $user->setPersonalDataLocked(1 == ($i + 1) % 7);
            $user->setPersonalDataConfirmed(1 != ($i + 2) % 3);
            $user->setIsSuperadmin(false);
            $user->setGender(1 == $i % 3 ? 'f' : 'm');
            if ($i < 10){
                $user->setBirthdate((new DateTimeImmutable("midnight"))->sub(DateInterval::createFromDateString(($i+10).' years')));
            }
            $this->addReference('user-'.$i, $user);
            $manager->persist($user);
        }

        $ghost = new User();
        $ghost->setUuid(Uuid::fromInteger(42));
        $ghost->setNickname('DeletedUser');
        $ghost->setFirstname('Deleted');
        $ghost->setSurname('User');
        $ghost->setStatus(-1);
        $ghost->setEmail('ghost@localhost.local');
        $ghost->setPassword($hasher->hash('ghost'));
        $ghost->setEmailConfirmed(1);
        $ghost->setInfoMails(true);
        $ghost->setPersonalDataLocked(false);
        $ghost->setPersonalDataConfirmed(false);
        $this->addReference('user-ghost', $ghost);
        $manager->persist($ghost);

        // create admin user1
        $admin = new User();
        $admin->setNickname('Admin');
        $admin->setFirstname('Ali');
        $admin->setSurname('Admin');
        $admin->setEmail('admin@localhost.local');
        $admin->setStatus(1);
        $admin->setPassword($hasher->hash('admin'));
        $admin->setEmailConfirmed(true);
        $admin->setInfoMails(false);
        $admin->setPersonalDataLocked(false);
        $admin->setPersonalDataConfirmed(false);
        $admin->setIsSuperadmin(true);
        $manager->persist($admin);
        $this->addReference('user-admin', $admin);

        $manager->flush();
    }
}
