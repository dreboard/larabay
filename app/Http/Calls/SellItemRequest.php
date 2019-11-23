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
use SimpleXMLElement;
use Throwable;

class SellItemRequest
{
    use EbayTrait;

    protected $callName = 'AddItem';
    protected $email = 'dre.board@gmail.com';
    protected $sandboxUrl = 'https://api.sandbox.ebay.com/ws/api.dll';

    public function __construct()
    {

    }
    /**
     * Single buy it now listing
     * @see https://developer.ebay.com/devzone/xml/docs/reference/ebay/additem.html#Request.Item.QuantityInfo
     */
    public function sellItemXml(array $data = [])
    {
        $request_body = '
<?xml version="1.0" encoding="utf-8"?>
<AddItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>'.config('ebay.sandbox.oauthUserToken').'</eBayAuthToken>
  </RequesterCredentials>
	<ErrorLanguage>en_US</ErrorLanguage>
	<WarningLevel>High</WarningLevel>
  <Item>
    <Title>1969-S PCGS PR-68-RD Lincoln Cent</Title>
    <Description>This is the a 1971 cent</Description>
    <PrimaryCategory>
      <CategoryID>31373</CategoryID>
    </PrimaryCategory>
    <StartPrice>1.0</StartPrice>
	<BuyItNowPrice>5.0</BuyItNowPrice>
    <CategoryMappingAllowed>true</CategoryMappingAllowed>
    <Country>US</Country>
    <Currency>USD</Currency>
    <DispatchTimeMax>3</DispatchTimeMax>
    <ListingDuration>Days_7</ListingDuration>
    <ItemSpecifics>
      <NameValueList>
        <Name>Grade</Name>
        <Value>PR68RD</Value>
      </NameValueList>	
      <NameValueList>
        <Name>Strike Type</Name>
        <Value>Proof</Value>
      </NameValueList>
	  <NameValueList>
        <Name>Certification</Name>
        <Value>PCGS</Value>
      </NameValueList>
      <NameValueList>
        <Name>Circulated/Uncirculated</Name>
        <Value>Uncirculated</Value>
      </NameValueList>
    </ItemSpecifics>
    <PaymentMethods>PayPal</PaymentMethods>
    <!--Enter your Paypal email address-->
    <PayPalEmailAddress>'.$this->email.'</PayPalEmailAddress>
    <PictureDetails>
      <PictureURL>https://d9nvuahg4xykp.cloudfront.net/8113183364110030500/-1605102138059168419_thumbnail.jpg</PictureURL>
    </PictureDetails>
    <PostalCode>44035</PostalCode>
    <Quantity>5</Quantity>
    <ReturnPolicy>
      <ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>
      <RefundOption>MoneyBack</RefundOption>
      <ReturnsWithinOption>Days_30</ReturnsWithinOption>
      <ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>
    </ReturnPolicy>
    <ShippingDetails>
      <ShippingType>Flat</ShippingType>
      <ShippingServiceOptions>
        <ShippingServicePriority>1</ShippingServicePriority>
        <ShippingService>USPSMedia</ShippingService>
        <ShippingServiceCost>2.50</ShippingServiceCost>
      </ShippingServiceOptions>
    </ShippingDetails>
    <Site>US</Site>
 </Item>
</AddItemRequest>
';
        try{
            $ch = curl_init();
            $headers = [
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Content-length: ".strlen($request_body),
                "X-EBAY-API-APP-ID:".config('ebay.sandbox.credentials.appId')."",
                "X-EBAY-API-COMPATIBILITY-LEVEL: 967",
                "X-EBAY-API-CALL-NAME: AddItem",
                "X-EBAY-API-SITEID: 0",
                'X-EBAY-API-VERSION:967',
                'X-EBAY-API-REQUEST-ENCODING:xml'

            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$this->sandboxUrl);
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
            $item = new SimpleXMLElement($server_output);
            echo $item->AddItemResponse->ItemID, '<br>';
            echo $item->AddItemResponse->StartTime, '<br>';
            echo $item->AddItemResponse->EndTime, '<br>';

        }catch (Throwable $e){
            echo $e->getMessage();
        }

    }

