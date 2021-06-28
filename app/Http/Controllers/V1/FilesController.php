<?php

namespace Raank\Http\Controllers\V1;

use Exception;
use Carbon\Carbon;
use Raank\Models\V1\File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Raank\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Raank\Http\Controllers\FilesControllerInterface;
use Raank\Repositories\V1\FileRepository as Repository;

/**
 * @category controllers
 * @package Raank\Http\Controllers
 * @subpackage V1\FilesController
 * @version 1.0.0
 * 
 * @OA\Schema(
 *  schema="v1.paginated.files",
 *  type="object",
 *  description="Response CRUD paginated",
 *  @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
 *  @OA\Property(
 *      property="data",
 *      type="array",
 *      @OA\Items(ref="#/components/schemas/v1.models.file")
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
class FilesController extends Controller implements FilesControllerInterface
{
    /**
     * Create a new controller instance.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Repository $repository
     *
     * @return void
     */
    public function __construct(
        private Repository $repository
    ) {}

    /**
     * Storing a new File to User.
     *
     * @OA\Post(
     *  tags={"v1.files"},
     *  path="/v1/files",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Response(
     *      response="202",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *              @OA\Property(property="data", ref="#/components/schemas/v1.models.file")
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
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              @OA\Property(property="file", description="The file to parse."),
     *              @OA\Property(property="user_id", type="integer", description="The user id to relation.", example=1),
     *              required={"file", "user_id"}
     *          )
     *      )
     *  )
     * )
     *
     * @inheritDoc
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'file' => ['required', 'file']
        ]);

        $file = $request->file('file') ?? $request->get('file');

        if (\is_null($file)) {
            return $this->unprocessable('The file field is null');
        }

        $name = $file->getClientOriginalName();

        // make name hashed with date now
        $hashedName = $file->hashName();

        // storage file an path
        Storage::disk('public')
            ->put(
                $hashedName,
                /* @phpstan-ignore-next-line */
                \file_get_contents($file)
            );

        $request->merge([
            'user_id' => (int) $request->get('user_id'),
            'original' => $name,
            'name' => $hashedName,
            'size' => $file->getSize(),
            'mimeType' => $file->getClientMimeType()
        ]);

        $store = $this
            ->repository
            ->store(
                $request->except('file')
            );

        return response()
            ->json([
                'message' => __('Successful action'),
                'data' => $store
            ], JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * Show specified file.
     *
     * @OA\Get(
     *  tags={"v1.files"},
     *  path="/v1/files/{fileId}",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Parameter(
     *      name="fileId",
     *      in="path",
     *      required=true,
     *      description="Identification of File",
     *      example=2,
     *      @OA\Schema(
     *          type="integer"
     *      )
     *  ),
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *              @OA\Property(property="data", ref="#/components/schemas/v1.models.file")
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
    public function show(int $fileId): JsonResponse
    {
        $this->exists($fileId, File::class);

        return response()
            ->json([
                'message' => __('Successful action'),
                'data' => $this->repository->find($fileId)
            ]);
    }

    /**
     * Delete specified file.
     *
     * @OA\Delete(
     *  tags={"v1.files"},
     *  path="/v1/files/{fileId}",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Parameter(
     *      name="fileId",
     *      in="path",
     *      required=true,
     *      description="Identification of File",
     *      example=2,
     *      @OA\Schema(
     *          type="integer"
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
    public function destroy(int $fileId): JsonResponse
    {
        $this->exists($fileId, File::class);

        $deleted = $this->repository
            ->destroy($fileId);

        return response()
            ->json([
                'message' => __('Successful action'),
                'data' => $deleted
            ]);
    }

    /**
     * Searching files.
     *
     * @OA\Post(
     *  tags={"v1.files"},
     *  path="/v1/files/search",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(ref="#/components/schemas/v1.paginated.files")
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
     *              @OA\Property(property="whereBetween", description="The filename of file."),
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
