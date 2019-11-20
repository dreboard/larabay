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


use Illuminate\Support\Facades\Log;

class GetItem
{


    /**
     * @param int $item
     * http://open.api.ebay.com/shopping?
        callname=GetSingleItem&
        responseencoding=XML&
        appid=YourAppIDHere&
        siteid=0&
        version=967&
        ItemID=180126682091&
        IncludeSelector=Description,ItemSpecifics
     */
    public function getSingleItem(int $item)
    {
        $request_body = '<?xml version="1.0" encoding="utf-8"?>
            <GetSingleItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
              <!-- Call-specific Input Fields -->
              <IncludeSelector> Description,ItemSpecifics </IncludeSelector>
              <ItemID> '.$item.' </ItemID>
              <VariationSKU> string </VariationSKU>
              <VariationSpecifics> NameValueListArrayType
                <NameValueList> NameValueListType
                  <Name> string </Name>
                  <Value> string </Value>
                  <!-- ... more Value values allowed here ... -->
                </NameValueList>
                <!-- ... more NameValueList nodes allowed here ... -->
              </VariationSpecifics>
              <!-- Standard Input Fields -->
              <MessageID> string </MessageID>
            </GetSingleItemRequest>
        ';
        $url = "http://open.api.ebay.com/shopping?
        callname=GetSingleItem&
        responseencoding=XML&
        appid=env".env('EBAY_APP_ID_LIVE')."&
        siteid=0&
        version=967&
        ItemID=180126682091&
        IncludeSelector=Description,ItemSpecifics";

        try{
            $headers = [
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Content-length: ".strlen($url),
                "X-EBAY-API-APP-ID:".env('EBAY_APP_ID_LIVE'),
                "X-EBAY-API-COMPATIBILITY-LEVEL:" .env('EBAY_API_VERSION'),
                "X-EBAY-API-CALL-NAME: GetSingleItem",
                "X-EBAY-API-SITEID: 0",
                'X-EBAY-API-VERSION:'.env('EBAY_API_VERSION'),
                'X-EBAY-API-REQUEST-ENCODING:xml'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
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
