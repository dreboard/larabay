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


use App\Http\Traits\EbayTrait;
use Illuminate\Support\Facades\Log;

class GetSellerListRequest
{
    use EbayTrait;

    protected $callName = 'GetSellerList';

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