<?php

namespace Raank\Models\V1;

use Raank\Models\V1\File;
use Illuminate\Support\Str;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * @category models
 * @package Raank\Models
 * @subpackage V1\User
 * @version 1.0.0
 *
 * @OA\Schema(
 *  schema="v1.models.user",
 *  type="object",
 *  description="Response data of user",
 *  @OA\Property(property="id", type="integer", description="Identification of User", example=1),
 *  @OA\Property(property="name", type="string", description="Name of User", example="John Doe"),
 *  @OA\Property(property="email", type="string", description="Email of User", example="john@doe.com"),
 *  @OA\Property(property="document", type="string", description="Document of User", example="123456789"),
 *  @OA\Property(property="deleted_at", type="string", description="Date of Destroy", example=null),
 *  @OA\Property(property="updated_at", type="string", description="Date of last updated", example="2021-01-01T00:00:00.000000Z"),
 *  @OA\Property(property="created_at", type="string", description="Date of Created", example="2021-01-01T00:00:00.000000Z"),
 * )
 *
 * @property integer $id
 * @property string  $name
 * @property string  $email
 * @property string  $document
 * @property string  $remember_token
 * @property string  $password
 *
 * @method static find(string $itemId)
 * @method static where($params, string $operator, $values)
 * @method static factory(int $length)
 * 
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable,
        Authorizable,
        HasFactory,
        SoftDeletes;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',

        // encrypted
        'document',

        'remember_token',
        'password'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * The relation with a model Files.
     *
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'user_id');
    }

    /**
     * Set document encryptation.
     *
     * @param string|null $value
     *
     * @return void
     */
    public function setDocumentAttribute(string $value = null)
    {
        if (isset($value)) {
            $this->attributes['document'] = Crypt::encrypt($value);
        }
    }

    /**
     * Get document decrypted.
     *
     * @return string|null
     */
    public function getDocumentAttribute()
    {
        $document = $this->attributes['document'];

        if (!isset($document)) {
            return null;
        }

        try {
            return Crypt::decrypt($document);
        } catch (DecryptException $th) {
            return null;
        }
    }

    /**
     * Setting hash to Passwords.
     *
     * @param string $value
     *
     * @return void
     */
    public function setPasswordAttribute(string $value = null)
    {
        $this->attributes['password'] = !is_null($value) ? Hash::make($value) : null;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
