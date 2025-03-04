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

  // Récupérer les messages envoyés et reçus par l'utilisateur
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
   $forms[$userGroup['user']->getId()] = $this->createForm(MessageType::class, $message)->createView();
  }

  return $this->render('message/list.html.twig', [
   'groupedMessages' => $groupedMessages,
   'forms'           => $forms,
  ]);
 }

 #[Route('/message/{id}', name: 'app_message_create')]
public function create(User $receiver, Request $request, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();

    // Vérifie si c'est une demande de discussion
    if ($request->query->get('chatRequest')) {
        if ($user->getProfile()->getSex() === 'male' && $receiver->getProfile()->getSex() === 'female') {
            $message = new Message();
            $message->setSender($user);
            $message->setReceiver($receiver);
            $message->setContent("Demande de discussion envoyée !");
            $message->setIsChatRequest(true); // On marque ce message comme une demande de chat

            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Demande de discussion envoyée !');

            return $this->redirectToRoute('app_messages');
        }
    }

    // Cas normal : envoi d'un message standard
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

        return $this->redirectToRoute('app_messages');
    }

    return $this->render('message/create.html.twig', [
        'form'     => $form->createView(),
        'receiver' => $receiver,
    ]);
}

#[Route('/message/accept/{id}', name: 'app_message_accept', methods: ['POST'])]
public function acceptMessage(Message $message, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();

    if ($message->isChatRequest() && $message->getReceiver() === $user) {
        // Marquer la demande comme acceptée en envoyant un message de confirmation
        $confirmationMessage = new Message();
        $confirmationMessage->setSender($user);
        $confirmationMessage->setReceiver($message->getSender());
        $confirmationMessage->setContent("Votre demande de discussion a été acceptée !");
        $confirmationMessage->setSentAt(new \DateTime());

        // On peut aussi supprimer le flag de demande de discussion
        $message->setIsChatRequest(false);

        $entityManager->persist($confirmationMessage);
        $entityManager->flush();

        $this->addFlash('success', 'Demande de discussion acceptée.');
    }

    return $this->redirectToRoute('app_messages');
}


}
