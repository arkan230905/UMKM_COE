@extends('layouts.app')

@section('title', 'FORCE TEST BOP')

@section('content')
<div class="alert alert-danger">
    <h2>FORCE TEST - THIS SHOULD SHOW IF VIEW IS WORKING</h2>
    <p>If you see this page, then Laravel is using the correct view file.</p>
    <p>If you still see the old BOP page with 0 values, then there's a fundamental caching issue.</p>
</div>

<div class="row mb-4">
    <div class="col-12">
        <h3>Hardcoded Test Values:</h3>
        <table class="table table-bordered">
            <tr>
                <td>Gas / BBM</td>
                <td class="text-end">Rp 67</td>
            </tr>
            <tr>
                <td>Air & Kebersihan</td>
                <td class="text-end">Rp 28</td>
            </tr>
            <tr class="fw-bold">
                <td>Total BOP / produk</td>
                <td class="text-end">Rp 95</td>
            </tr>
        </table>
    </div>
</div>

<div class="alert alert-info">
    <strong>Expected:</strong> If this view is working, you should see hardcoded values (67, 28, 95)<br>
    <strong>If you see old page with 0 values:</strong> There's a server caching issue that needs restart
</div>

@endsection
