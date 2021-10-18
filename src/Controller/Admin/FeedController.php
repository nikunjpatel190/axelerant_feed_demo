<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Entity\Feed;
use App\Form\FeedType;
use App\Repository\FeedRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage feed URLs in the backend.
 *
 * @Route("/admin/feed")
 * @IsGranted("ROLE_ADMIN")
 *
 * @author Nikunj Bambhroliya <nikunjpatel190@gmail.com>
 */
class FeedController extends AbstractController
{
    /**
     * Lists all Feed entities.
     *
     * This controller responds to two different routes with the same URL:
     *   * 'admin_post_index' is the route with a name that follows the same
     *     structure as the rest of the controllers of this class.
     *   * 'admin_index' is a nice shortcut to the backend homepage. This allows
     *     to create simpler links in the templates. Moreover, in the future we
     *     could move this annotation to any other controller while maintaining
     *     the route name and therefore, without breaking any existing link.
     *
     * @Route("/", methods="GET", name="feed_index")
     * @Route("/", methods="GET", name="admin_feed_index")
     */
    public function index(FeedRepository $feeds): Response
    {
        $feeds = $feeds->findAll();

        return $this->render('admin/feed/index.html.twig', ['feeds' => $feeds]);
    }

    /**
     * Creates a new Feed entity.
     *
     * @Route("/new", methods="GET|POST", name="admin_feed_new")
     *
     */
    public function new(Request $request): Response
    {
        $feed = new Feed();
        $feed->setCreatedBy($this->getUser());
        $feed->setStatus('active');
        $feed->setIsDummy(0);

        $form = $this->createForm(FeedType::class, $feed);

        $form->handleRequest($request);

        // save form data
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($feed);
            $em->flush();

            $this->addFlash('success', 'feed.created_successfully');
            return $this->redirectToRoute('admin_feed_index');
        }

        return $this->render('admin/feed/new.html.twig', [
            'feed' => $feed,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing Feed entity.
     *
     * @Route("/{id<\d+>}/edit", methods="GET|POST", name="admin_feed_edit")
     */
    public function edit(Request $request, Feed $feed): Response
    {
        $form = $this->createForm(FeedType::class, $feed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'feed.updated_successfully');

            return $this->redirectToRoute('admin_feed_edit', ['id' => $feed->getId()]);
        }

        return $this->render('admin/feed/edit.html.twig', [
            'feed' => $feed,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a Feed entity.
     *
     * @Route("/{id}/delete", methods="GET", name="admin_feed_delete")
     */
    public function delete(Request $request, Feed $feed): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($feed);
        $em->flush();

        $this->addFlash('success', 'feed.deleted_successfully');

        return $this->redirectToRoute('admin_feed_index');
    }
}
