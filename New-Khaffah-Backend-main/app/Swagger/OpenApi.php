<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     title="Kaffah API",
 *     version="1.0.0",
 *     description="Dokumentasi API Kaffah (frontend & admin)"
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Local development"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class OpenApi
{
    // This class only holds OpenAPI annotations for swagger-php scanning.
}
