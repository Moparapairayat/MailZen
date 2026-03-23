<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiDocsController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.api.docs');
    }
}
