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
     'user'     => $otherUser,
     'messages' => [],
    ];
   }
   $groupedMessages[$otherUser->getId()]['messages'][] = $message;
  }

  // Préparer un formulaire pour chaque utilisateur
  $forms = [];
  foreach ($groupedMessages as $userGroup) {
   $message = new Message();
   $message->setSender($this->getUser());
   $message->setReceiver($userGroup['user']);
   $forms[$userGroup['user']->getId()] = $this->createForm(MessageType::class, $message)->createView(); // Utilisation de createView()
  }

  // Passe les formulaires au template
  return $this->render('message/list.html.twig', [
   'groupedMessages' => $groupedMessages,
   'forms'           => $forms,
  ]);
 }

 #[Route('/message/{id}', name: 'app_message_create')]
 public function create(User $receiver, Request $request, EntityManagerInterface $entityManager): Response
 {
  $message = new Message();
  $message->setSender($this->getUser());
  $message->setReceiver($receiver);

  // Créer le formulaire
  $form = $this->createForm(MessageType::class, $message);
  $form->handleRequest($request);

  if ($form->isSubmitted() && $form->isValid()) {
   $message->setSentAt(new \DateTime());
   $entityManager->persist($message);
   $entityManager->flush();

   // Rediriger vers la page des messages après l'envoi
   return $this->redirectToRoute('app_messages');
  }

  return $this->render('message/create.html.twig', [
   'form'     => $form->createView(),
   'receiver' => $receiver,
  ]);
 }
}

