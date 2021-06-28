<?php

namespace Raank\Tests\V1\Api;

use Carbon\Carbon;
use Raank\Models\V1\User;
use Raank\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @category tests
 * @package Raank\Tests
 * @package V1\Api\FilesTest
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FilesTest extends TestCase
{
    /**
     * Testing storing user route.
     *
     * @return mixed
     */
    public function testStore()
    {
        $demoFile = Storage::disk('local')->get('demo.xml');
        Storage::fake('public');

        $file = UploadedFile::fake()
            ->createWithContent('demo.xml', $demoFile);

        /** @var User $user */
        $user = User::factory()
            ->create();

        $data = [
            'user_id' => $user
                ->id,
            'file' => $file,
        ];

        $this->post(
            '/api/v1/files',
            $data,
            [
                'x-app-token' => $this->appToken,
            ]
        )
            ->seeJson([
                'message' => 'Successful action'
            ])
            ->seeStatusCode(Response::HTTP_ACCEPTED);

        $content = $this->getContent(
            $this->response
        );

        Storage::disk('public')->assertExists($file->hashName());

        $this->assertTrue($content['data']['name'] === $file->hashName());

        return $content;
    }

    /**
     * Testing search route with pagination.
     * 
     * @depends testStore
     *
     * @return void
     */
    public function testSearch()
    {
        $this
            ->json(
                'POST',
                '/api/v1/files/search',
                [
                    'where' => [
                        ['name', 'LIKE', 'xml'],
                    ],

                    'whereNotNull' => ['mimeType'],
                    'whereNull' => ['deleted_at'],
                    'orderBy' => [
                        ['field' => 'created_at', 'order' => 'DESC'],
                    ],

                    'whereBetween' => [
                        [
                            'created_at' => [
                                Carbon::now()->startOfDay()->format('Y-m-d H:i:s'),
                                Carbon::now()->endOfDay()->format('Y-m-d H:i:s')
                            ]
                        ]
                    ]
                ],
                [
                    'x-app-token' => $this->appToken,
                ]
            )
            ->seeJson([
                'message' => __('Success'),
                'current_page' => 1,
                'total' => 1
            ])
            ->seeStatusCode(Response::HTTP_OK);
    }

    /**
     * Testing show file route.
     *
     * @depends testStore
     *
     * @return array
     */
    public function testShow(...$args)
    {
        $data = Arr::first($args);
        $itemId = Arr::get($data, 'data.id');

        $this
            ->json(
                'GET',
                '/api/v1/files/' . $itemId,
                [],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => __('Success')
            ])
            ->seeStatusCode(Response::HTTP_OK);
    
        return $this->getContent(
            $this->response
        );
    }

    /**
     * Testing show file error route.
     *
     * @return void
     */
    public function testShowNotFound()
    {
        $this
            ->json(
                'GET',
                '/api/v1/files/' . rand(1111, 9999),
                [],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => __('Notfound')
            ])
            ->seeStatusCode(Response::HTTP_NOT_FOUND);
    }

    /**
     * Testing destroy file route.
     *
     * @depends testShow
     *
     * @return void
     */
    public function testDestroy(...$args)
    {
        $data = Arr::first($args);
        $itemId = Arr::get($data, 'data.id');

        $this
            ->json(
                'DELETE',
                '/api/v1/files/' . $itemId,
                [],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => __('Success'),
            ])
            ->seeStatusCode(Response::HTTP_OK);
    }
}
