<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Comment;
use App\Form\EventType;
use App\Form\EventUpdateType;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event')]
    public function index(EventRepository $eventRepository): Response
    {

        $events = $eventRepository->findLatest();

        return $this->render('event/index.html.twig', [
            'events' => $events
        ]);
    }

    #[Route('/event/create', name: 'app_event_create')]
    public function create(EntityManagerInterface $entityManager): Response
    {

        // Composant Request de Symfony
        $request = Request::createFromGlobals();

        $event = new Event();

        $user = $this->getUser();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $event->setUser($user);
            // Mise à jour de la base de données
            $entityManager->persist($event);
            $entityManager->flush();

            // Redirection sur la page qui affiche les articles
            return $this->redirectToRoute('app_event');
        }

        return $this->render('event/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/event/{id}', name: 'app_event_details')]
    public function details(EventRepository $eventRepository, CommentRepository $commentRepository, string $id, EntityManagerInterface $entityManager, Request $request): Response
    {

        // Récupération de la liste des évènements triés du plus récent au plus ancien
        $eventDetail = $eventRepository->find($id);

        // Composant Request de Symfony
        $request = Request::createFromGlobals();

        // On déclenche une exception si l'article n'existe pas
        if ($eventDetail === null) {
            throw $this->createNotFoundException("L'article demandé n'existe pas");
        }

        // Création d'un commentaire vide
        $comment = new Comment();

        // Création du formulaire pour les commentaires avec l'entité liée
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // L'entité commentaire a été mise à jour avec les données du formulaire
            // Je rattache le commentaire à l'article
            $comment->setEvent($eventDetail);

            $user = $this->getUser();
            $comment->setUser($user);

            // Mise à jour de la base de données
            $entityManager->persist($comment);
            $entityManager->flush();

            // Redirection sur la page qui affiche le détail de l'article et les commentaires
            return $this->redirectToRoute('app_event_details', ['id' => $id]);
        }

        // Récupération de tous les commentaires de l'article
        $comments = $commentRepository->findOldest($eventDetail->getId());

        return $this->render('event/detail.html.twig', [
            'eventDetail' => $eventDetail,
            'comments' => $comments,
            'form' => $form
        ]);
    }

    #[Route('/event/{id}/update', name: 'app_event_update')]
    public function update(EntityManagerInterface $entityManager, EventRepository $eventRepository, string $id): Response
    {

        $eventUpdate = $eventRepository->find($id);
        if ($eventUpdate->getId() !== null && $eventUpdate->getUser() !== $this->getUser()) {
            throw new AccessDeniedException("Cet article n'est pas le vôtre");
        }

        // Composant Request de Symfony
        $request = Request::createFromGlobals();

        $form = $this->createForm(EventUpdateType::class, $eventUpdate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Mise à jour de la base de données
            $entityManager->persist($eventUpdate);
            $entityManager->flush();

            // Redirection sur la page qui affiche les articles
            return $this->redirectToRoute('app_event_details', ['id' => $id]);
        }

        return $this->render('event/update.html.twig', [
            'event' => $eventUpdate,
            'form' => $form
        ]);

    }
}
