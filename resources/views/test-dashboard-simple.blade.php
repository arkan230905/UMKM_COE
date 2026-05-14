@extends('layouts.pelanggan')

@section('content')
<div style="padding: 2rem;">
    <h1>Dashboard Test</h1>
    <p>Kategoris: {{ $kategoris->count() }}</p>
    <p>Produks: {{ $produks->count() }}</p>
</div>
@endsection
