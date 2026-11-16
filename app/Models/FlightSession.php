<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FlightSession extends Model {
    protected $table = 'flight_sessions';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','user_id','hill','gate','physics_version','wind','rho','mu'];
    protected function casts():array {return ['wind'=>'array'];}
}
