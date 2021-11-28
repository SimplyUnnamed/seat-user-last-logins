<?php


namespace SimplyUnnamed\Seat\UserLastLogin\Http\Controllers\Corporation;


use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Web\Http\Controllers\Controller;
use SimplyUnnamed\Seat\UserLastLogin\Http\Datatables\Corporation\LastLoginsDataTable;
use SimplyUnnamed\Seat\UserLastLogin\Http\Datatables\Scopes\CorporationScope;

class LastloginsController extends Controller
{

    public function index(CorporationInfo $corporation, LastLoginsDataTable $dataTable){
        return $dataTable
            ->addScope(new CorporationScope('corporation.tracking', [$corporation->corporation_id]))
            ->render('last-login::corporation.userlist', compact('corporation'));
    }

}