<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserAddressesController extends Controller
{
    use ApiResponse;
    protected $user;

    public function __construct()
    {
        auth()->shouldUse('api');
        $this->user = auth()->user();
    }

    public function index()
    {
        $addresses = $this->user->addresses;
        return $this->success($addresses);
    }
}
