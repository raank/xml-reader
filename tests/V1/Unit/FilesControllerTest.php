<?php

namespace Raank\Tests\V1\Unit;

use Carbon\Carbon;
use Raank\Models\V1\File;
use Raank\Models\V1\User;
use Raank\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use Laravel\Lumen\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Raank\Repositories\V1\FileRepository;
use Raank\Http\Controllers\V1\FilesController;

/**
 * @category tests
 * @package Raank\Tests
 * @package V1\Unit\FilesControllerTest
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FilesControllerTest extends TestCase
{
    /**
     * Testing store method.
     *
     * @return void
     */
    public function testStoreMethod()
    {
        $filename = 'demo.xml';

        /** Instance a fake storage disk */
        Storage::fake('public');

        /** Getting data of demo file */
        $demoFile = Storage::disk('local')
            ->get($filename);

        /** Create a fake file with content of demo file */
        $file = UploadedFile::fake()
            ->createWithContent($filename, $demoFile);

        /**
         * Making a new user.
         *
         * @var User $user
         */
        $user = User::factory()
            ->create();

        /** Make request params */
        $requestParams = [
            'user_id' => $user->id,
            'file'    => $file
        ];
    
        /** Instance a fake request */
        $request = Request::create(
            '/api/v1/files',
            'POST',
            $requestParams
        );

        /**
         * Instance a controller
         *
         * @var FilesController $controller
         */
        $controller = (new FilesController(
            (new FileRepository())
        ));

        /** Gettin method store of controller */
        $store = $controller->store($request);

        /** Assertions to expect */
        $this->assertTrue($store instanceof JsonResponse);
        $this->assertEquals(Response::HTTP_ACCEPTED, $store->getStatusCode());

        return \json_decode((string) $store->getContent(), true);
    }

    /**
     * Testing searching method.
     *
     * @depends testStoreMethod
     *
     * @return void
     */
    public function testSearchMethod(...$args)
    {
        /** Getting data received from testStoreMethod */
        $data = Arr::first($args);

        /** Making a request with query builder dynamic */
        $requestParams = [
            'where' => [
                ['original', 'LIKE', Arr::get($data, 'data.original')],
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
        ];
        
        /** Making a fake request */
        $request = Request::create(
            '/api/v1/files/search',
            'POST',
            $requestParams
        );

        /**
         * Instance a controller
         *
         * @var FilesController $controller
         */
        $controller = (new FilesController(
            (new FileRepository())
        ));

        /** Getting search method of controller */
        $search = $controller->search($request);

        /** Assertions to expect */
        $this->assertTrue($search instanceof JsonResponse);
        $this->assertEquals(Response::HTTP_OK, $search->getStatusCode());
    }

    /**
     * Testing show method.
     *
     * @depends testStoreMethod
     *
     * @param  array ...$args
     *
     * @return void
     */
    public function testShowMethod(...$args)
    {
        /** Getting id received from testStoreMethod */
        $itemId = Arr::get(
            Arr::first($args),
            'data.id'
        );

        /**
         * Instance a controller
         *
         * @var FilesController $controller
         */
        $controller = (new FilesController(
            (new FileRepository())
        ));

        /** Getting show method */
        $store = $controller->show($itemId);

        /** Assertions to expect */
        $this->assertTrue($store instanceof JsonResponse);
        $this->assertEquals(Response::HTTP_OK, $store->getStatusCode());
    }

    /**
     * Testing destroy method.
     *
     * @depends testStoreMethod
     *
     * @param  array ...$args
     *
     * @return void
     */
    public function testDestroyMethod(...$args)
    {
        $data = Arr::first($args);
        /** Getting id received from testStoreMethod */
        $itemId = Arr::get($data, 'data.id');

        /**
         * Instance controller.
         * 
         * @var FilesController $controller
         */
        $controller = (new FilesController(
            (new FileRepository())
        ));

        /** Getting method destroy */
        $store = $controller->destroy($itemId);

        /** Assertions to expect */
        $this->assertTrue($store instanceof JsonResponse);
        $this->assertEquals(Response::HTTP_OK, $store->getStatusCode());
    }
}
