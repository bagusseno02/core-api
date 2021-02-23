<?php
/**
 * Created by PhpStorm.
 * User: Dicky Eka Ramadhan
 * Date: 15/03/2019
 * Time: 10:33
 */

namespace coreapi\Utilities\Models;

use Illuminate\Database\Eloquent\Model;

class SessionSetting extends Model {

    protected $table = 'session_setting';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    public $timestamps = false;
}