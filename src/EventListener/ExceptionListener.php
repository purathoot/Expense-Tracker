<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Psr\Log\LoggerInterface;

class ExceptionListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = $this->createJsonResponse($exception);
        $event->setResponse($response);
    }

    private function createJsonResponse(\Throwable $exception): JsonResponse
    {
        // Log the exception 
        // $this->logger->error($exception->getMessage());

        if ($exception instanceof NotFoundHttpException) {
            return new JsonResponse(['message' => $exception->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } elseif ($exception instanceof BadRequestHttpException) {
            return new JsonResponse(['message' => $exception->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'An error occurred'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
