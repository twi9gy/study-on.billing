<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\User;
use App\Service\PaymentService;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/courses")
 */
class CourseController extends AbstractController
{
    /**
     *
     * @OA\Get(
     *     path="/api/v1/courses/",
     *     tags={"courses"},
     *     summary="Get all courses",
     *     description="Get all courses",
     *     operationId="courses.index",
     *     @OA\Response(
     *          response="200",
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(
     *                      property="code",
     *                      type="string",
     *                      example="landshaftnoe-proektirovanie"
     *                  ),
     *                  @OA\Property(
     *                      property="type",
     *                      type="string",
     *                      example="rent"
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="number",
     *                      format="float",
     *                      example="99.90"
     *                  ),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Invalid credentials",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  example="401"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Invalid credentials."
     *              )
     *          )
     *     )
     * )
     *
     * @Route("/", name="courses_index", methods={"GET"})
     */
    public function index(SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);

        // Получаем все курсы
        $courses = $courseRepository->findAllCourses();

        $response = new Response();
        // Устанавливаем статус ответа
        $response->setStatusCode(Response::HTTP_OK);
        // Устанавливаем содержание ответа
        $response->setContent($serializer->serialize($courses, 'json'));
        // Устанавливаем заголовок
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Get(
     *     path="/api/v1/courses/{code}",
     *     tags={"courses"},
     *     summary="Get courses by code",
     *     description="Get courses by code",
     *     operationId="courses.show",
     *     @OA\Response(
     *          response="200",
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  example="landshaftnoe-proektirovanie"
     *              ),
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  example="rent"
     *              ),
     *              @OA\Property(
     *                  property="price",
     *                  type="number",
     *                  format="float",
     *                  example="99.90"
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Invalid credentials",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  example="401"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Invalid credentials."
     *              )
     *          )
     *     )
     * )
     *
     * @Route("/{code}", name="courses_show", methods={"GET"})
     * @param string $code
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function show(string $code, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);

        // Поиск курса
        $course = $courseRepository->findOneBy(['code' => $code]);

        $courseData = [
            'code' => $course->getCode(),
            'type' => $course->getTypeFormatString(),
            'price' => 'free' !== $course->getTypeFormatString() ? $course->getCost() : null
        ];

        $response = new Response();
        // Устанавливаем статус ответа
        $response->setStatusCode(Response::HTTP_OK);
        // Устанавливаем содержание ответа
        $response->setContent($serializer->serialize($courseData, 'json'));
        // Устанавливаем заголовок
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Post(
     *     path="/api/v1/courses/{code}/pay",
     *     tags={"courses"},
     *     summary="Pay course",
     *     description="Pay course",
     *     operationId="courses.pay",
     *     @OA\Response(
     *          response="200",
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="bool",
     *                  example="true"
     *              ),
     *              @OA\Property(
     *                  property="course_type",
     *                  type="string",
     *                  example="rent"
     *              ),
     *              @OA\Property(
     *                  property="expires_at",
     *                  type="datetime",
     *                  example="2019-05-20T13:46:07+00:00"
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *          response="406",
     *          description="Недостаточно средств",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="integer",
     *                  example="406"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="На вашем счете недостаточно средств."
     *              )
     *          )
     *     )
     * )
     *
     * @Route("/{code}/pay", name="courses_pay", methods={"POST"})
     * @param string $code
     * @param SerializerInterface $serializer
     * @param \App\Service\PaymentService $paymentService
     * @return Response
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function pay(string $code, SerializerInterface $serializer, PaymentService $paymentService): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $courseRepository = $entityManager->getRepository(Course::class);

        // Поиск курса
        $course = $courseRepository->findOneBy(['code' => $code]);

        // Получаем информацию о пользователе
        $email = $this->getUser()->getUsername();
        $user = $userRepository->findOneBy(['email' => $email]);

        $response = new Response();

        // Списываем деньги за курс
        $result = $paymentService->payment($user, $course, $course->getCost());

        // Формируем ответ
        $data = [
            'success' => true,
            'course_type' => $course->getTypeFormatString(),
            'expires_at' => 'rent' === $course->getTypeFormatString() ? $result : null,
        ];
        // Устанавливаем статус ответа
        $response->setStatusCode(Response::HTTP_OK);

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }
}
