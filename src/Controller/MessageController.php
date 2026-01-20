<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\DeletedMessage;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Repository\DeletedConversationRepository;
use App\Services\MessageQuotaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/messages')]
#[IsGranted('ROLE_USER')]
class MessageController extends AbstractController
{
    #[Route('/', name: 'app_messages')]
    public function index(
        MessageRepository $messageRepository,
        UserRepository $userRepository,
        Request $request
    ): Response {
        $user = $this->getUser();

        $conversations = $messageRepository->findUserConversations($user);
        $receiver = null;
        $messages = [];
        $chatAccepted = false;

        if ($request->query->get('id')) {
            $receiver = $userRepository->find($request->query->get('id'));

            if ($receiver) {
                $messages = $messageRepository->findByConversation($user, $receiver);
                $chatAccepted = $messageRepository->hasAcceptedChat($user, $receiver);

                foreach ($messages as $message) {
                    if ($message->isChatRequest() && $message->getContent() === 'ACCEPTED') {
                        $chatAccepted = true;
                        break;
                    }
                }
            }
        }

        return $this->render('message/messages.html.twig', [
            'conversations' => $conversations,
            'messages'      => $messages,
            'receiver'      => $receiver,
            'chatAccepted'  => $chatAccepted,
        ]);
    }

    #[Route('/request/{id}', name: 'app_message_request', methods: ['POST'])]
    public function requestChat(
        int $id,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $sender = $this->getUser();
        $receiver = $userRepository->find($id);

        if (!$receiver) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $existingRequest = $entityManager->getRepository(Message::class)->findOneBy([
            'sender' => $sender,
            'receiver' => $receiver,
            'isChatRequest' => true,
        ]);

        if (!$existingRequest) {
            $message = new Message();
            $message->setSender($sender);
            $message->setReceiver($receiver);
            $message->setContent('Demande de discussion en attente...');
            $message->setSentAt(new \DateTimeImmutable());
            $message->setIsChatRequest(true);

            $entityManager->persist($message);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_messages', ['id' => $receiver->getId()]);
    }

    #[Route('/response/{id}/{status}', name: 'app_message_response', methods: ['POST'])]
    public function respondToChat(
        int $id,
        string $status,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $receiver = $this->getUser();
        $sender = $userRepository->find($id);

        if (!$sender) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $message = new Message();
        $message->setSender($receiver);
        $message->setReceiver($sender);
        $message->setSentAt(new \DateTimeImmutable());
        $message->setIsChatRequest(true);
        $message->setContent($status === 'accept' ? 'ACCEPTED' : 'REFUSED');

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('app_messages', ['id' => $sender->getId()]);
    }

    #[Route('/send/{id}', name: 'app_message_send', methods: ['POST'])]
    public function sendMessage(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        MessageRepository $messageRepository,
        MessageQuotaService $messageQuotaService
    ): Response {
        $sender = $this->getUser();
        $receiver = $userRepository->find($id);

        if (!$receiver) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $content = trim((string) $request->request->get('content'));

        if ($content === '') {
            $this->addFlash('danger', 'Le message ne peut pas être vide.');
            return $this->redirectToRoute('app_messages', ['id' => $receiver->getId()]);
        }

        if (!$messageRepository->hasAcceptedChat($sender, $receiver)) {
            $this->addFlash('danger', 'Discussion non acceptée.');
            return $this->redirectToRoute('app_messages', ['id' => $receiver->getId()]);
        }

        // ✅ Limite 10 messages / jour pour non-premium
        if (!$this->isGranted('ROLE_PREMIUM')) {
            if ($messageQuotaService->hasReachedLimit($sender)) {
                $this->addFlash(
                    'danger',
                    'Limite atteinte : 10 messages par jour. Abonnez-vous pour continuer.'
                );
                return $this->redirectToRoute('app_stripe_subscribe');
            }
        }

        $message = new Message();
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setContent($content);
        $message->setSentAt(new \DateTimeImmutable());
        $message->setIsChatRequest(false);

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('app_messages', ['id' => $receiver->getId()]);
    }

    #[Route('/delete-conversation/{id}', name: 'app_delete_conversation', methods: ['POST'])]
    public function deleteConversation(
        int $id,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        DeletedConversationRepository $deletedConversationRepository,
        MessageRepository $messageRepository
    ): Response {
        $user = $this->getUser();
        $interlocutor = $userRepository->find($id);

        if (!$interlocutor) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $messages = $messageRepository->findByConversation($user, $interlocutor);

        foreach ($messages as $message) {
            $deletedMessage = new DeletedMessage();
            $deletedMessage->setContent($message->getContent());
            $deletedMessage->setSender($message->getSender());
            $deletedMessage->setReceiver($message->getReceiver());
            $deletedMessage->setSentAt($message->getSentAt());
            $deletedMessage->setDeletedAt(new \DateTimeImmutable());

            $entityManager->persist($deletedMessage);
        }

        $deletedConversationRepository->deleteConversation($user, $interlocutor);

        $entityManager->flush();

        $this->addFlash('success', 'La conversation a été supprimée.');

        return $this->redirectToRoute('app_messages');
    }
}
