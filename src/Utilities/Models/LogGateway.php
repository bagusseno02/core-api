<?php
/**
 * Created by PhpStorm.
 * User: alysangadji
 * Date: 10/06/19
 * Time: 15.38
 */
namespace coreapi\Utilities\Models;

use Illuminate\Database\Eloquent\Model;

class LogGateway extends Model {

    protected $connection = 'mysql_log';

    protected $table = 'log_api_gateway';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    public $timestamps = false;
}