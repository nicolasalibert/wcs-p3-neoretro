<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventIsVisibleFormType;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/event', name: 'event_')]
class EventController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(EventRepository $eventRepository, Request $request): Response
    {
        $isVisible = 1;

        if ($this->isGranted('ROLE_ADMIN')) {
            $isVisibleForm = $this->createForm(EventIsVisibleFormType::class);
            $isVisibleForm->handleRequest($request);

            if ($isVisibleForm->isSubmitted() && $isVisibleForm->isValid()) {
                $isVisible = $isVisibleForm->getData()->isVisible();
            }
        }

        $events = $eventRepository->findBy(
            ['isVisible' => $isVisible],
            ['startDate' => 'DESC',]
        );

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'pageTitle' => 'Events',
            'isVisibleForm' => $isVisibleForm ?? '',
            'isVisible' => $isVisible,
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'pageTitle' => 'Event details',
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($event->getLabel()) {
                $slug = $slugger->slug($event->getLabel());
                $event->setSlug($slug);
            }
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash("success", "The event has been added");

            return $this->redirectToRoute('event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
            'pageTitle' => 'New event'
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{slug}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
            'pageTitle' => 'Edit event'
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{slug}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{slug}/enroll', name: 'enroll', methods: ['GET'])]
    public function enroll(Event $event, EntityManagerInterface $entityManager, Request $request, EventService $eventService): Response
    {
        $user = $this->getUser();
        $referer = $request->headers->get('referer');

        $error = $eventService->checkEnrollment($event, $user, 'enroll');
        if ($error) {
            $this->addFlash('warning', $error);
            return $user ? $this->redirect($referer ? : $this->generateUrl('event_index')) : $this->redirectToRoute('dashboard');
        }

        $event->addParticipant($user);
        $entityManager->persist($event);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Your participation in ' . strtoupper($event->getLabel()) . ' is registered.' . "\n" .
            'See you on ' . $event->getStartDate()->format('d•m•Y \a\t H:i') . '!'
        );

        return $this->redirect($referer ?: $this->generateUrl('event_show', ['slug' => $event->getSlug()]));
    }

    #[Route('/{slug}/unenroll', name: 'unenroll', methods: ['GET'])]
    public function unenroll(Event $event, EntityManagerInterface $entityManager, Request $request, EventService $eventService): Response
    {
        $user = $this->getUser();
        $referer = $request->headers->get('referer');

        $error = $eventService->checkEnrollment($event, $user, 'unenroll');
        if ($error) {
            $this->addFlash('warning', $error);
            return $user ? $this->redirect($referer ? : $this->generateUrl('event_index')) : $this->redirectToRoute('dashboard');
        }

        $event->removeParticipant($user);
        $entityManager->persist($event);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Your participation in ' . strtoupper($event->getLabel()) . ' is canceled.' . "\n" .
            'See you soon!'
        );

        return $this->redirect($referer ?: $this->generateUrl('event_show', ['slug' => $event->getSlug()]));
    }
}
