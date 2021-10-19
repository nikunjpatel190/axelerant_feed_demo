<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * This test changes the database contents by creating a new feed URL. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
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
}
