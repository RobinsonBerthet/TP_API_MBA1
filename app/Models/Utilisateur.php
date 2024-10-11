<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;


/**
 * Class Utilisateur
 * 
 * @property int $id
 * @property string $nom
 * @property string $email
 * @property string $motDePasse
 * @property int|null $role_id
 * @property string $statut
 * @property string $dateCreation
 * 
 * @property Role|null $role
 * @property Collection|Log[] $logs
 *
 * @package App\Models
 */
class Utilisateur extends Authenticatable implements JWTSubject
{
	protected $table = 'utilisateurs';
	protected $primaryKey = 'id';
	public $timestamps = false;

	protected $casts = [
		'role_id' => 'int'
	];

	protected $fillable = [
		'nom',
		'email',
		'role_id',
		'motDePasse',
		'statut',
		'dateCreation'
	];

	public function getJWTIdentifier()
    {
        return $this->getKey();
    }

	public function getJWTCustomClaims()
    {
        return [];
    }

	public function getAuthPassword()
    {
        return $this->motDePasse;
    }

	public function role()
	{
		return $this->belongsTo(Role::class);
	}

	public function logs()
	{
		return $this->hasMany(Log::class);
	}
}
