@extends('web::corporation.layouts.view', ['viewname'=>'last-login', 'breadcrumb' => 'Last Login'])

@section('page_header', trans_choice('web::seat.corporation', 1).' '.trans('web::seat.last_login'))

@inject('request', 'Illuminate\Http\Request')

@section('corporation_content')

    <div class="card card-gray card-outline card-outline-tabs">
        <div class="card-header">
            <h3 class="card-title">{{ trans_choice('web::seat.corporation', 1) . ' ' . trans('web::seat.last_login') }}</h3>

        </div>
        <div class="card-body">

            {{ $dataTable->table() }}

        </div>
    </div>

@stop

@push('javascript')
    {!! $dataTable->scripts() !!}
    @endpush