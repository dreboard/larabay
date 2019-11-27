<?php

namespace App\Http\Controllers;

use App\Http\Calls\GetItemRequest;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->getItem = new GetItemRequest();
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function item($item)
    {
        $item = $this->getItem->getSingleItem($item);
        return response()->json([
            'item' => $item
        ]);
    }
}
