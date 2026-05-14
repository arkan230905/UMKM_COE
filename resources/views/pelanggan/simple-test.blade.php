@extends('layouts.pelanggan')
@section('content')
<div style="background: red; color: white; padding: 2rem; text-align: center;">
    <h1>SIMPLE TEST - IF YOU SEE THIS IN RED, THE LAYOUT IS WORKING</h1>
    <p>Kategoris: {{ $kategoris->count() ?? 0 }}</p>
    <p>Produks: {{ $produks->count() ?? 0 }}</p>
</div>
@endsection
