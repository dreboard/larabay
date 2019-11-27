<?php
/**
 * @since       v0.1.0
 * @package     Dev-PHP
 * @author      Andre Board <dre.board@gmail.com>
 * @version     v1.0
 * @access      public
 * @see         https://github.com/davidtsadler/ebay-sdk-examples/blob/master/shopping/02-get-single-item.php
 */

namespace App\Http\Calls;

use \DTS\eBaySDK\Shopping\Types;
use \DTS\eBaySDK\Trading\Enums;
use \Hkonnet\LaravelEbay\EbayServices;
use Illuminate\Support\Facades\Log;


/**
 * Class GetItemRequest
 * @package App\Http\Calls
 */
class GetItemRequest
{

    /**
     * @param string $itemID eBay itemID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getItemById(string $itemID)
    {
        $ebay_service = new EbayServices();
        $service = $ebay_service->createShopping();

        $request = new Types\GetSingleItemRequestType();
        $request->ItemID = $itemID;
        $request->IncludeSelector = 'ItemSpecifics,Variations,Compatibility,Details';

        $response = $service->getSingleItem($request);
        if ($response->Ack === 'Failure' || isset($response->Errors)) {
            $errors = [];
            foreach ($response->Errors as $error) {
                $errors['SeverityCode'] = $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning';
                $errors['ShortMessage'] = $error->ShortMessage ?? 'Failure';
                $errors['LongMessage'] = $error->LongMessage ?? 'Failure';
            }
            return response()->json([
                'errors' =>  $errors
            ]);
        }
        $data = json_decode($response, true);
        $item = $response->Item;
        return response()->json([
            'item' =>  $data
        ]);

        // Remove comment if you want variations and specifics
        if ($response->Ack !== 'Failure') {
            $item = $response->Item;
            print("$item->Title\n");
            printf(
                "Quantity sold %s, quantiy available %s\n",
                $item->QuantitySold,
                $item->Quantity - $item->QuantitySold
            );
            if (isset($item->ItemSpecifics)) {
                print("\nThis item has the following item specifics:\n\n");
                foreach ($item->ItemSpecifics->NameValueList as $nameValues) {
                    printf(
                        "%s: %s\n",
                        $nameValues->Name,
                        implode(', ', iterator_to_array($nameValues->Value))
                    );
                }
            }
            if (isset($item->Variations)) {
                print("\nThis item has the following variations:\n");
                foreach ($item->Variations->Variation as $variation) {
                    printf(
                        "\nSKU: %s\nStart Price: %s\n",
                        $variation->SKU,
                        $variation->StartPrice->value
                    );
                    printf(
                        "Quantity sold %s, quantiy available %s\n",
                        $variation->SellingStatus->QuantitySold,
                        $variation->Quantity - $variation->SellingStatus->QuantitySold
                    );
                    foreach ($variation->VariationSpecifics as $specific) {
                        foreach ($specific->NameValueList as $nameValues) {
                            printf(
                                "%s: %s\n",
                                $nameValues->Name,
                                implode(', ', iterator_to_array($nameValues->Value))
                            );
                        }
                    }
                }
            }
            if (isset($item->ItemCompatibilityCount)) {
                printf("\nThis item is compatible with %s vehicles:\n\n", $item->ItemCompatibilityCount);
                // Only show the first 3.
                $limit = min($item->ItemCompatibilityCount, 3);
                for ($x = 0; $x < $limit; $x++) {
                    $compatibility = $item->ItemCompatibilityList->Compatibility[$x];
                    foreach ($compatibility->NameValueList as $nameValues) {
                        printf(
                            "%s: %s\n",
                            $nameValues->Name,
                            implode(', ', iterator_to_array($nameValues->Value))
                        );
                    }
                    printf("Notes: %s \n", $compatibility->CompatibilityNotes);
                }
            }
            return response()->json([
                'items' => $response
            ]);
        }


    }
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
