<?php

namespace Database\Factories\V1;

use Raank\Models\V1\File;
use Raank\Models\V1\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @category database
 * @package Raank\Database\Factories
 * @subpackage V1\FileFactory
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        Storage::fake('public');

        $filename = 'demo.xml';
        $demoFile = Storage::disk('local')
            ->get($filename);

        $file = UploadedFile::fake()
            ->createWithContent($filename, $demoFile);

        /** @var User $user */
        $user = User::factory()
            ->create();

        return [
            'user_id' => $user->id,
            'original' => $filename,
            'name' => $file->hashName(),
            'size' => $file->getSize(),
            'mimeType' => $file->getMimeType(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
