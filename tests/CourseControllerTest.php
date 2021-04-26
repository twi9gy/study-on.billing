<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\DataFixtures\UserFixtures;
use App\Service\PaymentService;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class CourseControllerTest extends AbstractTest
{
    /**
     * @var string
     */
    private $basePath = '/api/v1/courses';

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

    // Тест получения всех курсов
    public function testGetAllCourses(): void
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

        // Проверка содержимого ответа (4 курса)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertCount(4, $response);
    }

    // Тест получения информации о курсе
    public function testGetCourse(): void
    {
        // Авторизация
        $userData = $this->auth();

        $client = self::getClient();
        // Создание запроса на получение всех курсов
        $client->request(
            'GET',
            $this->basePath . '/Business-Analyst',
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

        // Проверка содержимого ответа (Тип курса - арендуемый)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals('rent', $response['type']);
    }

    // Тест покупки курса
    public function testPayCourse(): void
    {
        // Авторизация
        $userData = $this->auth();

        $client = self::getClient();
        // Создание запроса на получение всех курсов
        $client->request(
            'POST',
            $this->basePath . '/Business-Analyst/pay',
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

        // Проверка содержимого ответа (успешная операция)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals(true, $response['success']);
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