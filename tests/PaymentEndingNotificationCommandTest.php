<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PaymentEndingNotificationCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);

        $command = $application->find('payment:ending:notification');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command'  => $command->getName()]);

        $output = $commandTester->getDisplay();
        // Проверка кода ответа
        self::assertEquals(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Сообщения были отправлены пользователям.', $output);
    }
}