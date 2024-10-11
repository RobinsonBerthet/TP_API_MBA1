<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Log
 * 
 * @property int $id
 * @property Carbon $date_action
 * @property int|null $utilisateur_id
 * @property int|null $fonctionnalite_id
 * @property string|null $description_action
 * 
 * @property Utilisateur|null $utilisateur
 * @property Fonctionnalite|null $fonctionnalite
 *
 * @package App\Models
 */
class Log extends Model
{
	protected $table = 'logs';
	public $timestamps = false;

	protected $casts = [
		'date_action' => 'datetime',
		'utilisateur_id' => 'int',
		'fonctionnalite_id' => 'int'
	];

	protected $fillable = [
		'date_action',
		'utilisateur_id',
		'fonctionnalite_id',
		'description_action'
	];

	public function utilisateur()
	{
		return $this->belongsTo(Utilisateur::class);
	}

	public function fonctionnalite()
	{
		return $this->belongsTo(Fonctionnalite::class);
	}
}
