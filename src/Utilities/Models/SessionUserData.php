<?php
/**
 * Created by PhpStorm.
 * User: Dicky Eka Ramadhan
 * Date: 15/03/2019
 * Time: 10:33
 */

namespace coreapi\Utilities\Models;

use Illuminate\Database\Eloquent\Model;

class SessionUserData extends Model {

    protected $table = 'session_user_data';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    public $timestamps = false;
}