<?php
/**
 * @since       v0.1.0
 * @package     Dev-PHP
 * @author      Andre Board <dre.board@gmail.com>
 * @version     v1.0
 * @access      public
 * @see         https://github.com/dreboard
 */

namespace App\Http\Calls;


use Carbon\Carbon;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use DTS\eBaySDK\Constants\SiteIds;
use DTS\eBaySDK\Trading\Enums\ItemSortTypeCodeType;
use DTS\eBaySDK\Trading\Enums\SeverityCodeType;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use DTS\eBaySDK\Trading\Types\GetMyeBaySellingRequestType;
use DTS\eBaySDK\Trading\Types\GetOrdersRequestType;
use DTS\eBaySDK\Trading\Types\ItemListCustomizationType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use Hkonnet\LaravelEbay\EbayServices;
use SimpleXMLElement;
use Throwable;


class GetOrdersRequest
{
    protected $endpoint = 'https://api.sandbox.ebay.com/ws/api.dll';

    public function getOrdersXml()
    {
        $orders = [];
        $date = new DateTimeImmutable();
        $date->setTimezone(new DateTimeZone('GMT'));
        $time = microtime(true);
        $tMicro = sprintf("%03d",($time - floor($time)) * 1000);
        $fromTime = $date->sub(new DateInterval('P30D'))->format('Y-m-d\TH:i:s\.').$tMicro.'Z';
        $toTime = $date->add(new DateInterval('P30D'))->format('Y-m-d\TH:i:s\.').$tMicro.'Z';
        $request_body = '<?xml version="1.0" encoding="utf-8"?>
                <GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                  <CreateTimeFrom>'.$fromTime.'</CreateTimeFrom>
                  <CreateTimeTo>'.$toTime.'</CreateTimeTo>
                  <IncludeFinalValueFee>true</IncludeFinalValueFee>
                  <OrderRole>Seller</OrderRole>
                  <OrderStatus>All</OrderStatus>
                  <DetailLevel>ReturnAll</DetailLevel>
                  <RequesterCredentials>
                   <eBayAuthToken>'.config('ebay.sandbox.oauthUserToken').'</eBayAuthToken>
                  </RequesterCredentials>
                </GetOrdersRequest>';

        try{
            $ch = curl_init();
            $headers = [
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Content-length: ".strlen($request_body),
                "X-EBAY-API-APP-ID:".config('ebay.sandbox.credentials.appId'),
                "X-EBAY-API-COMPATIBILITY-LEVEL: 967",
                "X-EBAY-API-CALL-NAME: GetOrders",
                "X-EBAY-API-SITEID: 0",
                'X-EBAY-API-VERSION:837',
                'X-EBAY-API-REQUEST-ENCODING:xml'

            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$this->endpoint);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$request_body);  //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $server_output = curl_exec ($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }

            curl_close ($ch);

            $xml = new SimpleXMLElement($server_output, 0, false);
            if ($xml === false) {
                echo "Failed loading XML\n";
                foreach(libxml_get_errors() as $error) {
                    echo "\t", $error->message . "<br>";
                }
            }

            $items = $xml->children('OrderArray');
            $con = json_encode($items);
            //return json_decode($con, true);
            $i = 0;
            //dd($xml->OrderArray);
            foreach($xml->OrderArray->Order as $item)
            {
                $orders[$i] = [
                    'OrderID' => $item->OrderID,
                    'OrderStatus' => $item->OrderStatus,

                ];
                $i ++;
            }
            return $orders;
            echo $i->OrderID . ": " . $i->OrderStatus . "<br>";
        }catch (Throwable $e){
            echo $e->getMessage();
        }
    }

    /**
     * @param null $date
     * @return array
     */
    public function getOrdersSdk($date = null)
    {
        $orders = [];
        $i = 0;
        $pageNum = 1;
        try{
            $ebay_service = new EbayServices();
            $service = $ebay_service->createTrading();

            $request = new GetOrdersRequestType();
            $request->RequesterCredentials = new CustomSecurityHeaderType();
            $authToken = config('ebay.sandbox.oauthUserToken');
            $request->RequesterCredentials->eBayAuthToken = $authToken;

            $request->Pagination = new \DTS\eBaySDK\Trading\Types\PaginationType();
            $request->Pagination->EntriesPerPage = 25;

            if ($date) {
                $startOfDay = Carbon::createFromFormat('m/d/Y', $date)->startOfDay()->toDateTimeString();
            } else {
                $startOfDay = Carbon::today()->subDays(30)->startOfDay()->toDateTimeString();
            }
            $endOfDay = Carbon::today()->endOfDay()->toDateTimeString();

            $request->CreateTimeFrom = \DateTime::createFromFormat('Y-m-d H:i:s', $startOfDay);
            $request->CreateTimeTo = \DateTime::createFromFormat('Y-m-d H:i:s', $endOfDay);
            $request->OrderRole = 'Seller';// 'Buyer';

            do {
                $request->Pagination->PageNumber = $pageNum;

                /**
                 * Send the request.
                 */
                $response = $service->getOrders($request);
                //dd($response);
                /**
                 * Output the result of calling the service operation.
                 */
                if (isset($response->Errors)) {
                    foreach ($response->Errors as $error) {
                        printf(
                            "%s: %s\n%s\n\n",
                            $error->SeverityCode === \DTS\eBaySDK\Trading\Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
                            $error->ShortMessage,
                            $error->LongMessage
                        );
                    }
                }

                if ($response->Ack !== 'Failure' && isset($response)) {
                    //dd($response->OrderArray);
                    foreach ($response->OrderArray->Order as $order) {
                        $orders[$i] = [
                            'OrderID' => $order->OrderID,
                            'Item' => $order->TransactionArray->Transaction[0]->Item->ItemID,
                            'Title' => $order->TransactionArray->Transaction[0]->Item->Title,
                            'BuyerUserID' => $order->BuyerUserID,
                            'OrderStatus' => $order->OrderStatus,
                            'SellerEmail' => $order->SellerEmail,
                            'SellerEIASToken' => $order->SellerEIASToken,
                            'Buyer' => $order->TransactionArray->Transaction[0]->Buyer->Email,
                            'TransactionID' => $order->OrderStatus,
                            'eBayPaymentStatus' => $order->CheckoutStatus->eBayPaymentStatus
                        ];
                        $i++;
                    }
                }
                $pageNum += 1;
                //$response->PaginationResult->TotalNumberOfPages
            } while (isset($response) && $pageNum <= 30);
            return $orders;
        } catch(Throwable $e){
            return ['error' => $e->getMessage()];
        }

    }

    public function geMyOrders()
    {
        /*
        Credentials must be an instance of DTS\eBaySDK\Credentials\CredentialsInterface, an associative
        array that contains "appId", "certId", "devId", or a credentials provider function.
        */
        $service = new TradingService([
            'credentials' => [
                'devId' => env('EBAY_SANDBOX_DEV_ID'),
                'appId' => env('EBAY_SANDBOX_APP_ID'),
                'certId' => env('EBAY_SANDBOX_CERT_ID'),
            ],
            'siteId'      => SiteIds::US
        ]);
        /**
         * Create the request object.
         */
        $request = new GetMyeBaySellingRequestType();
        /**
         * An user token is required when using the Trading service.
         */
        $request->RequesterCredentials = new CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = config('ebay.sandbox.authToken');
        /**
         * Request that eBay returns the list of actively selling items.
         * We want 10 items per page and they should be sorted in descending order by the current price.
         */
        $request->ActiveList = new ItemListCustomizationType();
        $request->ActiveList->Include = true;
        $request->ActiveList->Pagination = new PaginationType();
        $request->ActiveList->Pagination->EntriesPerPage = 10;
        $request->ActiveList->Sort = ItemSortTypeCodeType::C_CURRENT_PRICE_DESCENDING;
        $pageNum = 1;
        do {
            $request->ActiveList->Pagination->PageNumber = $pageNum;
            /**
             * Send the request.
             */
            $response = $service->getMyeBaySelling($request);
            /**
             * Output the result of calling the service operation.
             */
            echo "==================\nResults for page $pageNum\n==================\n";
            if (isset($response->Errors)) {
                foreach ($response->Errors as $error) {
                    printf(
                        "%s: %s\n%s\n\n",
                        $error->SeverityCode === SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
                        $error->ShortMessage,
                        $error->LongMessage
                    );
                }
            }
            if ($response->Ack !== 'Failure' && isset($response->ActiveList)) {
                foreach ($response->ActiveList->ItemArray->Item as $item) {
                    printf(
                        "(%s) %s: %s %.2f\n",
                        $item->ItemID,
                        $item->Title,
                        $item->SellingStatus->CurrentPrice->currencyID,
                        $item->SellingStatus->CurrentPrice->value
                    );
                }
            }
            $pageNum += 1;
        } while (isset($response->ActiveList) && $pageNum <= $response->ActiveList->PaginationResult->TotalNumberOfPages);
    }

}
