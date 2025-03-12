<?php


namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
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
    // Afficher la liste des conversations et les messages
    #[Route('/', name: 'app_messages')]
    public function index(MessageRepository $messageRepository, UserRepository $userRepository, Request $request): Response
    {
        $user = $this->getUser();

        // Récupérer toutes les conversations
        $conversations = $messageRepository->findUserConversations($user);

        // Si un utilisateur spécifique est sélectionné, récupérer les messages
        $receiver = null;
        $messages = [];

        if ($request->query->get('id')) {
            $receiver = $userRepository->find($request->query->get('id'));

            if ($receiver) {
                $messages = $messageRepository->findBy(
                    [
                        'sender'   => $user,
                        'receiver' => $receiver,
                    ],
                    ['sentAt' => 'ASC']
                );
            }
        }

        return $this->render('message/messages.html.twig', [
            'conversations' => $conversations,
            'messages'      => $messages,
            'receiver'      => $receiver,
        ]);
    }

    // Envoyer un message
    #[Route('/send/{id}', name: 'app_message_send', methods: ['POST'])]
    public function sendMessage(int $id, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $sender   = $this->getUser();
        $receiver = $userRepository->find($id);

        if (!$receiver) {
            throw $this->createNotFoundException("Utilisateur non trouvé.");
        }

        $content = $request->request->get('content');

        if (!$content) {
            $this->addFlash('danger', 'Le message ne peut pas être vide.');
            return $this->redirectToRoute('app_messages');
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
}
