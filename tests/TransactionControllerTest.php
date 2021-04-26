<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\DataFixtures\UserFixtures;
use App\Service\PaymentService;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class TransactionControllerTest extends AbstractTest
{
    /**
     * @var string
     */
    private $basePath = '/api/v1/transactions';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function getFixtures(): array
    {
        return [
            new UserFixtures(
                self::$kernel->getContainer()->get('security.password_encoder'),
                self::$kernel->getContainer()->get(PaymentService::class)),
            new CourseFixtures()
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$kernel->getContainer()->get('jms_serializer');
    }

    public function testHistTransactions(): void
    {
        // Авторизация
        $userData = $this->auth();

        $client = self::getClient();
        // Создание запроса на получение всех курсов
        $client->request(
            'GET',
            $this->basePath . '/',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $userData['token']
            ]
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type', 'application/json'
        ));

        // Проверка содержимого ответа (1-на транзакция)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertCount(1, $response);
    }

    public function auth(): array
    {
        // Имитируем ввод данных пользователем
        $user = [
            'username' => 'test@gmail.com',
            'password' => 'general_user',
        ];

        // Создание запроса
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/auth',
            [],
            [],
            [ 'CONTENT_TYPE' => 'application/json' ],
            $this->serializer->serialize($user, 'json')
        );

        // Проверка содержимого ответа (В ответе должен быть представлен token)
        return json_decode($client->getResponse()->getContent(), true);
    }
}