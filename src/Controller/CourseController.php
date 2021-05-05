<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\User;
use App\Model\CourseDto;
use App\Repository\CourseRepository;
use App\Service\PaymentService;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use PHPUnit\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

        foreach ($courses as $i => $course) {
            if ($course['type'] === 1) {
                $courses[$i]['type'] = 'rent';
            } elseif ($course['type'] === 2) {
                $courses[$i]['type'] = 'free';
            } else {
                $courses[$i]['type'] = 'buy';
            }
        }

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
     * @OA\Post(
     *     path="/api/v1/courses/new",
     *     tags={"courses"},
     *     summary="Create new course",
     *     description="Create new course",
     *     operationId="courses.new",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CourseDto")
     *     ),
     *     @OA\Response(
     *          response="201",
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="bool",
     *                  example="true"
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
     * @Route("/new", name="courses_new", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function new(Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // Десериализация запроса в Dto
        $courseDto = $serializer->deserialize($request->getContent(), CourseDto::class, 'json');
        // Проверка ошибок валидации
        $errors = $validator->validate($courseDto);

        $response = new Response();

        if (count($errors) > 0) {
            // Формируем ответ сервера
            $data = [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $errors,
            ];
            // Устанавливаем статус ответа
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            try {
                // Создаем курс из Dto
                $course = Course::fromDto($courseDto);

                $entityManager = $this->getDoctrine()->getManager();

                // Сохраняем курс в базе данных
                $entityManager->persist($course);
                $entityManager->flush();

                // Формируем ответ сервера
                $data = [
                    'success' => true,
                ];
                $response->setStatusCode(Response::HTTP_CREATED);
            } catch (\Exception $e) {
                // Формируем ответ сервера
                $data = [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => $e->getMessage(),
                ];
                $response->setStatusCode(Response::HTTP_CREATED);
            }
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Get(
     *     path="/api/v1/courses/{code}",
     *     tags={"courses"},
     *     summary="Get course by code",
     *     description="Get course by code",
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
     *     path="/api/v1/courses/{code}",
     *     tags={"courses"},
     *     summary="Edit course",
     *     description="Edit course",
     *     operationId="courses.edit",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CourseDto")
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="bool",
     *                  example="true"
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
     * @Route("/{code}", name="courses_edit", methods={"POST"})
     * @param Request $request
     * @param string $code
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param \App\Repository\CourseRepository $courseRepository
     * @return Response
     */
    public function edit(
        Request $request,
        string $code,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CourseRepository $courseRepository
    ): Response {
        // Десериализация запроса в Dto
        $courseDto = $serializer->deserialize($request->getContent(), CourseDto::class, 'json');
        // Проверка ошибок валидации
        $errors = $validator->validate($courseDto);

        $response = new Response();

        if (count($errors) > 0) {
            // Формируем ответ сервера
            $data = [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $errors,
            ];
            // Устанавливаем статус ответа
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            // Получаем существующий курс
            $course = $courseRepository->findOneBy(['code' => $code]);

            if ($course) {
                $course->setTitle($courseDto->title);
                $course->setCode($courseDto->code);
                $course->setCost($courseDto->price);
                $course->setType($courseDto->type === 'rent' ? 1 : 2);

                $entityManager = $this->getDoctrine()->getManager();

                // Сохраняем курс в базе данных
                $entityManager->persist($course);
                $entityManager->flush();

                // Формируем ответ сервера
                $data = [
                    'success' => true,
                ];
                $response->setStatusCode(Response::HTTP_OK);
            } else {
                // Формируем ответ сервера
                $data = [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Курс не найден.',
                ];
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        }

        $response->setContent($serializer->serialize($data, 'json'));
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
