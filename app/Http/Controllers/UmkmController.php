<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UmkmController extends Controller
{
    public function index()
    {
        return view('master-data.umkm.index');
    }
}
