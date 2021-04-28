<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/v1/transactions")
 */
class TransactionController extends AbstractController
{
    /**
     *
     * @OA\Get(
     *     path="/api/v1/transactions/",
     *     tags={"transactions"},
     *     summary="Get all user transactions",
     *     description="Get all user transactions",
     *     operationId="courses.transactions",
     *     @OA\Parameter(
     *          name="type",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              example="payment|deposit"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="code",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              example="Business-Analyst"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="skip_expired",
     *          in="query",
     *          @OA\Schema(
     *              type="bool",
     *              example="1"
     *          )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(
     *                      property="id",
     *                      type="integer",
     *                      example="11"
     *                  ),
     *                  @OA\Property(
     *                      property="created_at",
     *                      type="datetime",
     *                      example="2019-05-20T13:46:07+00:00"
     *                  ),
     *                  @OA\Property(
     *                      property="type",
     *                      type="string",
     *                      example="payment"
     *                  ),
     *                  @OA\Property(
     *                      property="course_code",
     *                      type="string",
     *                      example="landshaftnoe-proektirovanie"
     *                  ),
     *                  @OA\Property(
     *                      property="amount",
     *                      type="number",
     *                      format="float",
     *                      example="159.00"
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
     *                  type="integer",
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
     * @Route("/", name="transactions_index", methods={"GET"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     * @throws \Exception
     */
    public function transactions(Request $request, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $transactionRepository = $entityManager->getRepository(Transaction::class);

        // Получаем текущего пользователя
        $userJwt = $this->getUser();
        $user = $userRepository->findOneBy(['email' => $userJwt->getUsername()]);

        $type = null;
        $code = null;
        $skip_expired = null;

        // Если установлен тип
        if ($request->get('type')) {
            // Получаем тип
            $type = $request->get('type');
        }

        // Если установлен код курса
        if ($request->get('code')) {
            // Получаем код курса
            $code = $request->get('code');
        }

        // Если установлен срок окончания аренды
        if ($request->get('skip_expired')) {
            // Получаем срок окончания аренды
            $skip_expired = $request->get('skip_expired');
        }

        $transactions = $transactionRepository->findByFilter(
            $type,
            $code,
            $skip_expired,
            $user
        );

        foreach ($transactions as $i => $transaction) {
            if ($transaction['type'] === 1) {
                $transactions[$i]['type'] = 'payment';
            } else {
                $transactions[$i]['type'] = 'deposit';
            }
        }

        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($serializer->serialize($transactions, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }
}
