<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Droit
 * 
 * @property int $id
 * @property string $description
 * @property int|null $fonctionnalite_id
 * @property int|null $role_id
 * 
 * @property Fonctionnalite|null $fonctionnalite
 * @property Role|null $role
 *
 * @package App\Models
 */
class Droit extends Model
{
	protected $table = 'droits';
	public $timestamps = false;

	protected $casts = [
		'fonctionnalite_id' => 'int',
		'role_id' => 'int'
	];

	protected $fillable = [
		'description',
		'fonctionnalite_id',
		'role_id'
	];

	public function fonctionnalite()
	{
		return $this->belongsTo(Fonctionnalite::class);
	}

	public function role()
	{
		return $this->belongsTo(Role::class);
	}
}
