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

    public function getSeller()
    {
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
            return json_decode($response, true);
            return response()->json([
                'items' => $response
            ]);

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
            }
            $pageNum += 1;
        } while (isset($response->ActiveList) && $pageNum <= $response->ActiveList->PaginationResult->TotalNumberOfPages);
    }

    public function getSellerList($id)
    {
        $request_body = '<?xml version="1.0" encoding="utf-8"?>
        <GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">
          <RequesterCredentials>
            <eBayAuthToken>'.env(EBAY_AUTH_LIVE).'</eBayAuthToken>
          </RequesterCredentials>
            <ErrorLanguage>en_US</ErrorLanguage>
            <WarningLevel>High</WarningLevel>
             <!--You can use DetailLevel or GranularityLevel in a request, but not both-->
          <GranularityLevel>Fine</GranularityLevel> 
             <!-- Enter a valid Time range to get the Items listed using this format
                  2013-03-21T06:38:48.420Z -->
                  <UserID>'.$id.'</UserID>
                  <DetailLevel>ItemReturnDescription</DetailLevel>
          <StartTimeFrom>2018-10-12T21:59:59.005Z</StartTimeFrom> 
          <StartTimeTo>2018-10-26T21:59:59.005Z</StartTimeTo> 
          <IncludeWatchCount>true</IncludeWatchCount> 
          <Pagination> PaginationType
            <EntriesPerPage>20</EntriesPerPage>
            <PageNumber>1</PageNumber>
          </Pagination>
        </GetSellerListRequest>
        ';
        try{
            $headers = [
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Content-length: ".strlen($request_body),
                "X-EBAY-API-APP-ID:".env(EBAY_APP_ID_LIVE),
                "X-EBAY-API-COMPATIBILITY-LEVEL: 837",
                "X-EBAY-API-CALL-NAME: GetSellerList",
                "X-EBAY-API-SITEID: 0",
                'X-EBAY-API-VERSION:837',
                'X-EBAY-API-REQUEST-ENCODING:xml'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, env(EBAY_XML_URL_LIVE));
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
            return $server_output;
        }catch (\Throwable $e){
            Log::error($e->getMessage());
            return false;
        }
        return false;
    }


}
