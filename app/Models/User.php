<?php
namespace App\Models;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable implements FilamentUser {
    use HasApiTokens, Notifiable;
    protected $fillable = ['name','email','password'];
    protected $hidden = ['password','remember_token'];
    public function canAccessPanel(Panel $panel): bool { return true; } // Private single-user instance; restrict by authorization policy for multi-user deployments.
    protected function casts(): array { return ['password'=>'hashed','email_verified_at'=>'datetime']; }
}
