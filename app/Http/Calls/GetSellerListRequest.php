<?php
/**
 * @since       v0.1.0
 * @package     Dev-PHP
 * @author      Andre Board <dre.board@gmail.com>
 * @version     v1.0
 * @access      public
 * @see         https://developer.ebay.com/devzone/xml/docs/reference/ebay/GetSellerList.html
 */

namespace App\Http\Calls;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;
use Hkonnet\LaravelEbay\Ebay;
use \Hkonnet\LaravelEbay\EbayServices;
use App\Http\Traits\EbayTrait;

use Illuminate\Support\Facades\Log;

class GetSellerListRequest
{
    use EbayTrait;

    protected $callName = 'GetSellerList';

    protected $sandboxUrl = 'https://api.sandbox.ebay.com/ws/api.dll';

    public function __construct()
    {
        
    }
    /**
     * Get My Selling getSellerOrders (GetOrdersRequestType)
     * @return \Illuminate\Http\JsonResponse|mixed
     * @see https://developer.ebay.com/devzone/xml/docs/reference/ebay/getmyebayselling.html
     * @see https://developer.ebay.com/DevZone/XML/docs/Reference/ebay/GetOrders.html#Request.CreateTimeFrom
     * @see https://stackoverflow.com/questions/30254723/how-to-fetch-my-listings-of-product-from-ebay
     * {@internal TESTED}}
     */
    public function getSellerOrders(int $days = null)
    {
        $date = new DateTimeImmutable();
        $date->setTimezone(new DateTimeZone('GMT'));
        $time = microtime(true);
        $tMicro = sprintf("%03d",($time - floor($time)) * 1000);

        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();
        $authToken = config('ebay.sandbox.oauthUserToken');
        $items = [];
        $request = new Types\GetOrdersRequestType();

        $format = 'Y-m-d\TH:i:s\.000';
        $date = DateTime::createFromFormat($format, '2019-11-01');

        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = config('ebay.sandbox.oauthUserToken');
        $request->DetailLevel[] = 'ItemReturnDescription';
        //$request->CreateTimeFrom = new DateTime('2019-11-10');
        //$request->CreateTimeTo = new DateTime('2019-11-30');
        $request->Pagination = new Types\PaginationType();
        $request->Pagination->EntriesPerPage = 25;
        $request->NumberOfDays = 20;
        $request->Pagination->PageNumber = 1;
        $request->OrderRole = 'Seller';
        $pageNum = 1;
        $request->Pagination->PageNumber = $pageNum;

        $response = json_decode($service->getOrders($request), true);
        if (isset($response->Errors)) {
            foreach ($response->Errors as $error) {
                printf("%s: %s\n%s\n\n",
                    $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
                    $error->ShortMessage,
                    $error->LongMessage
                );
            }
        }
        if ($response['Ack'] !== 'Failure') {
            /*foreach ($response->OrderArray->Order->data as $item) {
                $items[] = $item;
            }*/
            foreach ($response['OrderArray']['Order'] as $k => $item) {
                $items[] = $item;
            }
        }

        return response()->json([
            'items' => $items
        ]);

    }

