<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        // Log detallado del error
        logger()->error('Excepción capturada', [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'input' => $request->all(),
            'user_id' => optional($request->user())->id, // si está autenticado
        ]);

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $exception->errors()
            ], 422);
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->json(['message' => 'Ruta no encontrada'], 404);
        }

        return response()->json([
            'message' => $exception->getMessage(),
        ], 500);
    }
}