    /**
     * Multiple buy it now listing
     * @return \Illuminate\Http\JsonResponse|mixed
     * @see https://developer.ebay.com/devzone/xml/docs/reference/ebay/additem.html#Request.Item.QuantityInfo
     */
    public function sellMultipleItemXml(array $data = [])
    {
        $request_body = '
<?xml version="1.0" encoding="utf-8"?>
<AddItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>'.config('ebay.sandbox.oauthUserToken').'</eBayAuthToken>
  </RequesterCredentials>
	<ErrorLanguage>en_US</ErrorLanguage>
	<WarningLevel>High</WarningLevel>
  <Item>
    <Title>1969-S PCGS PR-68-RD Lincoln Cent</Title>
    <Description>This is the a 1971 cent</Description>
    <PrimaryCategory>
      <CategoryID>31373</CategoryID>
    </PrimaryCategory>
	<ListingType>FixedPriceItem</ListingType>
	<StartPrice>10.00</StartPrice>
	<!--
	https://community.ebay.com/t5/Archive-File-Exchange/quot-Error-You-have-entered-invalid-start-price-or-Buy-It-Now/td-p/1494467
    Error - You have entered invalid start price or Buy It Now price.	
	This error will occur when you have the Fixed Price format set and use the Buy It Now field. The Buy It Now field is only to be used when the Format is Auction. 
	<BuyItNowPrice currencyID="USD">10.00</BuyItNowPrice> -->
    <CategoryMappingAllowed>true</CategoryMappingAllowed>
    <Country>US</Country>
    <Currency>USD</Currency>
    <DispatchTimeMax>3</DispatchTimeMax>
    <ListingDuration>Days_7</ListingDuration>
    <ItemSpecifics>
      <NameValueList>
        <Name>Grade</Name>
        <Value>PR68RD</Value>
      </NameValueList>	
      <NameValueList>
        <Name>Strike Type</Name>
        <Value>Proof</Value>
      </NameValueList>
	  <NameValueList>
        <Name>Certification</Name>
        <Value>PCGS</Value>
      </NameValueList>
      <NameValueList>
        <Name>Circulated/Uncirculated</Name>
        <Value>Uncirculated</Value>
      </NameValueList>
    </ItemSpecifics>
    <PaymentMethods>PayPal</PaymentMethods>
    <!--Enter your Paypal email address-->
    <PayPalEmailAddress>'.$this->email.'</PayPalEmailAddress>
    <PictureDetails>
      <PictureURL>https://d9nvuahg4xykp.cloudfront.net/8113183364110030500/-1605102138059168419_thumbnail.jpg</PictureURL>
    </PictureDetails>
    <PostalCode>44035</PostalCode>
    <Quantity>5</Quantity>
    <QuantityInfo> 
      <MinimumRemnantSet>2</MinimumRemnantSet>
    </QuantityInfo>
    <ReturnPolicy>
      <ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>
      <RefundOption>MoneyBack</RefundOption>
      <ReturnsWithinOption>Days_30</ReturnsWithinOption>
      <ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>
    </ReturnPolicy>
    <ShippingDetails>
      <ShippingType>Flat</ShippingType>
      <ShippingServiceOptions>
        <ShippingServicePriority>1</ShippingServicePriority>
        <ShippingService>USPSExpressMail</ShippingService>
        <ShippingServiceCost>2.50</ShippingServiceCost>		
		<ShippingServiceAdditionalCost currencyID="USD">1.00</ShippingServiceAdditionalCost>
      </ShippingServiceOptions>
    </ShippingDetails>
    <Site>US</Site>
 </Item>
</AddItemRequest>
';
        try{
            $ch = curl_init();
            $headers = [
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Content-length: ".strlen($request_body),
                "X-EBAY-API-APP-ID:".config('ebay.sandbox.credentials.appId'),
                "X-EBAY-API-COMPATIBILITY-LEVEL: 967",
                "X-EBAY-API-CALL-NAME: AddItem",
                "X-EBAY-API-SITEID: 0",
                'X-EBAY-API-VERSION:967',
                'X-EBAY-API-REQUEST-ENCODING:xml'

            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$this->sandboxUrl);
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
            $item = new SimpleXMLElement($server_output);
            echo $item->AddItemResponse->ItemID, '<br>';
            echo $item->AddItemResponse->StartTime, '<br>';
            echo $item->AddItemResponse->EndTime, '<br>';

        }catch (Throwable $e){
            echo $e->getMessage();
        }
    }

