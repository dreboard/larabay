<?php

namespace App\Http\Controllers;

use App\Http\Calls\GetOrdersRequest;
use App\Http\Calls\GetSellerListRequest;
use Illuminate\Http\Request;

class CallController extends Controller
{

    /**
     * @var GetSellerListRequest
     */
    private $getSellerListRequest;
    /**
     * @var GetOrdersRequest
     */
    private $getOrdersRequest;

    /**
     * CallController constructor.
     * @param GetSellerListRequest $getSellerListRequest
     * @param GetOrdersRequest $getOrdersRequest
     */
    public function __construct(
        GetSellerListRequest $getSellerListRequest,
        GetOrdersRequest $getOrdersRequest
    )
    {
        $this->getSellerListRequest = $getSellerListRequest;
        $this->getOrdersRequest = $getOrdersRequest;
    }

    public function getSellerOrders()
    {
        return $this->getSellerListRequest->getSellerOrders();
    }

    public function getSellerList()
    {
        return $this->getSellerListRequest->getSellerList();
    }

    public function getSellerListXml()
    {
        return $this->getSellerListRequest->getSellerListXml();
    }

    public function getOrders()
    {
        return $this->getOrdersRequest->getOrdersXml();
    }
}
