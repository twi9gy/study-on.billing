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
     *          name="course_code",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              example="landshaftnoe-proektirovanie"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="skip_expired",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              example="2019-05-20T13:46:07+00:00"
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

        // Получаем все транзакции пользователя
        $transactions = $transactionRepository->findBy(['userBilling' => $user->getId()]);

        // Если установлен тип, то фильтруем транзакции по типу
        if ($request->get('type') !== null) {
            // Получаем тип
            $type = $request->get('type');
            // Фильтруем транзации по типу
            $transactions = $this->filterByType($transactions, $type);
        }

        // Если установлен код курса, то фильтруем транзакции по коду курса
        if ($request->get('course_code') !== null) {
            // Получаем код курса
            $course_code = $request->get('course_code');
            // Фильтруем транзации по код курса
            $transactions = $this->filterByCodeCourse($transactions, $course_code);
        }

        // Если установлен срок окончания аренды, то фильтруем транзакции по сроку окончания аренды
        if ($request->get('skip_expired') !== null) {
            // Получаем срок окончания аренды
            $skip_expired = $request->get('skip_expired');
            // Фильтруем транзации по сроку окончания аренды
            $transactions = $this->filterBySkipExpired($transactions, new \DateTime($skip_expired));
        }

        // Формируем ответ сервера
        $data = [];
        foreach ($transactions as $transaction) {
            if ($transaction->getCourse()) {
                if ($transaction->getPeriodValidity()) {
                    $record = [
                        'id' => $transaction->getId(),
                        'created_at' => $transaction->getCreatedAt(),
                        'skip_expired' => $transaction->getPeriodValidity(),
                        'type' => $transaction->getTypeOperationFormatString(),
                        'course_code' => $transaction->getCourse()->getCode(),
                        'amount' => $transaction->getValue()
                    ];
                } else {
                    $record = [
                        'id' => $transaction->getId(),
                        'created_at' => $transaction->getCreatedAt(),
                        'type' => $transaction->getTypeOperationFormatString(),
                        'course_code' => $transaction->getCourse()->getCode(),
                        'amount' => $transaction->getValue()
                    ];
                }
            } else {
                $record = [
                    'id' => $transaction->getId(),
                    'created_at' => $transaction->getCreatedAt(),
                    'type' => $transaction->getTypeOperationFormatString(),
                    'amount' => $transaction->getValue()
                ];
            }
            $data[] = $record;
        }

        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    private function filterByType(array $transactions, string $type): array
    {
        $result = [];
        foreach ($transactions as $transaction) {
            if ($transaction->getTypeOperationFormatString() === $type) {
                $result[] = $transaction;
            }
        }
        return $result;
    }

    private function filterByCodeCourse(array $transactions, string $course_code): array
    {
        $result = [];
        foreach ($transactions as $transaction) {
            if ($transaction->getCourse() && $transaction->getCourse()->getCode() === $course_code) {
                $result[] = $transaction;
            }
        }
        return $result;
    }

    private function filterBySkipExpired(array $transactions, \DateTime $skip_expired): array
    {
        $result = [];
        foreach ($transactions as $transaction) {
            if ($transaction->getPeriodValidity() && $transaction->getPeriodValidity() === $skip_expired) {
                $result[] = $transaction;
            }
        }
        return $result;
    }
}
