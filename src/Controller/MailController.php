<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{
    #[Route('/test-email', name: 'test_email', methods: ['GET'])]
    public function sendTestEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('noreply@monsite.com')
            ->to('ton-email@mail.com') // Remplace par ton vrai email
            ->subject('Test Symfony Mailtrap')
            ->text('Ceci est un email de test envoyé depuis Symfony avec Mailtrap.');

        $mailer->send($email);

        return new Response('Email envoyé avec succès !');
    }
}
