<?php
namespace App\Http\Controllers\Api\V1;
use App\Models\FlightSession;
use App\Models\Strategy;
use App\Models\Simulation;
use App\Models\RealJump;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FlightController extends Controller {
    private function write(Request $r):void { abort_unless($r->user()->tokenCan('flight:write'),403); }
    private function read(Request $r):void { abort_unless($r->user()->tokenCan('flight:read'),403); }
    private function owned(string $model, string $id, int $user):object {
        return $model::query()->where('user_id',$user)->findOrFail($id);
    }
    public function session(Request $r) {
        $this->write($r);
        $data=$r->validate([
            'hill'=>['required',Rule::in(['akureyri','planica'])], 'gate'=>'required|integer|between:1,50',
            'physics_version'=>'required|string|max:128', 'wind'=>'required|array:mean,gust,seed,phase',
            'wind.mean'=>'required|numeric|between:-20,20','wind.gust'=>'required|numeric|between:0,20',
            'wind.seed'=>'required|integer','wind.phase'=>'required|numeric|between:0,1000000',
            'rho'=>'required|numeric|between:0.5,2','mu'=>'required|numeric|between:0,1',
        ]);
        $model=FlightSession::create([...$data,'id'=>(string)Str::uuid(),'user_id'=>$r->user()->id]);
        return response()->json(['id'=>$model->id],201);
    }
    public function strategy(Request $r) {
        $this->write($r);
        $data=$r->validate(['hill'=>['required',Rule::in(['akureyri','planica'])],
            'physics_version'=>'required|string|max:128',
            'controls'=>'required|array|max:50',
            'controls.toErr'=>'sometimes|numeric|between:-1,1',
            'controls.lean'=>'sometimes|array|max:32',
            'controls.lean.*'=>'numeric|between:-2,2',
        ]);
        $model=Strategy::create([...$data,'id'=>(string)Str::uuid(),'user_id'=>$r->user()->id]);
        return response()->json(['id'=>$model->id],201);
    }
    public function simulations(Request $r) {
        $this->write($r);
        $data=$r->validate([
            'session_id'=>'required|uuid', 'items'=>'required|array|min:1|max:200',
            'items.*.strategy_id'=>'required|uuid',
            'items.*.distance'=>'required|numeric|between:0,1000',
            'items.*.telemark'=>'required|boolean','items.*.fall'=>'required|boolean',
            'items.*.metrics'=>'nullable|array|max:80',
        ]);
        $user=$r->user()->id;
        $session=$this->owned(FlightSession::class,$data['session_id'],$user);
        $strategyIds=array_unique(array_column($data['items'],'strategy_id'));
        $strategies=Strategy::query()->where('user_id',$user)->whereIn('id',$strategyIds)->get()->keyBy('id');
        foreach($strategyIds as $id) {
            $s=$strategies->get($id);
            if(!$s || $s->hill!==$session->hill || $s->physics_version!==$session->physics_version) {
                throw ValidationException::withMessages(['items'=>'Each strategy must be owned and match the session hill and physics version.']);
            }
        }
        $ids=DB::transaction(function() use($data,$user) {
            $ids=[];
            foreach($data['items'] as $item) {
                $s=Simulation::create([...$item,'id'=>(string)Str::uuid(),'session_id'=>$data['session_id'],'user_id'=>$user]);
                $ids[]=$s->id;
            }
            return $ids;
        });
        return response()->json(['ids'=>$ids,'count'=>count($ids)],201);
    }
    public function jump(Request $r) {
        $this->write($r);
        $data=$r->validate([
            'session_id'=>'required|uuid','strategy_id'=>'required|uuid','simulation_id'=>'required|uuid',
            'distance'=>'required|numeric|between:0,1000','telemark'=>'required|boolean','fall'=>'required|boolean',
            'telemetry'=>'nullable|array|max:10000',
        ]);
        $user=$r->user()->id;
        $session=$this->owned(FlightSession::class,$data['session_id'],$user);
        $strategy=$this->owned(Strategy::class,$data['strategy_id'],$user);
        $simulation=$this->owned(Simulation::class,$data['simulation_id'],$user);
        if($session->id!==$simulation->session_id || $strategy->id!==$simulation->strategy_id ||
           $session->hill!==$strategy->hill || $session->physics_version!==$strategy->physics_version)
            throw ValidationException::withMessages(['simulation_id'=>'Simulation and strategy must belong to the supplied session.']);
        $jump=RealJump::create([...$data,'id'=>(string)Str::uuid(),'user_id'=>$user]);
        return response()->json(['id'=>$jump->id,'delta'=>$jump->distance-$simulation->distance],201);
    }
    public function best(Request $r) {
        $this->read($r);
        $v=$r->validate(['hill'=>['required',Rule::in(['akureyri','planica'])],'gate'=>'required|integer|between:1,50',
            'physics_version'=>'required|string|max:128','limit'=>'sometimes|integer|between:1,50']);
        $rows=Simulation::query()->join('flight_sessions','flight_sessions.id','=','simulations.session_id')
            ->join('strategies','strategies.id','=','simulations.strategy_id')
            ->where('simulations.user_id',$r->user()->id)->where('flight_sessions.hill',$v['hill'])
            ->where('flight_sessions.gate',$v['gate'])->where('flight_sessions.physics_version',$v['physics_version'])
            ->where('simulations.telemark',true)->where('simulations.fall',false)
            ->orderByDesc('simulations.distance')->limit($v['limit']??20)
            ->get(['simulations.id','simulations.strategy_id','simulations.distance','strategies.controls'])
            ->map(fn($row) => [
                'id'=>$row->id,
                'strategy_id'=>$row->strategy_id,
                'distance'=>$row->distance,
                'controls'=>json_decode($row->controls,true),
            ]);
        return response()->json(['data'=>$rows]);
    }
    public function stats(Request $r) {
        $this->read($r);
        $id=$r->user()->id;
        return response()->json(['simulations'=>Simulation::where('user_id',$id)->count(),
            'jumps'=>RealJump::where('user_id',$id)->count(),
            'best_real_distance'=>RealJump::where('user_id',$id)->where('telemark',true)->where('fall',false)->max('distance')]);
    }
}
