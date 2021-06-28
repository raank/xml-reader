<?php

namespace Raank\Http\Controllers\V1;

use Raank\Models\V1\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Raank\Http\Controllers\Controller;
use Raank\Repositories\V1\UserRepository;
use Raank\Http\Controllers\UsersControllerInterface;

/**
 * @category controllers
 * @package Raank\Http\Controllers
 * @subpackage V1\UsersController
 * @version 1.0.0
 * 
 * @OA\Schema(
 *  schema="v1.paginated.users",
 *  type="object",
 *  description="Response CRUD paginated",
 *  @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
 *  @OA\Property(
 *      property="data",
 *      type="array",
 *      @OA\Items(ref="#/components/schemas/v1.models.user")
 *  ),
 *  @OA\Property(property="current_page", type="integer", description="Current page", example=1),
 *  @OA\Property(property="first_page_url", type="string", description="First page URL", example="http://localhost"),
 *  @OA\Property(property="from", type="integer", description="From start items", example=1),
 *  @OA\Property(property="last_page", type="integer", description="Last Page Number", example=1),
 *  @OA\Property(property="last_page_url", type="string", description="Last page URL", example="http://localhost"),
 *  @OA\Property(
 *      property="links",
 *      type="array",
 *      description="List of Links",
 *      @OA\Items(ref="#/components/schemas/v1.pagination.links")
 *  ),
 *  @OA\Property(property="next_page_url", type="string", description="Next page URL", example="http://localhost"),
 *  @OA\Property(property="path", type="string", description="Path of current URL", example="http://localhost"),
 *  @OA\Property(property="per_page", type="integer", description="Items number per page", example=1),
 *  @OA\Property(property="prev_page_url", type="string", description="Prev page URL", example="http://localhost"),
 *  @OA\Property(property="to", type="integer", description="Items to end page", example=1),
 *  @OA\Property(property="total", type="integer", description="Total of Items", example=1)
 * )
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class UsersController extends Controller implements UsersControllerInterface
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
    ) {}

    /**
     * All users.
     *
     * @OA\Get(
     *  tags={"v1.users"},
     *  path="/v1/users",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(ref="#/components/schemas/v1.paginated.users")
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
     *  )
     * )
     *
     * @inheritDoc
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository
            ->all(
                (int) $request->query
                    ->get('perPage', '10')
            );

        return response()
            ->json(
                array_merge(
                    [
                        'message' => __('Successful action'),
                    ],
                    $data->toArray()
                )
            );
    }

    /**
     * Storing a new User.
     *
     * @OA\Post(
     *  tags={"v1.users"},
     *  path="/v1/users",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *              @OA\Property(property="data", ref="#/components/schemas/v1.models.user")
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
     *                  "document": "12345678"
     *              }
     *          )
     *      )
     *  )
     * )
     *
     * @inheritDoc
     */
    public function store(Request $request): JsonResponse
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
        ]);

        return response()
            ->json([
                'message' => __('Successful action'),
                'data' => $this->repository
                    ->store(
                        $request->all()
                    )
            ]);
    }

    /**
     * Show user specified.
     *
     * @OA\Get(
     *  tags={"v1.users"},
     *  path="/v1/users/{userId}",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Parameter(
     *      name="userId",
     *      in="path",
     *      required=true,
     *      description="Identification of User",
     *      example="60aeba949828bb0c57abc123",
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
     *              @OA\Property(property="data", ref="#/components/schemas/v1.models.user")
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
    public function show(int $userId): JsonResponse
    {
        $this->exists($userId, User::class);

        return response()
            ->json([
                'message' => __('Successful action'),
                'data' => $this->repository->find($userId)
            ]);
    }

    /**
     * Update user specified.
     *
     * @OA\Put(
     *  tags={"v1.users"},
     *  path="/v1/users/{userId}",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Parameter(
     *      name="userId",
     *      in="path",
     *      required=true,
     *      description="Identification of User",
     *      example="60aeba949828bb0c57abc123",
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
     *              @OA\Property(property="data", ref="#/components/schemas/v1.models.user")
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
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="name", type="string", description="The name of user."),
     *              @OA\Property(property="email", type="string", description="The email of user."),
     *              @OA\Property(property="document", type="string", description="The document of user."),
     *              example={
     *                  "name": "John Doe",
     *                  "email": "john@doe.com",
     *                  "document": "12345678"
     *              }
     *          )
     *      )
     *  )
     * )
     *
     * @inheritDoc
     */
    public function update(Request $request, int $userId): JsonResponse
    {
        $this->exists($userId, User::class);

        $this->validate($request, [
            'name' => ['string'],
            'email' => ['email', 'unique:users,email,' . $userId . ',id'],
            'document' => ['string'],
            'active' => ['boolean']
        ]);

        $this->repository
            ->update(
                $userId,
                $request->all()
            );
        
        return response()
            ->json([
                'message' => __('Successful action'),
                'data' => $this->repository->find($userId)
            ]);
    }

    /**
     * Delete user specified.
     *
     * @OA\Delete(
     *  tags={"v1.users"},
     *  path="/v1/users/{userId}",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Parameter(
     *      name="userId",
     *      in="path",
     *      required=true,
     *      description="Identification of User",
     *      example="60aeba949828bb0c57abc123",
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
    public function destroy(int $userId): JsonResponse
    {
        $this->exists($userId, User::class);

        $deleted = $this->repository
            ->destroy($userId);

        return response()
            ->json([
                'message' => __('Successful action'),
                'data' => $deleted
            ]);
    }

    /**
     * Searching users.
     *
     * @OA\Post(
     *  tags={"v1.users"},
     *  path="/v1/users/search",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(ref="#/components/schemas/v1.paginated.users")
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
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="where", description="Where Condition."),
     *              @OA\Property(property="whereNotNull", description="Where field not null."),
     *              @OA\Property(property="whereNull", description="Where field is nullable."),
     *              @OA\Property(property="orderBy", description="The password confirmation."),
     *              @OA\Property(property="whereBetween", description="The where between of user."),
     *              example={
     *                  "where": {
     *                      {"field_name", "operator", "value"},
     *                      {"name", "LIKE", "john"}
     *                  },
     *                  "whereNotNull": {"field_name"},
     *                  "whereNull": {"field_name"},
     *                  "orderBy": {
     *                      {"field_name": "field_name", "order": "DESC"}
     *                  },
     *                  "whereBetween": {
     *                      {"field_name": {"from_value", "to_value"}}
     *                  }
     *              }
     *          )
     *      )
     *  )
     * )
     *
     * @inheritDoc
     */
    public function search(Request $request): JsonResponse
    {
        $data = $this->repository
            ->search($request);

        return response()
            ->json(
                array_merge(
                    [
                        'message' => __('Successful action'),
                    ],
                    $data->toArray()
                )
            );
    }
}
