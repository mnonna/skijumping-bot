<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Simulation extends Model {
    public $incrementing=false; protected $keyType='string';
    protected $fillable=['id','user_id','session_id','strategy_id','distance','telemark','fall','metrics'];
    protected function casts():array {return ['metrics'=>'array','telemark'=>'boolean','fall'=>'boolean'];}
}
