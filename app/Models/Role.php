<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Role
 * 
 * @property int $id
 * @property string $nom_role
 * 
 * @property Collection|Droit[] $droits
 * @property Collection|Utilisateur[] $utilisateurs
 *
 * @package App\Models
 */
class Role extends Model
{
	protected $table = 'roles';
	public $timestamps = false;

	protected $fillable = [
		'nom_role'
	];

	public function droits()
	{
		return $this->hasMany(Droit::class);
	}

	public function utilisateurs()
	{
		return $this->hasMany(Utilisateur::class);
	}
}
