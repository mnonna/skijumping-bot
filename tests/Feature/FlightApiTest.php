<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
class FlightApiTest extends TestCase {
 use RefreshDatabase;
 private function login():void {
    Sanctum::actingAs(User::query()->create(['name'=>'Operator','email'=>'operator@example.invalid','password'=>'secret']),['flight:write','flight:read']);
 }
 public function test_unauthenticated_requests_are_rejected():void {
    $this->getJson('/api/v1/stats')->assertUnauthorized();
    $this->postJson('/api/v1/sessions',[])->assertUnauthorized();
 }
 public function test_simulation_and_actual_jump_are_linked():void {
    $this->login();
    $session=$this->postJson('/api/v1/sessions',[
      'hill'=>'akureyri','gate'=>26,'physics_version'=>'physics-v1',
      'wind'=>['mean'=>1.32,'gust'=>0.33,'seed'=>371404197,'phase'=>248.38],
      'rho'=>1.174,'mu'=>0.0182
    ])->assertCreated()->json('id');
    $strategy=$this->postJson('/api/v1/strategies',[
      'hill'=>'akureyri','physics_version'=>'physics-v1',
      'controls'=>['toErr'=>-0.02,'lean'=>[-0.37,0.62]]
    ])->assertCreated()->json('id');
    $sim=$this->postJson('/api/v1/simulations/batch',[
       'session_id'=>$session,'items'=>[['strategy_id'=>$strategy,'distance'=>314,'telemark'=>true,'fall'=>false]]
    ])->assertCreated()->assertJsonPath('count',1)->json('ids.0');
    $this->postJson('/api/v1/jumps',[
       'session_id'=>$session,'strategy_id'=>$strategy,'simulation_id'=>$sim,
       'distance'=>314,'telemark'=>true,'fall'=>false,
       'telemetry'=>[['t'=>1,'alpha'=>22]]
    ])->assertCreated()->assertJsonPath('delta',0);
    $this->getJson('/api/v1/stats')->assertOk()->assertJsonPath('simulations',1)->assertJsonPath('jumps',1);
    $this->getJson('/api/v1/strategies/best?hill=akureyri&gate=26&physics_version=physics-v1')
       ->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.controls.lean.0',-0.37);
 }
 public function test_mismatched_strategy_cannot_be_attached_to_session():void {
    $this->login();
    $session=$this->postJson('/api/v1/sessions',[
      'hill'=>'akureyri','gate'=>26,'physics_version'=>'physics-v1',
      'wind'=>['mean'=>1.32,'gust'=>0.33,'seed'=>371404197,'phase'=>248.38],
      'rho'=>1.174,'mu'=>0.0182
    ])->json('id');
    $strategy=$this->postJson('/api/v1/strategies',[
      'hill'=>'planica','physics_version'=>'physics-v1','controls'=>['lean'=>[0.1]]
    ])->json('id');
    $this->postJson('/api/v1/simulations/batch',[
      'session_id'=>$session,'items'=>[['strategy_id'=>$strategy,'distance'=>300,'telemark'=>true,'fall'=>false]]
    ])->assertUnprocessable();
 }
}
