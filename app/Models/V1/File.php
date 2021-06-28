<?php

namespace Raank\Models\V1;

use ErrorException;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @category models
 * @package Raank\Models
 * @subpackage V1\File
 * @version 1.0.0
 *
 * @OA\Schema(
 *  schema="v1.models.file",
 *  type="object",
 *  description="Response data of user",
 *  @OA\Property(property="id", type="integer", description="Identification of User", example=1),
 *  @OA\Property(property="original", type="string", description="The file original name.", example="my-file.xml"),
 *  @OA\Property(property="name", type="string", description="The file name.", example="file_hashed.xml"),
 *  @OA\Property(property="path", type="string", description="Email of User", example="/path/to/file_hashed.xml"),
 *  @OA\Property(property="size", type="integer", description="The file size", example=1234),
 *  @OA\Property(property="mimeType", type="string", description="The file mime type", example="application/xml"),
 *  @OA\Property(property="content", type="object", description="The content of file"),
 *  @OA\Property(property="updated_at", type="string", description="Date of last updated", example="2021-01-01T00:00:00.000000Z"),
 *  @OA\Property(property="created_at", type="string", description="Date of Created", example="2021-01-01T00:00:00.000000Z"),
 *  @OA\Property(property="deleted_at", type="string", description="Date of Destroy", example=null),
 * )
 *
 * @property integer $id
 * @property string  $original
 * @property string  $name
 * @property string  $size
 * @property string  $mime_type
 *
 * @method static find(string $itemId)
 * @method static where($params, string $operator, $values)
 * @method static factory(int $length)
 * 
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class File extends Model
{
    use SoftDeletes,
        HasFactory;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'original',
        'name',
        'size',
        'mimeType'
    ];

    /**
     * The attributes for appends object.
     *
     * @var array
     */
    protected $appends = [
        'path',
        'contents'
    ];

    /**
     * The path to folder.
     *
     * @var string
     */
    public const FILE_PATH = 'app/public/files';

    /**
     * Getting path of file.
     *
     * @return mixed
     */
    public function getPathAttribute()
    {
        return sprintf(
            './storage/%s/%s',
            self::FILE_PATH,
            $this->attributes['name']
        );
    }

    /**
     * Getting the content of file.
     *
     * @return mixed
     */
    public function getContentsAttribute()
    {
        $name = $this->attributes['name'];

        if (!is_null($name) && Storage::disk('public')->exists($name)) {
            $contents = Storage::disk('public')->get($name);

            try {
                return \simplexml_load_string($contents);
            } catch (ErrorException $th) {
                return $contents;
            }
        }

        return null;
    }
}
