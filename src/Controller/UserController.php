<?php

namespace App\Controller;

use App\Entity\User;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/users")
 */
class UserController extends AbstractController
{
    /**
     * @OA\Post(
     *     path="/api/v1/users/current",
     *     tags={"user"},
     *     summary="Get info user",
     *     description="Get info user",
     *     operationId="current",
     *     @OA\Response(
     *          response="200",
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="username",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="roles",
     *                  type="array",
     *                  @OA\Items(
     *                      type="string"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="balance",
     *                  type="number",
     *                  format="float"
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  example="401"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="JWT Token not found"
     *              )
     *          )
     *     )
     * )
     *
     * @Route("/current", name="user", methods={"POST"})
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function current(SerializerInterface $serializer): Response
    {
        // Получаем пользователя
        $userJwt = $this->getUser();
        $response = new Response();

        if (!$userJwt) {
            // Формируем ответ
            $data = [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Пользователь не найден',
            ];
            $response->setStatusCode(Response::HTTP_CONFLICT);
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $userRepository = $entityManager->getRepository(User::class);
            // Получаем информацию о пользователе
            $user = $userRepository->findOneBy(['email' => $userJwt->getUsername()]);
            // Формируем ответ
            $data = [
                'username' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'balance' => $user->getBalance()
            ];
            $response->setStatusCode(Response::HTTP_OK);
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }
}