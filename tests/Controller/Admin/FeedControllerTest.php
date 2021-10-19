<?php
/*
 * This file is part of unit testing of feed crud
 *
 * @au Nikunj Bambhroliya <nikunjpatel190@gmail.com>
 */

namespace App\Tests\Controller\Admin;

use App\Repository\FeedRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test for the controllers defined inside the FeedController used
 * for managing the feed URLs in the backend.
 *
 *
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class FeedControllerTest extends WebTestCase
{
    /**
     * @dataProvider getUrlsForRegularUsers
     */
    public function testAccessDeniedForRegularUsers(string $httpMethod, string $url): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'john_user',
            'PHP_AUTH_PW' => 'kitten',
        ]);

        $client->request($httpMethod, $url);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function getUrlsForRegularUsers(): ?\Generator
    {
        yield ['GET', '/en/admin/feed/'];
        yield ['GET', '/en/admin/feed/1/edit'];
        yield ['GET', '/en/admin/feed/1/delete'];
    }

    public function testAdminBackendHomePage(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $client->request('GET', '/en/admin/feed/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists(
            'body#admin_feed_index #main tbody tr',
            'The backend homepage displays all the available feeds.'
        );
    }

    /**
     * This test changes the database contents by creating a new feed URL.
     *
     * @author Nikunnj Bambhroliya <nikunjpatel190@gmail.com>
     * @return void
     */
    public function testAdminNewFeed(): void
    {
        $title = 'axelerant '.mt_rand();
        $url = "https://www.axelerant.com/tag/drupal-planet/feed";

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten'
        ]);

        $client->request('GET', '/en/admin/feed/new');

        $client->submitForm('Create Feed', [
            'feed[name]' => $title,
            'feed[url]' => $url
        ]);

        $this->assertResponseRedirects('/en/admin/feed/', Response::HTTP_FOUND);

        /** @var \App\Entity\Feed $feed */
        $feed = self::$container->get(FeedRepository::class)->findOneByName($title);
        $this->assertNotNull($feed);
        $this->assertSame($url, $feed->getUrl());
    }

    /**
     * This test create a duplicate entries of feeds and check error message.
     *
     * @author Nikunnj Bambhroliya <nikunjpatel190@gmail.com>
     * @return void
     */

    public function testAdminNewDuplicatedFeed(): void
    {
        $title = 'axelerant '.mt_rand();
        $url = "https://www.axelerant.com/tag/drupal-planet/feed";

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten'
        ]);

        $crawler = $client->request('GET', '/en/admin/feed/new');
        $form = $crawler->selectButton('Create Feed')->form([
            'feed[name]' => $title,
            'feed[url]' => $url
        ]);
        $client->submit($form);

        // Feed name must be unique, so trying to create the same feed twice should result in an error
        $client->submit($form);

        $this->assertSelectorTextSame('form .form-group.has-error label', 'Feed Name');
        $this->assertSelectorTextContains('form .form-group.has-error .help-block', 'This name was already used in another feed post, but they must be unique.');
    }
    /**
     * This test changes the database contents by editing a feed post.
     *
     * @author Nikunnj Bambhroliya <nikunjpatel190@gmail.com>
     * @return void
     */
    public function testAdminEditPost(): void
    {
        $title = 'axelerant '.mt_rand();
        $url = "https://www.axelerant.com/tag/drupal-planet/feed";

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $client->request('GET', '/en/admin/feed/2/edit');
        $client->submitForm('Save changes', [
            'feed[name]' => $title,
            'feed[url]' => $url
        ]);

        $this->assertResponseRedirects('/en/admin/feed/2/edit', Response::HTTP_FOUND);

        /** @var \App\Entity\Feed $feed */
        $feed = self::$container->get(FeedRepository::class)->find(2);
        $this->assertSame($title, $feed->getName());
    }

    /**
     * This test changes the database contents by deleting a feed post.
     *
     * @author Nikunnj Bambhroliya <nikunjpatel190@gmail.com>
     * @return void
     */
    public function testAdminDeletePost(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $crawler = $client->request('GET', '/en/admin/feed/21/delete');

        $this->assertResponseRedirects('/en/admin/feed/', Response::HTTP_FOUND);

        $feed = self::$container->get(FeedRepository::class)->find(21);
        $this->assertNull($feed);
    }
}
