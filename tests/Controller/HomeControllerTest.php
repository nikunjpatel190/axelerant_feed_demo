<?php

/*
 * This file is part of unit testing of check dashboard page
 *
 * (c) Nikunj Bambhroliya <nikunjpatel190@gmail.com>
 *
 */

namespace App\Tests\Controller;

use App\Entity\Feed;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional test for the controllers defined inside HomeController.
 */
class HomeControllerTest extends WebTestCase
{
    /**
     * This test changes check dashboard page loaded or not
     *
     * @author Nikunnj Bambhroliya <nikunjpatel190@gmail.com>
     * @return void
     */
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $this->assertResponseIsSuccessful();
        /*$this->assertSelectorExists(
            'body#homepage_index',
            'The backend homepage displays all the available feeds.'
        );*/

        $this->assertSelectorTextContains('body#homepage_index div#main h1', 'Latest Curated Feeds :');
    }
}
