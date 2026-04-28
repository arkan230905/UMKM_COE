@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('transaksi.retur-pembelian.partials.retur-table', [
        'returs' => $returs,
        'showCreateButton' => true,
        'showTitle' => true,
        'pageTitle' => 'Retur Pembelian',
        'tableClass' => 'table table-bordered table-hover'
    ])
</div>
@endsection
