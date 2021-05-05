<?php

namespace App\Command;

use App\Entity\Transaction;
use App\Service\Twig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;

class PaidCoursesMonth extends Command
{
    private $twig;
    private $mailer;
    private $manager;

    protected static $defaultName = 'payment:report';
    public function __construct(Twig $twig, MailerInterface $mailer, EntityManagerInterface $manager)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->manager = $manager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'destination_address',
                null,
                'destination address message',
                'main@gmail.com'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $transactions = $this->manager->getRepository(Transaction::class)->findPaidCoursesAtMonth();

        if (count($transactions) > 0) {
            // Получаем итоговую сумму
            $sumOver = 0;
            foreach ($transactions as $transaction) {
                $sumOver += $transaction['sum_buy'];
            }

            // Получаем период создания отчета
            // Текущая дата
            $endDate = (new \DateTime())->format('Y-m-d H:i');
            // Текущая дата минус 1 месяц
            $startDate = (new \DateTime())->modify('-1 month')->format('Y-m-d H:i');

            // Получаем шаблон для сообщения
            $html = $this->twig->render(
                'mail/paidCoursesMonth.html.twig',
                [
                    'courses' => $transactions,
                    'endDate' => new \DateTime(),
                    'startDate' => (new \DateTime())->modify('-1 month'),
                    'sumOver' => $sumOver
                ]
            );

            // Создаем сообщение пользователю
            $message = (new Email())
                ->to($input->getArgument('destination_address'))
                ->from('admin@gmail.com')
                ->subject('Отчет по данным об оплаченных курсах за месяц')
                ->html($html);

            try {
                // Оповещаем пользователя
                $this->mailer->send($message);
            } catch (TransportExceptionInterface $e) {
                $output->writeln($e->getMessage());

                $output->writeln('При отправлении сообщения появилась ошибка.');
                return Command::FAILURE;
            }
        }

        $output->writeln('Отчет успешно сформирован.');
        return Command::SUCCESS;
    }
}