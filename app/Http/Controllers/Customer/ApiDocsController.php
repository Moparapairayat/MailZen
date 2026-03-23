<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiDocsController extends Controller
{
    public function __construct()
    {
        $this->middleware('customer.access:api.permissions.can_access_api_docs')->only(['index']);
    }

    public function index(Request $request)
    {
        return view('customer.api.docs');
    }
}
