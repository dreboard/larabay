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


use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
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

    public function getOrdersSdk()
    {

    }

}
