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


use App\Http\Traits\EbayTrait;
use SimpleXMLElement;
use Throwable;

class GetCategoryInfo
{
    use EbayTrait;

    /**
     * @param null $category
     */
    public function GetCategorySpecifics($category = 31373)
    {
        $request_body = '<?xml version="1.0" encoding="utf-8"?>
<GetCategorySpecificsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>'.env('EBAY_PROD_AUTH_TOKEN').'</eBayAuthToken>
  </RequesterCredentials>
  <CategoryID>'.$category.'</CategoryID>
  <ExcludeRelationships>false</ExcludeRelationships>
  <IncludeConfidence>true</IncludeConfidence>
  <ErrorLanguage>en_US</ErrorLanguage>
  <Version>'.env('EBAY_API_VERSION').'</Version>
  <WarningLevel>High</WarningLevel>
</GetCategorySpecificsRequest>';


        try{
            $ch = curl_init();
            $headers = [
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Content-length: ".strlen($request_body),
                "X-EBAY-API-APP-ID:".env('EBAY_APP_ID_LIVE'),
                "X-EBAY-API-COMPATIBILITY-LEVEL: 967",
                "X-EBAY-API-CALL-NAME: GetCategorySpecifics",
                "X-EBAY-API-SITEID: 0",
                'X-EBAY-API-VERSION:'.env('EBAY_API_VERSION'),
                'X-EBAY-API-REQUEST-ENCODING:xml'

            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, env('EBAY_XML_URL_LIVE'));
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

            $names = $xml->children('NameRecommendation');
//var_dump($names);die;
            foreach($xml->Recommendations->NameRecommendation as $i)
            {
                echo $i->Name . ": <br>";
            }
        }catch (Throwable $e){
            echo $e->getMessage();
        }


    }

}
