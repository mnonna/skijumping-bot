<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Strategy extends Model {
    public $incrementing=false; protected $keyType='string';
    protected $fillable=['id','user_id','hill','physics_version','controls'];
    protected function casts():array {return ['controls'=>'array'];}
}
