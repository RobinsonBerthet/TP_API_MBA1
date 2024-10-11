<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Fonctionnalite
 * 
 * @property int $id
 * @property string $nom_fonctionnalite
 * @property string|null $description
 * 
 * @property Collection|Droit[] $droits
 * @property Collection|Log[] $logs
 *
 * @package App\Models
 */
class Fonctionnalite extends Model
{
	protected $table = 'fonctionnalites';
	public $timestamps = false;

	protected $fillable = [
		'nom_fonctionnalite',
		'description'
	];

	public function droits()
	{
		return $this->hasMany(Droit::class);
	}

	public function logs()
	{
		return $this->hasMany(Log::class);
	}
}
