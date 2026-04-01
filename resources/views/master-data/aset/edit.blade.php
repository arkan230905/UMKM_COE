@extends('layouts.app')

@section('content')
<div class="container">
    <h1>TEST - Edit Aset Simple</h1>
    
    <div class="alert alert-success">
        <strong>View berhasil dimuat!</strong> Aset ID: {{ $aset->id ?? 'tidak ada' }}
    </div>
    
    <p>Nama Aset: {{ $aset->nama_aset ?? 'tidak ada' }}</p>
    
    <form action="{{ route('master-data.aset.update', $aset->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label>Nama Aset</label>
            <input type="text" name="nama_aset" value="{{ $aset->nama_aset }}" class="form-control">
        </div>
        
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection