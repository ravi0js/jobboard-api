<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'JobBoard API',
    version: '1.0.0',
    description: 'Production-grade Job Board REST API built with Laravel, Sanctum, Redis and RBAC.',
    contact: new OA\Contact(email: 'ravi194455@gmail.com', name: 'Ravi Kumar')
)]
#[OA\Server(url: '/api', description: 'API Server')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter: Bearer {token}'
)]
abstract class Controller
{
}
