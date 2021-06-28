<?php

namespace Raank\Http\Controllers\V1;

use Raank\Models\V1\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Raank\Processors\AwsSQS;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Raank\Jobs\SendMailMailgunSQS;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Raank\Http\Controllers\Controller;
use Raank\Repositories\V1\UserRepository;
use Raank\Repositories\V1\MessageRepository;
use Raank\Http\Controllers\AuthControllerInterface;

/**
 * @category controllers
 * @package Raank\Http\Controllers
 * @subpackage V1\AuthController
 * @version 1.0.0
 * 
 * @OA\Schema(
 *  schema="v1.token",
 *  type="object",
 *  description="Response auth token",
 *  @OA\Property(property="token", type="string", description="Token access", example="abc1234defg"),
 *  @OA\Property(property="type", type="string", description="Type of Token", example="Bearer"),
 *  @OA\Property(property="expires", type="integer", description="Expires token in", example=3600)
 * )
 * 
 * @OA\Schema(
 *  schema="v1.auth.response",
 *  type="object",
 *  description="Response data of Authentication",
 *  @OA\Property(property="auth", ref="#/components/schemas/v1.token"),
 *  @OA\Property(property="user", ref="#/components/schemas/v1.models.user"),
 * )
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class AuthController extends Controller implements AuthControllerInterface
{
    /**
     * Create a new controller instance.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param UserRepository $repository
     *
     * @return void
     */
    public function __construct(
        private UserRepository $repository
    ) {
        $this->middleware(
            'auth:api',
            [
                'except' => [
                    'register',
                    'login',
                    'forgot',
                    'reset'
                ]
            ]
        );
    }

    /**
     * User Registering on Application.
     *
     * @OA\Post(
     *  tags={"v1.auth"},
     *  path="/v1/auth/register",
     *  @OA\Response(
     *      response="201",
     *      description="Information has been successfully registered",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Information has been successfully registered"),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/v1.auth.response"
     *              )
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="422",
     *      description="There is some incorrect information",
     *      @OA\JsonContent(ref="#/components/schemas/Validation"),
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="name", type="string", description="The name of user."),
     *              @OA\Property(property="email", type="string", description="The email of user."),
     *              @OA\Property(property="password", type="string", description="The password of user."),
     *              @OA\Property(property="password_confirmation", type="string", description="The password confirmation."),
     *              @OA\Property(property="document", type="string", description="The document of user."),
     *              required={"name", "email", "password", "password_confirmation"},
     *              example={
     *                  "name": "John Doe",
     *                  "email": "john@doe.com",
     *                  "password": "password123",
     *                  "password_confirmation": "password123",
     *                  "document": "12345678910"
     *              }
     *          )
     *      )
     *  )
     * )
     *
     * @inheritDoc
     */
    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'type' => $request->get('type', 'default'),
            'active' => $request->get('active', true),
            'password' => $request->get('password', null),
            'remember_token' => $request->get('remember_token', Str::random(32))
        ]);

        $this->validate($request, [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'document' => ['string'],
            'active' => ['boolean'],
            'password' => ['string', 'confirmed'],
            'password_confirmation' => ['required']
        ]);

        $user = $this->repository
            ->store(
                $request->all()
            );

        $auth = [
            'token' => Auth::attempt($request->only(['email', 'password'])),
            'type' => 'bearer',
            'expires' => 3600
        ];

        return response()
            ->json([
                'message' => __('Success'),
                'data' => compact('auth', 'user')
            ], JsonResponse::HTTP_CREATED);
    }

    /**
     * User login on Application.
     *
     * @OA\Post(
     *  tags={"v1.auth"},
     *  path="/v1/auth/login",
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/v1.auth.response"
     *              )
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="422",
     *      description="There is some incorrect information",
     *      @OA\JsonContent(ref="#/components/schemas/Validation"),
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="email", type="string", description="The email of user."),
     *              @OA\Property(property="password", type="string", description="The password of user."),
     *              required={"email", "password"},
     *              example={
     *                  "email": "john@doe.com",
     *                  "password": "password123"
     *              }
     *          )
     *      )
     *  )
     * )
     *
     * @inheritDoc
     */
    public function login(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['string'],
        ]);

        $user = $this->repository
            ->findByField(
                'email',
                $request->get('email')
            );

        $token = Auth::attempt($request->only(['email', 'password']));

        if (! $token) {
            return $this->invalidCredentials();
        }

        $auth = [
            'token' => $token,
            'type' => 'bearer',
            'expires' => 3600
        ];

        return response()
            ->json([
                'message' => __('Success'),
                'data' => compact('auth', 'user')
            ], JsonResponse::HTTP_OK);
    }

    /**
     * User forgot password.
     *
     * @OA\Post(
     *  tags={"v1.auth"},
     *  path="/v1/auth/forgot",
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  ),
     *  @OA\Response(
     *      response="422",
     *      description="There is some incorrect information",
     *      @OA\JsonContent(ref="#/components/schemas/Validation"),
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="email", type="string", description="The email of user."),
     *              required={"email", "password"},
     *              example={
     *                  "email": "john@doe.com"
     *              }
     *          )
     *      )
     *  )
     * )
     *
     * @inheritDoc
     */
    public function forgot(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = $this->repository->findByField('email', $request->get('email'));

        $url = route('api.v1.auth.reset', [
            'token' => $user->remember_token
        ]);

        if (env('APP_ENV') !== 'testing') {
            Mail::send(
                'emails.auth.forgot',
                compact('user', 'url'),
                function ($mail) use ($user) {
                    Log::info(get_class($mail));
                    $mail
                        ->from(
                            env('MAIL_FROM_ADDRESS'),
                            env('MAIL_FROM_NAME')
                        )
                        ->to(
                            $user->email,
                            $user->name
                        )
                        ->subject('Reset your Password');
                }
            );
        }

        return response()
            ->json([
                'message' => __('Success')
            ], JsonResponse::HTTP_OK);
    }

    /**
     * User reset password.
     *
     * @OA\Post(
     *  tags={"v1.auth"},
     *  path="/v1/auth/reset/{remember_token}",
     *  @OA\Parameter(
     *      name="remember_token",
     *      in="path",
     *      required=true,
     *      description="Remember token of User",
     *      example="ABc123DefG",
     *      @OA\Schema(
     *          type="string"
     *      )
     *  ),
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  ),
     *  @OA\Response(
     *      response="422",
     *      description="There is some incorrect information",
     *      @OA\JsonContent(ref="#/components/schemas/Validation"),
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="password", type="string", description="The password of user."),
     *              @OA\Property(property="password_confirmation", type="string", description="The password confirmation."),
     *              required={"password", "password_confirmation"},
     *              example={
     *                  "password": "password123",
     *                  "password_confirmation": "password123"
     *              }
     *          )
     *      )
     *  )
     * )
     *
     * @inheritDoc
     */
    public function reset(Request $request, string $token): JsonResponse
    {
        $this->validate($request, [
            'password' => ['string', 'confirmed'],
            'password_confirmation' => ['required']
        ]);

        $request->merge([
            'remember_token' => Str::random(32)
        ]);

        $user = $this->repository
            ->findByField('remember_token', $token);

        if (! $user) {
            return $this->notfound();
        }

        $updated = $this->repository
            ->update(
                $user->id,
                $request->only([
                    'password',
                    'remember_token'
                ])
            );

        return response()
            ->json([
                'message' => __('Success'),
                'data' => compact('updated')
            ], JsonResponse::HTTP_OK);
    }

    /**
     * Checking if user is authenticated.
     *
     * @OA\Head(
     *  tags={"v1.auth"},
     *  path="/v1/auth/check",
     *  security={
     *      {"bearerAuth": {}}
     *  },
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="401",
     *      description="You are not authorized for this action",
     *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  )
     * )
     *
     * @inheritDoc
     */
    public function check(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!isset($user)) {
            return $this->unauthorized();
        }

        return response()
            ->json([
                'message' => __('Success'),
                'data' => $user
            ], JsonResponse::HTTP_OK);
    }

    /**
     * User refresh token.
     *
     * @OA\Get(
     *  tags={"v1.auth"},
     *  path="/v1/auth/refresh",
     *  security={
     *      {"bearerAuth": {}}
     *  },
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/v1.auth.response"
     *              )
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="401",
     *      description="You are not authorized for this action",
     *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  )
     * )
     *
     * @inheritDoc
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!isset($user)) {
            return $this->unauthorized();
        }

        $auth = [
            'token' => auth()->refresh(),
            'type' => 'bearer',
            'expires' => 3600
        ];

        return response()
            ->json([
                'message' => __('Success'),
                'data' => compact('auth', 'user')
            ], JsonResponse::HTTP_OK);
    }
}
