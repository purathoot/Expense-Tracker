<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories', name: 'api_category_')]
class CategoryController extends BaseController
{
    private $entityManager;
    private $categoryRepository;
    private $serializer;
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    )
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        try {
            $categories = $this->categoryRepository->findAll();

            if (empty($categories)) {
                return new JsonResponse(['message' => 'No categories found'], Response::HTTP_NOT_FOUND);
            }

            $data = $this->serializer->serialize($categories, 'json');
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->categoryRepository->find($id);

            if (!$category) {
                return new JsonResponse(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
            }

            $data = $this->serializer->serialize($category, 'json');
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['categoryName'])) {
                return new JsonResponse(['message' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
            }

            $category = new Category();
            $category->setCategoryName($data['categoryName']);
            $category->setCreatedAt(new \DateTime());

            $errors = $this->validator->validate($category);
            if (count($errors) > 0) {
                $errorsArray = [];
                foreach ($errors as $error) {
                    $errorsArray[] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $data = $this->serializer->serialize($category, 'json');
            return new JsonResponse(json_decode($data, true), Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $category = $this->categoryRepository->find($id);

            if (!$category) {
                return new JsonResponse(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['categoryName'])) {
                $category->setCategoryName($data['categoryName']);
            }

            $errors = $this->validator->validate($category);
            if (count($errors) > 0) {
                $errorsArray = [];
                foreach ($errors as $error) {
                    $errorsArray[] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
            }

            $category->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();

            $data = $this->serializer->serialize($category, 'json');
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $category = $this->categoryRepository->find($id);

            if (!$category) {
                return new JsonResponse(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($category);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Category deleted'], Response::HTTP_NO_CONTENT);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
