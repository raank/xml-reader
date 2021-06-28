<?php

namespace Raank\Tests\V1\Unit;

use Carbon\Carbon;
use Raank\Models\V1\File;
use Raank\Models\V1\User;
use Raank\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Raank\Repositories\V1\FileRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @category tests
 * @package Raank\Tests
 * @package V1\Unit\FileRepositoryTest
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FileRepositoryTest extends TestCase
{
    /**
     * Testing all method.
     *
     * @return void
     */
    public function testAllMethod()
    {
        /** Setting length to create users */
        $length = rand(1, 10);

        /** Making a factory users */
        File::factory($length)->create();

        /**
         * Instance a repository
         *
         * @var FileRepository $repository
         */
        $repository = (new FileRepository());

        /** Getting all method */
        $all = $repository->all();
        /**
         * Convert to array.
         *
         * PS: Converted to array because property $total is protected
         */
        $data = $all->toArray();

        /** Assertions to expect */
        $this->assertTrue($all instanceof LengthAwarePaginator);
        $this->assertEquals($data['total'], $length);
    }

    /**
     * Testing store method.
     *
     * @return mixed
     */
    public function testStoreMethod()
    {
        $filename = 'demo.xml';

        /** Instance a fake disk storage */
        Storage::fake('public');

        /** Getting content from demo file */
        $demoFile = Storage::disk('local')
            ->get($filename);

        /** Make a fake file with demo file content */
        $file = UploadedFile::fake()
            ->createWithContent($filename, $demoFile);

        /** @var User $user */
        $user = User::factory()
            ->create();

        /** Params to storing file */
        $data = [
            'user_id' => $user->id,
            'original' => $filename,
            'name' => $file->hashName(),
            'size' => $file->getSize(),
            'mimeType' => $file->getMimeType(),
        ];

        /**
         * Instance a repository
         *
         * @var FileRepository $repository
         */
        $repository = (new FileRepository());

        /** Getting store method */
        $store = $repository->store($data);

        /** Assertions to expect */
        $this->assertTrue($store instanceof File);
        $this->assertEquals($store->name, $data['name']);

        return $store;
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
        $file = Arr::first($args);

        /** Making a request with query builder dynamic */
        $requestParams = [
            'where' => [
                ['original', 'LIKE', Arr::get($file, 'data.original')],
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

        /** Instance a fake Request */
        $request = Request::create(
            '/api/v1/files/search',
            'POST',
            $requestParams
        );

        /**
         * Instance a repository
         *
         * @var FileRepository $repository
         */
        $repository = (new FileRepository());

        /** Getting search method of controller */
        $search = $repository->search($request);

        /**
         * Convert to array.
         *
         * PS: Converted to array because property $total is protected
         */
        $data = $search->toArray();

        /** Assertions to expect */
        $this->assertTrue($search instanceof LengthAwarePaginator);
        $this->assertTrue($data['total'] > 0);
    }

    /**
     * Testing find method.
     *
     * @return void
     */
    public function testFindMethod()
    {
        /** @var File $file */
        $file = File::factory()->create();

        /**
         * Instance a repository
         *
         * @var FileRepository $repository
         */
        $repository = (new FileRepository());
        /** Getting find method */
        $find = $repository->find($file->id);

        /** Assertions to expect */
        $this->assertTrue($find instanceof File);
        $this->assertEquals($find->original, 'demo.xml');
    }

    /**
     * Testing update method.
     *
     * @return void
     */
    public function testUpdateMethod()
    {
        /** @var File $file */
        $file = File::factory()->create();

        /**
         * Instance a repository
         *
         * @var FileRepository $repository
         */
        $repository = (new FileRepository());

        /** Getting update method */
        $update = $repository->update($file->id, [
            'original' => 'demo2.xml'
        ]);

        /** Getting find method */
        $find = $repository->find($file->id);

        /** Assertions to expect */
        $this->assertTrue($update);
        $this->assertEquals($find->original, 'demo2.xml');
    }

    /**
     * Testing destroy method.
     *
     * @return void
     */
    public function testDestroyMethod()
    {
        /** @var File $file */
        $file = File::factory()->create();

        /**
         * Instance a repository
         *
         * @var FileRepository $repository
         */
        $repository = (new FileRepository());
        /** Getting destroy method */
        $destroy = $repository->destroy($file->id);
        /** Getting find method to check item already deleted */
        $find = $repository->find($file->id);

        /** Assertions to expect */
        $this->assertTrue($destroy > 0);
        $this->assertTrue(\is_null($find));
    }
}
