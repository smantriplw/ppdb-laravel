<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Archive extends Authenticatable implements JWTSubject
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nik',
        'nisn',
        'name',
        'mother_name',
        'father_name',
        'birthday',
        'school',
        'graduated_year',
        'phone',
        'address',
        'email',
        'type',
        'religion',
        'gender',
        
        'photo_path',
        'skhu_path',
        'certificate_path',
        'kip_path',
        'kk_path',
        'mutation_path',
        'verificator_id',
    ];

    public function nilai(): BelongsToMany {
        return $this->belongsToMany(NilaiSemester::class, 'archive_id');
    }

    public function verificator(): BelongsTo {
        return $this->belongsTo(User::class, 'verificator_id', 'id');
    }

    public function verifyData(): BelongsTo {
        return $this->belongsTo(VerifyModel::class, 'id', 'archive_id');
    }

    public function daftarUlang(): BelongsTo {
        return $this->belongsTo(DaftarUlangModel::class, 'archive_id');
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
    public function getJWTCustomClaims()
    {
        return [];
    }

}
