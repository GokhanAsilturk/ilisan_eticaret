<?php

/**
 * @OA\Info(
 *     title="İlisan E-Ticaret API",
 *     version="1.0.0",
 *     description="İlisan E-Ticaret Backend API - Laravel 11 tabanlı RESTful API",
 *     @OA\Contact(
 *         email="api@ilisan.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost/api",
 *     description="Development API Server"
 * )
 *
 * @OA\Server(
 *     url="https://api.ilisan.com.tr",
 *     description="Production API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum Bearer Token"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization"
 * )
 *
 * @OA\Tag(
 *     name="Products",
 *     description="Product management and catalog"
 * )
 *
 * @OA\Tag(
 *     name="Cart",
 *     description="Shopping cart operations"
 * )
 *
 * @OA\Tag(
 *     name="Orders",
 *     description="Order management and tracking"
 * )
 *
 * @OA\Tag(
 *     name="Checkout",
 *     description="Checkout and payment process"
 * )
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
