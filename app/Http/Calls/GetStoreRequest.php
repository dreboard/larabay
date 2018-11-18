<?php
/**
 * @since       v0.1.0
 * @package     Dev-PHP
 * @author      Andre Board <dre.board@gmail.com>
 * @version     v1.0
 * @access      public
 * @see         https://developer.ebay.com/devzone/xml/docs/reference/ebay/GetStore.html
 */

namespace App\Http\Calls;


class GetStoreRequest
{
    protected $callName = 'GetStore';

    public function getStore(string $id)
    {
        $request_body = '<?xml version="1.0" encoding="utf-8"?>
        <GetStoreRequest xmlns="urn:ebay:apis:eBLBaseComponents">
          <RequesterCredentials>
            <eBayAuthToken>'.env(EBAY_AUTH_LIVE).'</eBayAuthToken>
          </RequesterCredentials>
          <LevelLimit>1</LevelLimit>
          <UserID>'.$id.'</UserID>
          <ErrorLanguage>en_US</ErrorLanguage>
          <Version>'.env(EBAY_API_VERSION).'</Version>
          <WarningLevel>High</WarningLevel>
        </GetStoreRequest>
        ';
        try{
            $ch = curl_init();
            $headers = [
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Content-length: ".strlen($request_body),
                "X-EBAY-API-APP-ID:".env(EBAY_APP_ID_LIVE),
                "X-EBAY-API-COMPATIBILITY-LEVEL:" .env(EBAY_API_VERSION),
                "X-EBAY-API-CALL-NAME: GetStore",
                "X-EBAY-API-SITEID: 0",
                'X-EBAY-API-VERSION:'.env(EBAY_API_VERSION),
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
                echo 'Error:' . curl_error($ch);
            }

            curl_close ($ch);
            return $server_output;
        }catch (\Throwable $e){
            echo $e->getMessage();
        }
        return false;
    }



}