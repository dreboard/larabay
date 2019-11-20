<?php

namespace App\Http\Controllers;

use App\Http\Calls\GetSellerListRequest;
use Illuminate\Http\Request;

class CallController extends Controller
{

    /**
     * @var GetSellerListRequest
     */
    private $getSellerListRequest;

    public function __construct(GetSellerListRequest $getSellerListRequest)
    {

        $this->getSellerListRequest = $getSellerListRequest;
    }
    public function getSeller()
    {
        return $this->getSellerListRequest->getSeller();
    }
}
