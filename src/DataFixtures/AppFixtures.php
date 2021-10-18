<?php

namespace App\DataFixtures;

use App\Entity\Feed;
use App\Entity\FeedContent;
use App\Entity\User;
use App\Repository\FeedContentRepository;
use App\Repository\FeedRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Component\String\u;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    private $slugger;
    private $feed;
    private $feedContent;

    public function __construct(SluggerInterface $slugger, UserPasswordHasherInterface $passwordHasher, FeedRepository $feed, FeedContentRepository $feedContent)
    {
        $this->passwordHasher = $passwordHasher;
        $this->slugger = $slugger;
        $this->feed = $feed;
        $this->feedContent = $feedContent;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadFeeds($manager);
    }

    private function loadUsers(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$fullname, $username, $password, $email, $roles]) {
            $user = new User();
            $user->setFullName($fullname);
            $user->setUsername($username);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setEmail($email);
            $user->setRoles($roles);

            $manager->persist($user);
            $this->addReference($username, $user);
        }

        $manager->flush();
    }

    private function loadFeeds(ObjectManager $manager): void
    {
        $feedName = "Dummy Feed";
        $feedSlug = $this->slugger->slug($feedName)->lower();

        $feed = $this->feed->findOneBy(["slug" => $feedSlug]);
        if(!$feed){
            $feed = new feed();
            $feed->setName("dummy feed");
            $feed->setSlug($feedSlug);
            $feed->setUrl("");
            $feed->setStatus("active");
            $feed->setIsDummy(1);

            $manager->persist($feed);
        }

        foreach ($this->getPhrases() as $i => $title) {
            $slug = $this->slugger->slug($title)->lower();
            $feedContent = $this->feedContent->findOneBy(["slug" => $slug]);
            if(!$feedContent){
                $feedContent = new FeedContent();
            }

            $feedContent->setFeed($feed);
            $feedContent->setTitle($title);
            $feedContent->setSlug($slug);
            $feedContent->setContent($this->getPostContent());
            $feedContent->setUrl("https://www.axelerant.com/blog/using-drupal-api-create-cross-platform-menu");
            $feedContent->setPublishedAt(date("Y-m-d h:i:s"));
            $feedContent->setGuid(uniqid());
            $feedContent->setStatus('active');
            $feedContent->setAuthor('Nikunj Bambhroliya');
            $feedContent->setCreatedAt(new \DateTime());

            $manager->persist($feedContent);
        }

        $manager->flush();
    }

    private function getUserData(): array
    {
        return [
            // $userData = [$fullname, $username, $password, $email, $roles];
            ['Jane Doe', 'jane_admin', 'kitten', 'jane_admin@symfony.com', ['ROLE_ADMIN']],
            ['Tom Doe', 'tom_admin', 'kitten', 'tom_admin@symfony.com', ['ROLE_ADMIN']],
            ['John Doe', 'john_user', 'kitten', 'john_user@symfony.com', ['ROLE_USER']],
        ];
    }

    private function getPhrases(): array
    {
        return [
            'Lorem ipsum dolor sit amet consectetur adipiscing elit',
            'Pellentesque vitae velit ex',
            'Mauris dapibus risus quis suscipit vulputate',
            'Eros diam egestas libero eu vulputate risus',
            'In hac habitasse platea dictumst',
            'Morbi tempus commodo mattis',
            'Ut suscipit posuere justo at vulputate',
            'Ut eleifend mauris et risus ultrices egestas',
            'Aliquam sodales odio id eleifend tristique',
            'Urna nisl sollicitudin id varius orci quam id turpis',
            'Nulla porta lobortis ligula vel egestas',
            'Curabitur aliquam euismod dolor non ornare',
            'Sed varius a risus eget aliquam',
            'Nunc viverra elit ac laoreet suscipit',
            'Pellentesque et sapien pulvinar consectetur',
            'Ubi est barbatus nix',
            'Abnobas sunt hilotaes de placidus vita',
            'Ubi est audax amicitia',
            'Eposs sunt solems de superbus fortis',
            'Vae humani generis',
            'Diatrias tolerare tanquam noster caesium',
            'Teres talis saepe tractare de camerarius flavum sensorem',
            'Silva de secundus galatae demitto quadra',
            'Sunt accentores vitare salvus flavum parses',
            'Potus sensim ad ferox abnoba',
            'Sunt seculaes transferre talis camerarius fluctuies',
            'Era brevis ratione est',
            'Sunt torquises imitari velox mirabilis medicinaes',
            'Mineralis persuadere omnes finises desiderium',
            'Bassus fatalis classiss virtualiter transferre de flavum',
        ];
    }

    private function getPostContent(): string
    {
        return <<<'MARKDOWN'
Lorem ipsum dolor sit amet consectetur adipisicing elit, sed do eiusmod tempor
incididunt ut labore et **dolore magna aliqua**: Duis aute irure dolor in
reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia
deserunt mollit anim id est laborum.
MARKDOWN;
    }
}
