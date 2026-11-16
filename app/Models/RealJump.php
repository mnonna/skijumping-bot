<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RealJump extends Model {
    public $incrementing=false; protected $keyType='string';
    protected $fillable=['id','user_id','session_id','strategy_id','simulation_id','distance','telemark','fall','telemetry'];
    protected function casts():array {return ['telemetry'=>'array','telemark'=>'boolean','fall'=>'boolean'];}
}
