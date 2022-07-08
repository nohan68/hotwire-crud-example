<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/message')]
class MessageController extends AbstractController
{

    #[Route('/', name: 'app_message_msg', methods: ['GET'])]
    public function messages(MessageRepository $messageRepository): Response
    {
        $newForm = $this->createForm(MessageType::class, new Message());
        return $this->render('message/index.html.twig', [
            'messages' => $messageRepository->findAll(),
            'newForm' => $newForm->createView()
        ]);
    }

    #[Route('/index', name: 'app_message_index', methods: ['GET'])]
    public function index(MessageRepository $messageRepository): Response
    {
        return $this->render('message/index.html.twig', [
            'messages' => $messageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_message_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MessageRepository $messageRepository): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setDate(new \DateTime('now'));
            $messageRepository->add($message, true);
            return new Response(
                $this->render('message/new.turbo.html.twig', ['message' => $message]),
                Response::HTTP_OK,
                ['Content-Type' => 'text/vnd.turbo-stream.html']
            );
            //return $this->render('new.turbo.html.twig', ['message' => $message]);
            //return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('message/new.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_message_show', methods: ['GET'])]
    public function show(Message $message): Response
    {
        return $this->render('message/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_message_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Message $message, MessageRepository $messageRepository): Response
    {
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $messageRepository->add($message, true);

            return new Response(
                $this->render('message/edit.html.twig', ['message' => $message]),
                Response::HTTP_OK,
                ['Content-Type' => 'text/vnd.turbo-stream.html']
            );
        }

        return new Response(
            $this->render('message/edit.turbo.html.twig', ['message' => $message, 'form' => $form->createView()]),
            Response::HTTP_OK,
            ['Content-Type' => 'text/vnd.turbo-stream.html']
        );
    }

    #[Route('/{id}/delete', name: 'app_message_delete', methods: ['GET'])]
    public function delete(Request $request, Message $message, MessageRepository $messageRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $messageRepository->remove($message, true);
        }
        $clone = clone $message;
        $messageRepository->remove($message,true);
        return new Response(
            $this->render('message/delete.turbo.html.twig', ['message' => $clone]),
            Response::HTTP_OK,
            ['Content-Type' => 'text/vnd.turbo-stream.html']
        );
        //return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
    }
}
