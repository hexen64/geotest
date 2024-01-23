<?php

namespace App\Services;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{

    private string $sender;
    private MailerInterface $mailer;
    private Environment $twig;
    private array $data;

    public function __construct(MailerInterface $mailer, Environment $twig, string $sender)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->sender = $sender;
    }

    public function execute(array $data)
    {
        $this->data = $data;
    }

    public function sendReceipt()
    {
        $from = $this->sender;
        $to = $this->data['contact']['email'];
        $subject = 'Геотест. Заказ № ' . $this->data['id'];
        $this->data['info'] = [
            'email' => $from,
            //'phones' => '(343) 383-76-84, 383-64-73, 368-75-77, 383-77-53'));
            //'phones' => '(343) 368-75-77, 383-64-73, 385-77-53'));
            'phones' => '(343) 368-75-77, 383-64-73'
        ];
        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->html($this->twig->render('orders/mail.html.twig', $this->data));

        $this->mailer->send($email);

    }

    public function toAdmin()
    {
        $from = $this->sender;
        $to = $this->data['contact']['email'];
        $subject = 'Геотест. Заказ № ' . $this->data['id'];
        $this->data['info'] = [
            'email' => $from,
            //'phones' => '(343) 383-76-84, 383-64-73, 368-75-77, 383-77-53'));
            //'phones' => '(343) 368-75-77, 383-64-73, 385-77-53'));
            'phones' => '(343) 368-75-77, 383-64-73'
        ];

        $email = (new Email())
            ->from($from)
            ->replyTo($to)
            ->to($this->sender)
            ->subject($subject)
            ->html($this->twig->render('orders/mailAdmin.html.twig', $this->data));

        $this->mailer->send($email);

    }
}