    /**
     * Use raw call for getting sellers items
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function sellItemSingleSdk(array $data = [])
    {
        $siteId = Constants\SiteIds::US;
        /**
         * Create the service object.
         */
        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();
        /**
         * Create the request object.
         */
        $request = new Types\AddFixedPriceItemRequestType();
        /**
         * An user token is required when using the Trading service.
         */
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $authToken = config('ebay.sandbox.oauthUserToken');
        $request->RequesterCredentials->eBayAuthToken = $authToken;
        /**
         * Begin creating the fixed price item.
         */
        $item = new Types\ItemType();
        /**
         * We want a multiple quantity fixed price listing.
         */
        $item->ListingType = Enums\ListingTypeCodeType::C_FIXED_PRICE_ITEM;
        $item->Quantity = 99;
        /**
         * Let the listing be automatically renewed every 30 days until cancelled.
         */
        $item->ListingDuration = Enums\ListingDurationCodeType::C_GTC;
        /**
         * The cost of the item is $19.99.
         * Note that we don't have to specify a currency as eBay will use the site id
         * that we provided earlier to determine that it will be United States Dollars (USD).
         */
        $item->StartPrice = new Types\AmountType(['value' => 19.99]);
        /**
         * Allow buyers to submit a best offer.
         */
        $item->BestOfferDetails = new Types\BestOfferDetailsType();
        $item->BestOfferDetails->BestOfferEnabled = true;
        /**
         * Automatically accept best offers of $17.99 and decline offers lower than $15.99.
         */
        $item->ListingDetails = new Types\ListingDetailsType();
        $item->ListingDetails->BestOfferAutoAcceptPrice = new Types\AmountType(['value' => 17.99]);
        $item->ListingDetails->MinimumBestOfferPrice = new Types\AmountType(['value' => 15.99]);
        /**
         * Provide a title and description and other information such as the item's location.
         * Note that any HTML in the title or description must be converted to HTML entities.
         */
        $item->Title = 'Bits & Bobs'; // $data['Title']
        $item->Description = '<h1>Bits & Bobs</h1><p>Just some &lt;stuff&gt; I found.</p>'; // $data['Description']
        $item->SKU = 'ABC-001';
        $item->Country = 'US';
        $item->Location = 'Beverly Hills';
        $item->PostalCode = '90210';
        /**
         * This is a required field.
         */
        $item->Currency = 'USD';
        /**
         * Display a picture with the item.
         */
        $item->PictureDetails = new Types\PictureDetailsType();
        $item->PictureDetails->GalleryType = Enums\GalleryTypeCodeType::C_GALLERY;
        $item->PictureDetails->PictureURL = ['http://lorempixel.com/1500/1024/abstract']; // $data['PictureURL']
        /**
         * List item in the Books > Audiobooks (29792) category.
         */
        $item->PrimaryCategory = new Types\CategoryType();
        $item->PrimaryCategory->CategoryID = '29792'; // $data['CategoryID']
        /**
         * Tell buyers what condition the item is in.
         * For the category that we are listing in the value of 1000 is for Brand New.
         */
        $item->ConditionID = 1000;
        /**
         * Buyers can use one of two payment methods when purchasing the item.
         * Visa / Master Card
         * PayPal
         * The item will be dispatched within 1 business days once payment has cleared.
         * Note that you have to provide the PayPal account that the seller will use.
         * This is because a seller may have more than one PayPal account.
         */
        $item->PaymentMethods = [
            'VisaMC',
            'PayPal'
        ];
        $item->PayPalEmailAddress = 'example@example.com';
        $item->DispatchTimeMax = 1;
        /**
         * Setting up the shipping details.
         * We will use a Flat shipping rate for both domestic and international.
         */
        $item->ShippingDetails = new Types\ShippingDetailsType();
        $item->ShippingDetails->ShippingType = Enums\ShippingTypeCodeType::C_FLAT;
        /**
         * Create our first domestic shipping option.
         * Offer the Economy Shipping (1-10 business days) service at $2.00 for the first item.
         * Additional items will be shipped at $1.00.
         */
        $shippingService = new Types\ShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 1;
        $shippingService->ShippingService = 'Other';
        $shippingService->ShippingServiceCost = new Types\AmountType(['value' => 2.00]);
        $shippingService->ShippingServiceAdditionalCost = new Types\AmountType(['value' => 1.00]);
        $item->ShippingDetails->ShippingServiceOptions[] = $shippingService;
        /**
         * Create our second domestic shipping option.
         * Offer the USPS Parcel Select (2-9 business days) at $3.00 for the first item.
         * Additional items will be shipped at $2.00.
         */
        $shippingService = new Types\ShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 2;
        $shippingService->ShippingService = 'USPSParcel';
        $shippingService->ShippingServiceCost = new Types\AmountType(['value' => 3.00]);
        $shippingService->ShippingServiceAdditionalCost = new Types\AmountType(['value' => 2.00]);
        $item->ShippingDetails->ShippingServiceOptions[] = $shippingService;
        /**
         * Create our first international shipping option.
         * Offer the USPS First Class Mail International service at $4.00 for the first item.
         * Additional items will be shipped at $3.00.
         * The item can be shipped Worldwide with this service.
         */
        $shippingService = new Types\InternationalShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 1;
        $shippingService->ShippingService = 'USPSFirstClassMailInternational';
        $shippingService->ShippingServiceCost = new Types\AmountType(['value' => 4.00]);
        $shippingService->ShippingServiceAdditionalCost = new Types\AmountType(['value' => 3.00]);
        $shippingService->ShipToLocation = ['WorldWide'];
        $item->ShippingDetails->InternationalShippingServiceOption[] = $shippingService;
        /**
         * Create our second international shipping option.
         * Offer the USPS Priority Mail International (6-10 business days) service at $5.00 for the first item.
         * Additional items will be shipped at $4.00.
         * The item will only be shipped to the following locations with this service.
         * N. and S. America
         * Canada
         * Australia
         * Europe
         * Japan
         */
        $shippingService = new Types\InternationalShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 2;
        $shippingService->ShippingService = 'USPSPriorityMailInternational';
        $shippingService->ShippingServiceCost = new Types\AmountType(['value' => 5.00]);
        $shippingService->ShippingServiceAdditionalCost = new Types\AmountType(['value' => 4.00]);
        $shippingService->ShipToLocation = [
            'Americas',
            'CA',
            'AU',
            'Europe',
            'JP'
        ];
        $item->ShippingDetails->InternationalShippingServiceOption[] = $shippingService;
        /**
         * The return policy.
         * Returns are accepted.
         * A refund will be given as money back.
         * The buyer will have 14 days in which to contact the seller after receiving the item.
         * The buyer will pay the return shipping cost.
         */
        $item->ReturnPolicy = new Types\ReturnPolicyType();
        $item->ReturnPolicy->ReturnsAcceptedOption = 'ReturnsAccepted';
        $item->ReturnPolicy->RefundOption = 'MoneyBack';
        $item->ReturnPolicy->ReturnsWithinOption = 'Days_14';
        $item->ReturnPolicy->ShippingCostPaidByOption = 'Buyer';
        /**
         * Finish the request object.
         */
        $request->Item = $item;
        /**
         * Send the request.
         */
        $response = $service->addFixedPriceItem($request);
        /**
         * Output the result of calling the service operation.
         */
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
        if ($response->Ack !== 'Failure') {
            printf(
                "The item was listed to the eBay Sandbox with the Item number %s\n",
                $response->ItemID
            );
        }
    }

    /**
     * @param array $data
     * @see https://github.com/davidtsadler/ebay-sdk-examples/tree/master/trading
     * @see https://github.com/hkonnet/laravel-ebay
     */
    public function sellMultipleItemSdk(array $data = [])
    {

    }


}