    /**
     * Use SDK call for getting sellers items (GetMyeBaySellingRequestType)
     * @return \Illuminate\Http\JsonResponse|mixed
     * @see https://developer.ebay.com/devzone/xml/docs/reference/ebay/getmyebayselling.html
     * {@internal TESTED }}
     */
    public function getSellerList()
    {
        $items = [];
        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();

        /**
         * Create the request object.
         */
        $request = new Types\GetMyeBaySellingRequestType();

        /**
         * An user token is required when using the Trading service.
         */
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        //$authToken = Ebay::getAuthToken();
        $authToken = config('ebay.sandbox.oauthUserToken');
        $request->RequesterCredentials->eBayAuthToken = $authToken;

        /**
         * Request that eBay returns the list of actively selling items.
         * We want 10 items per page and they should be sorted in descending order by the current price.
         */
        $request->ActiveList = new Types\ItemListCustomizationType();
        $request->ActiveList->Include = true;
        $request->ActiveList->Pagination = new Types\PaginationType();
        $request->ActiveList->Pagination->EntriesPerPage = 10;
        $request->ActiveList->Sort = Enums\ItemSortTypeCodeType::C_CURRENT_PRICE_DESCENDING;
        $pageNum = 1;

        do {
            $request->ActiveList->Pagination->PageNumber = $pageNum;
            $response = $service->getMyeBaySelling($request);
            //$response = json_decode($response, true);
//dd($response);

            /**
             * Output the result of calling the service operation.
             */
            echo "==================\nResults for page $pageNum\n==================\n";
            if (isset($response->Errors)) {
                foreach ($response->Errors as $error) {
                    printf(
                        "%s: %s\n%s\n\n",
                        $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
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
/*              ==================
                Results for page 1 json
                ==================
                (110478053549) 1969-S PCGS PR-68-RD Lincoln Cent: USD 10.00
                (110477255755) 2010 Lincoln Cent: USD 1.00
                (110477284652) 1972 Proof Lincoln Cent: USD 1.00
                (110477284658) 1971 Proof Lincoln Cent: USD 1.00
                (110477285526) 1971 Proof Lincoln Cent: USD 1.00
                (110478051066) 1969 Proof Lincoln Cent: USD 1.00
                {"items":{}}*/
            }
            $pageNum += 1;
        } while (isset($response->ActiveList) && $pageNum <= $response->ActiveList->PaginationResult->TotalNumberOfPages);
        return response()->json([
            'items' => $response
        ]);
    }

    /**
     * Use raw call for getting sellers items
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * {@internal TESTED}}
     */
    public function getSellerListXml()
    {
        $request_body = '<?xml version="1.0" encoding="utf-8"?>
        <GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">
          <RequesterCredentials>
            <eBayAuthToken>'.config('ebay.sandbox.oauthUserToken').'</eBayAuthToken>
          </RequesterCredentials>
            <ErrorLanguage>en_US</ErrorLanguage>
            <WarningLevel>High</WarningLevel>
             <!--You can use DetailLevel or GranularityLevel in a request, but not both-->
          <GranularityLevel>Fine</GranularityLevel> 
             <!-- Enter a valid Time range to get the Items listed using this format
                  2013-03-21T06:38:48.420Z -->
             
                  <DetailLevel>ItemReturnDescription</DetailLevel>
          <StartTimeFrom>2019-11-12T21:59:59.005Z</StartTimeFrom> 
          <StartTimeTo>2019-11-26T21:59:59.005Z</StartTimeTo> 
          <IncludeWatchCount>true</IncludeWatchCount> 
          <Pagination> PaginationType
            <EntriesPerPage>20</EntriesPerPage>
            <PageNumber>1</PageNumber>
          </Pagination>
            <OutputSelector>Seller</OutputSelector>
          <OutputSelector>ItemArray.Item.ItemID</OutputSelector>
          <OutputSelector>ItemArray.Item.Quantity</OutputSelector>
          <OutputSelector>ItemArray.Item.Title</OutputSelector>
          <OutputSelector>ItemArray.Item.SellingStatus.CurrentPrice</OutputSelector>
          <OutputSelector>ItemArray.Item.TimeLeft</OutputSelector>
          <OutputSelector>ItemArray.Item.PrimaryCategory.CategoryID</OutputSelector>
          <OutputSelector>ItemArray.Item.PrimaryCategory.CategoryName</OutputSelector>
        </GetSellerListRequest>
        ';
        try{
            $headers = [
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Content-length: ".strlen($request_body),
                "X-EBAY-API-APP-ID:".config('ebay.sandbox.credentials.appId'),
                "X-EBAY-API-COMPATIBILITY-LEVEL: 837",
                "X-EBAY-API-CALL-NAME: GetSellerList",
                "X-EBAY-API-SITEID: 0",
                'X-EBAY-API-VERSION:967',
                'X-EBAY-API-REQUEST-ENCODING:xml'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->sandboxUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$request_body);  //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $server_output = curl_exec ($ch);

            if (curl_errno($ch)) {
                Log::error(curl_error($ch));
                return false;
            }
            curl_close ($ch);

            return response($server_output, 200)
                ->header('Content-Type', 'text/xml');

        }catch (\Throwable $e){
            Log::error($e->getMessage());
            return response([], 404)
                ->header('Content-Type', 'text/xml');
        }

    }


}
