<?php
namespace App\Controller;

use App\Repository\ExpenseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Route('/api/reports', name: 'api_report_')]
class ReportController extends BaseController
{
    private $expenseRepository;
    private $entityManager;

    public function __construct(
        ExpenseRepository $expenseRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->expenseRepository = $expenseRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/total-expenses-per-category/{month}', name: 'total_expenses_per_category', methods: ['GET'])]
    public function totalExpensesPerCategory(string $month): JsonResponse
    {
        try {
            $startDate = new \DateTime("$month-01");
            $endDate = (clone $startDate)->modify('last day of this month');
    
            $expenses = $this->expenseRepository->findTotalExpensesPerCategory($startDate, $endDate);
    
            if (empty($expenses)) {
                return new JsonResponse(['message' => 'No expenses found for the given month'], Response::HTTP_NOT_FOUND);
            }
    
            $labels = [];
            $values = [];
    
            foreach ($expenses as $expense) {
                $labels[] = $expense['category'];
                $values[] = $expense['total']; 
            }
    
            $formattedData = [
                'labels' => $labels,
                'values' => $values,
            ];
    
            return new JsonResponse($formattedData, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    #[Route('/average-daily-expenses/{month}', name: 'average_daily_expenses', methods: ['GET'])]
    public function averageDailyExpenses(string $month): JsonResponse
    {
        try {
            $startDate = new \DateTime("$month-01");
            $endDate = (clone $startDate)->modify('last day of this month');

            $expenses = $this->expenseRepository->findTotalExpenses($startDate, $endDate);

            $totalExpenses = 0;
            $daysInMonth = $endDate->format('t');
            $dailyExpenses = [];

            foreach ($expenses as $expense) {
                $date = $expense['expenseDate']->format('Y-m-d'); 
                $amount = $expense['totalAmount'];
                $dailyExpenses[$date] = $amount;
                $totalExpenses += $amount;
            }

            $averageDaily = $daysInMonth > 0 ? $totalExpenses / $daysInMonth : 0;

            return new JsonResponse([
                'days' => $dailyExpenses,
                'total_expenses' => $totalExpenses,
                'average_daily_expenses' => $averageDaily
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route('/total-expenses-per-user', name: 'total_expenses_per_user', methods: ['GET'])]
    public function totalExpensesPerUser(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
            $totalExpenses = $this->expenseRepository->findTotalExpensesByUser($user->getId());
    
            if (empty($totalExpenses)) {
                return new JsonResponse(['message' => 'No expenses found for the given user'], Response::HTTP_NOT_FOUND);
            }
    
           
            $formattedData = [
                'labels' => array_column($totalExpenses, 'category'), 
                'values' => array_column($totalExpenses, 'totalAmount') 
            ];
    
            return new JsonResponse($formattedData, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    

}
