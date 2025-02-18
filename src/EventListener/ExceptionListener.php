<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // If the requested route is not found (404)
        if ($exception instanceof NotFoundHttpException) {
            $response = new JsonResponse([
                'error' => 'Route not found',
                'message' => $exception->getMessage()
            ], JsonResponse::HTTP_NOT_FOUND);

            $event->setResponse($response);
        }

        // If the HTTP method is not allowed (405)
        if ($exception instanceof MethodNotAllowedHttpException) {
            $response = new JsonResponse([
                'error' => 'Method not allowed',
                'message' => $exception->getMessage()
            ], JsonResponse::HTTP_METHOD_NOT_ALLOWED);

            $event->setResponse($response);
        }
    }
}
