<?php


namespace SimplyUnnamed\Seat\UserLastLogin\Http\Datatables\Corporation;


use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Web\Models\User;
use Yajra\DataTables\Services\DataTable;

class LastLoginsDataTable extends DataTable
{


    public function ajax()
    {
        return datatables()
            ->eloquent($this->applyScopes($this->query()))
            ->editColumn('token_status', function($row){

                return $row->refresh_tokens->count() === $row->all_characters()->count() ?
                    view('last-login::partials.valid-tokens') :
                    view('last-login::partials.invalid-tokens');
            })
            ->editColumn('main_character.name', function ($row) {
                return view('web::partials.character', ['character' => $row->main_character]);
            })
            ->editColumn('last_character_login_name', function ($row) {


                $character = $row->characters->sortByDesc(function ($character) {
                    return $character->online->last_login;
                })->first();


                return view('web::partials.character', ['character' => $character]);
            })
            ->editColumn('last_character_login', function($row){
                return human_diff($row->last_character_login);
            })
            ->editColumn('last_login', function ($row) {
                return human_diff($row->last_login);
            })
            ->rawColumns(['token_status'])
            ->make(true);
    }

    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->orderBy(3, 'asc')
            ->parameters([
                'drawCallback' => 'function() { ids_to_names(); $("[data-toggle=tooltip]").tooltip(); }',
            ]);
    }

    public function query()
    {
        $subQuery = RefreshToken::query()

            ->join('character_onlines', 'character_onlines.character_id', '=', 'refresh_tokens.character_id')

            ->select([
                DB::raw('max(character_onlines.last_login) last_login'),
                'refresh_tokens.user_id'
            ])

            ->groupBy('refresh_tokens.user_id');


        return User::with(['main_character', 'characters.affiliation', 'characters' => function (HasManyThrough $query) {
                    $query->whereHas('online');
                }, 'characters.online'])
            ->joinSub($subQuery, 'latest_login', function($join){
                $join->on('users.id', '=', 'latest_login.user_id');
            })
            ->addSelect([
                'users.*',
                'latest_login.last_login as last_character_login'
            ])
        ->whereHas('characters');


    }

    public function getColumns()
    {
        return [
            ['data' => 'token_status', 'title' => 'Tokens', 'sortable'=>false],
            ['data' => 'main_character.name', 'title' => trans('web::seat.main_character')],
            ['data' => 'last_character_login_name', 'title' => trans('web::seat.character_name'), 'sortable' => false],
            ['data' => 'last_character_login', 'title' => trans('web::seat.last_login').' ( EVE )'],
            ['data' => 'last_login', 'title' => trans('web::seat.last_login').' ( SeAT )']
        ];
    }


}
