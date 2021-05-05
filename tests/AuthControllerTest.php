<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Service\PaymentService;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends AbstractTest
{
    /**
     * @var string
     */
    private $basePath = '/api/v1';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function getFixtures(): array
    {
        return [new AppFixtures(self::$kernel->getContainer()->get('security.password_encoder'),self::$kernel->getContainer()->get(PaymentService::class))];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$kernel->getContainer()->get('jms_serializer');
    }

    // Тесты для успешного входа в систему
    public function testSuccessfulAuth(): void
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
            $this->basePath . '/auth',
            [],
            [],
            [ 'CONTENT_TYPE' => 'application/json' ],
            $this->serializer->serialize($user, 'json')
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type', 'application/json'
        ));

        // Проверка содержимого ответа (В ответе должен быть представлен token)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($response['token']);
        self::assertNotEmpty($response['refresh_token']);
    }

    // Тесты для неуспешно входа в систему с невырным паролем
    public function testUnsuccessfulAuthWithInvalidPassword(): void
    {
        // Имитируем ввод данных пользователем
        // Пароль неверный
        $user = [
            'username' => 'test@gmail.com',
            'password' => 'general',
        ];

        // Создание запроса
        $client = self::getClient();
        $client->request(
            'POST',
            $this->basePath . '/auth',
            [],
            [],
            [ 'CONTENT_TYPE' => 'application/json' ],
            $this->serializer->serialize($user, 'json')
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type', 'application/json'
        ));

        // Проверка содержимого ответа (В ответе должена быть инормация об ошибке)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($response['message']);
    }

    // Тесты для неуспешно входа в систему с невырным именем пользователя
    public function testUnsuccessfulAuthWithInvalidUserName(): void
    {
        // Имитируем ввод данных пользователем
        // Имя пользователя неверное
        $user = [
            'username' => 'test',
            'password' => 'general_user',
        ];

        // Создание запроса
        $client = self::getClient();
        $client->request(
            'POST',
            $this->basePath . '/auth',
            [],
            [],
            [ 'CONTENT_TYPE' => 'application/json' ],
            $this->serializer->serialize($user, 'json')
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type', 'application/json'
        ));

        // Проверка содержимого ответа (В ответе должена быть инормация об ошибке)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($response['message']);
    }

    // Тесты для успешной регистрации
    public function testSuccessfulRegister(): void
    {
        // Имитируем ввод данных пользователем
        $user = [
            'email' => 'NewUser@gmail.com',
            'password' => 'i`m batman',
        ];

        // Создание запроса
        $client = self::getClient();
        $client->request(
            'POST',
            $this->basePath . '/register',
            [],
            [],
            [ 'CONTENT_TYPE' => 'application/json' ],
            $this->serializer->serialize($user, 'json')
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_CREATED, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type', 'application/json'
        ));

        // Проверка содержимого ответа (В ответе должен быть представлен token)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($response['token']);
        self::assertNotEmpty($response['refresh_token']);
    }

    // Тесты для регистрации уже существующего пользователя
    public function testExistUserRegister(): void
    {
        // Имитируем ввод данных пользователем
        $user = [
            'email' => 'test@gmail.com',
            'password' => 'general_user',
        ];

        // Создание запроса
        $client = self::getClient();
        $client->request(
            'POST',
            $this->basePath . '/register',
            [],
            [],
            [ 'CONTENT_TYPE' => 'application/json' ],
            $this->serializer->serialize($user, 'json')
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_FORBIDDEN, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type', 'application/json'
        ));

        // Проверка содержимого ответа (В ответе должена быть информация об ошибке)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals('Пользователь уже существует', $response['message']);
    }

    // Тесты для регистрации неправильных полей
    public function testInvalidFieldsRegister(): void
    {
        // Имитируем ввод данных пользователем
        // Имя пользователя не представляет формат email
        // Пароль не состоит из 6 символом
        $user = [
            'email' => 'SuperMan',
            'password' => '123',
        ];

        // Создание запроса
        $client = self::getClient();
        $client->request(
            'POST',
            $this->basePath . '/register',
            [],
            [],
            [ 'CONTENT_TYPE' => 'application/json' ],
            $this->serializer->serialize($user, 'json')
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type', 'application/json'
        ));

        // Проверка содержимого ответа (Информация об ошибке в ответе должна быть массивом с двумя подмассивами)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertCount(2, $response['message']);
    }

    // Тест обновления токека
    public function testRefreshToken(): void
    {
        // Имитируем ввод данных пользователем
        $user = [
            'username' => 'test@gmail.com',
            'password' => 'general_user',
        ];

        // Создание запроса для авторизации пользователя
        $client = self::getClient();
        $client->request(
            'POST',
            $this->basePath . '/auth',
            [],
            [],
            [ 'CONTENT_TYPE' => 'application/json' ],
            $this->serializer->serialize($user, 'json')
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type', 'application/json'
        ));

        $response = json_decode($client->getResponse()->getContent(), true);

        // Создание запроса для обновления токена доступа
        $client = self::getClient();
        $client->request(
            'POST',
            $this->basePath . '/token/refresh',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'Authorization' => 'Bearer ' . $response['token']
            ],
            $this->serializer->serialize(['refresh_token' => $response['refresh_token']], 'json')
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type', 'application/json'
        ));

        // Проверка содержимого ответа (В ответе должен быть представлен token)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($response['token']);
        self::assertNotEmpty($response['refresh_token']);
    }
}
