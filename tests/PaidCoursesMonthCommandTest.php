<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PaidCoursesMonthCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        // Тест без аргумента (адрес назначения письма)
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);

        $command = $application->find('payment:report');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command'  => $command->getName()]);

        $output = $commandTester->getDisplay();
        // Проверка кода ответа
        self::assertEquals(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Отчет успешно сформирован.', $output);

        // Тест с аргументом (адрес назначения письма)
        $commandTester->execute([
            'command'  => $command->getName(),
            'destination_address' => 'twi9gy@mail.ru'
        ]);

        $output = $commandTester->getDisplay();
        // Проверка кода ответа
        self::assertEquals(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Отчет успешно сформирован.', $output);
    }
}