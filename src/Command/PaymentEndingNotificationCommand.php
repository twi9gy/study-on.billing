<?php

namespace App\Command;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Symfony\Component\Mime\Email;

class PaymentEndingNotificationCommand extends Command
{
    private $twig;
    private $mailer;
    private $manager;

    protected static $defaultName = 'payment:ending:notification';
    public function __construct(Twig $twig, MailerInterface $mailer, EntityManagerInterface $manager)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->manager = $manager;

        parent::__construct();
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->manager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            // Получаем курсы пользователя, срок аренды которых заканчивается через сутки.
            // Получаем транзации связанные с этими курсами.
            $transactions = $this->manager->getRepository(Transaction::class)->findEndRentalPeriod($user);

            if (count($transactions) > 0) {
                // Получаем шаблон для сообщения
                $html = $this->twig->render(
                    'mail/endPeriodRent.html.twig',
                    ['courses' => $transactions]
                );

                // Создаем сообщение пользователю
                $message = (new Email())
                    ->to($user->getUserName())
                    ->from('admin@gmail.com')
                    ->subject('Уведомление об окончании срока аренды')
                    ->html($html);

                try {
                    // Оповещаем пользователя
                    $this->mailer->send($message);
                } catch (TransportExceptionInterface $e) {
                    $output->writeln($e->getMessage());

                    $output->writeln('Не удалось отправить сообщение.');
                    return Command::FAILURE;
                }
            }
        }

        $output->writeln('Сообщения были отправлены пользователям.');
        return Command::SUCCESS;
    }
}