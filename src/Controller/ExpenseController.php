<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Repository\ExpenseRepository;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/expenses', name: 'api_expense_')]
class ExpenseController extends BaseController
{
    private $entityManager;
    private $expenseRepository;
    private $categoryRepository;
    private $userRepository;
    private $serializer;
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ExpenseRepository $expenseRepository,
        CategoryRepository $categoryRepository,
        UserRepository $userRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    )
    {
        $this->entityManager = $entityManager;
        $this->expenseRepository = $expenseRepository;
        $this->categoryRepository = $categoryRepository;
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
        
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
            $expense = $this->expenseRepository->findAllByUserId($user->getId());

            if (!$expense) {
                return new JsonResponse(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
            }

            $data = $this->serializer->serialize($expense, 'json');
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $expense = $this->expenseRepository->find($id);

            if (!$expense) {
                return new JsonResponse(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
            }

            $data = $this->serializer->serialize($expense, 'json');
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
       // try {
            $data = json_decode($request->getContent(), true);
        
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
          
            if (!isset( $data['categoryId'], $data['amount'], $data['expenseDate'])) {
                return new JsonResponse(['message' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
            }

            $category = $this->categoryRepository->find($data['categoryId']);
            if (!$category) {
                return new JsonResponse(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
            }

            $expense = new Expense();
            $expense->setUserId($user->getId());
            $expense->setCategoryId($data['categoryId']);
            $expense->setAmount($data['amount']);
            $expense->setDescription($data['description'] ?? null);
            $expense->setExpenseDate(new \DateTime($data['expenseDate'])); // Set the manual date
            $expense->setCreatedAt(new \DateTime());
           
            $errors = $this->validator->validate($expense);
            if (count($errors) > 0) {
                $errorsArray = [];
                foreach ($errors as $error) {
                    $errorsArray[] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($expense);
            $this->entityManager->flush();

            $data = $this->serializer->serialize($expense, 'json');
            return new JsonResponse(json_decode($data, true), Response::HTTP_CREATED);

        // } catch (\Exception $e) {
        //      return $this->handleException($e);
        // }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
           
            $expense = $this->expenseRepository->find($id);

            if (!$expense) {
                return new JsonResponse(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset( $data['categoryId'], $data['amount'], $data['expenseDate'])) {
                return new JsonResponse(['message' => 'Invalid data. Missing required fields.'], Response::HTTP_BAD_REQUEST);
            }

            $category = $this->categoryRepository->find($data['categoryId']);
            if (!$category) {
                return new JsonResponse(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
            }

            $expense->setUserId($user->getId());
            $expense->setCategoryId($data['categoryId']);
            $expense->setAmount($data['amount']);
            $expense->setDescription($data['description'] ?? null);
            $expense->setExpenseDate(new \DateTime($data['expenseDate'])); // Set the manual date

            $errors = $this->validator->validate($expense);
            if (count($errors) > 0) {
                $errorsArray = [];
                foreach ($errors as $error) {
                    $errorsArray[] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            $data = $this->serializer->serialize($expense, 'json');
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $expense = $this->expenseRepository->find($id);

            if (!$expense) {
                return new JsonResponse(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($expense);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Expense deleted'], Response::HTTP_NO_CONTENT);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
