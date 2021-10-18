<?php

namespace App\Controller;

use App\Repository\FeedContentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage dashboard contents.
 *
 * @Route("/")
 *
 * @author Nikunj Bambhroliya <nikunjpatel190@gmail.com>
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", methods="GET", name="home_index")
     */
    public function index(Request $request, FeedContentRepository $feedContent): Response
    {
        $latestFeeds = $feedContent->findLatest();

        return $this->render('default/home.html.twig', [
            'latestFeeds' => $latestFeeds
        ]);
    }
}
