<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
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
            new AppFixtures(
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

        // Проверка содержимого ответа (9 курсов)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertCount(9, $response);
    }

    // Тест получения информации о курсе
    public function testGetCourse(): void
    {
        // Авторизация
        $userData = $this->auth();

        $client = self::getClient();
        // Создание запроса на получения курса
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
        // Создание запроса для оплаты курса
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

    public function testCreateCourse(): void
    {
        // Авторизация
        $userData = $this->auth();

        $client = self::getClient();

        // Описание нового курса
        $course = [
            'type' => 'free',
            'title' => '1-С программирование.',
            'code' => '1-C'
        ];

        // Создание запроса на создание курса
        $client->request(
            'POST',
            $this->basePath . '/new',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $userData['token']
            ],
            $this->serializer->serialize($course, 'json')
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_CREATED, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type', 'application/json'
        ));

        // Проверка содержимого ответа (успешная операция)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($response['success']);
    }

    public function testEditCourse(): void
    {
        // Авторизация
        $userData = $this->auth();

        $client = self::getClient();

        $courseRepository = self::getEntityManager()->getRepository(Course::class);

        $course = $courseRepository->findOneBy(['code' => 'Financial-management']);

        // Описание нового курса
        $newData = [
            'type' => 'free',
            'title' => '1-С программирование.',
            'code' => '1-C'
        ];

        // Создание запроса на создание курса
        $client->request(
            'POST',
            $this->basePath . '/' . $course->getCode(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $userData['token']
            ],
            $this->serializer->serialize($newData, 'json')
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type', 'application/json'
        ));

        // Проверка содержимого ответа (успешная операция)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($response['success']);
    }

    public function auth(): array
    {
        // Имитируем ввод данных пользователем
        $user = [
            'username' => 'admin@gmail.com',
            'password' => 'super_admin',
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