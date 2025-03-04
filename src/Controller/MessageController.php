<?php


// src/Controller/MessageController.php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
 #[Route('/message/{id}', name: 'app_message_create')]
 public function create(User $receiver, Request $request, EntityManagerInterface $entityManager): Response
 {
  // Créer un nouveau message
  $message = new Message();
  $message->setSender($this->getUser()); // Utilise l'utilisateur connecté
  $message->setReceiver($receiver); // Utilise l'utilisateur auquel on envoie le message

  // Créer le formulaire pour le message
  $form = $this->createForm(MessageType::class, $message);
  $form->handleRequest($request);

  if ($form->isSubmitted() && $form->isValid()) {
   // Enregistrer le message
   $message->setSentAt(new \DateTime());
   $entityManager->persist($message);
   $entityManager->flush();

   // Rediriger vers la page des messages
   return $this->redirectToRoute('app_messages');
  }

  // Rendre la vue avec le formulaire
  return $this->render('message/create.html.twig', [
   'form'     => $form->createView(),
   'receiver' => $receiver,
  ]);
 }

 #[Route('/messages', name: 'app_messages')]
    public function list(MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();

        // Récupérer les messages envoyés et reçus par l'utilisateur, triés par correspondant
        $messages = $messageRepository->findBySenderOrReceiver($user);

        // Grouper les messages par utilisateur avec qui ils ont été échangés
        $groupedMessages = [];
        foreach ($messages as $message) {
            $otherUser = ($message->getSender() === $user) ? $message->getReceiver() : $message->getSender();
            if (!isset($groupedMessages[$otherUser->getId()])) {
                $groupedMessages[$otherUser->getId()] = [
                    'user' => $otherUser,
                    'messages' => [],
                ];
            }
            $groupedMessages[$otherUser->getId()]['messages'][] = $message;
        }

        return $this->render('message/list.html.twig', [
            'groupedMessages' => $groupedMessages,
        ]);
    }
}
