<?php

namespace App\Http\Controllers;

use App\Http\Calls\GetCategoryInfo;
use App\Http\Calls\GetItemRequest;
use App\Http\Calls\GetOrdersRequest;
use App\Http\Calls\GetSellerListRequest;
use Illuminate\Http\Request;

/**
 * Class CallController
 * @package App\Http\Controllers
 */
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
     * @var GetItemRequest
     */
    private $getItemRequest;
    /**
     * @var GetCategoryInfo
     */
    private $getCategoryInfo;

    /**
     * CallController constructor.
     * @param GetSellerListRequest $getSellerListRequest
     * @param GetOrdersRequest $getOrdersRequest
     * @param GetItemRequest $getItemRequest
     * @param GetCategoryInfo $getCategoryInfo
     */
    public function __construct(
        GetSellerListRequest $getSellerListRequest,
        GetOrdersRequest $getOrdersRequest,
        GetItemRequest $getItemRequest,
        GetCategoryInfo $getCategoryInfo
    )
    {
        $this->getSellerListRequest = $getSellerListRequest;
        $this->getOrdersRequest = $getOrdersRequest;
        $this->getItemRequest = $getItemRequest;
        $this->getCategoryInfo = $getCategoryInfo;
    }

    /**
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getSellerOrders()
    {
        return $this->getSellerListRequest->getSellerOrders();
    }

    /**
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getSellerList()
    {
        return $this->getSellerListRequest->getSellerList();
    }

    /**
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getSellerListXml()
    {
        return $this->getSellerListRequest->getSellerListXml();
    }

    /**
     * @return array
     */
    public function getOrders()
    {
        return $this->getOrdersRequest->getOrdersSdk();
        //return $this->getOrdersRequest->getOrdersXml();
    }

    /**
     * @param $id
     */
    public function getItemById($id)
    {
        return $this->getItemRequest->getItemById($id);
    }
}
