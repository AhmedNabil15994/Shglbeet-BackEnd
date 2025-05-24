<?php

namespace Modules\Transaction\Services;

use Propaganistas\LaravelPhone\PhoneNumber;

class MyFatoorahPaymentService
{
    //Test token value to be placed here: https://myfatoorah.readme.io/docs/test-token


    protected $paymentMode = 'test_mode';
    protected $test_mode = 1;
    protected $whitelabled = true;
    protected $paymentUrl = "https://apitest.myfatoorah.com/v2";
    protected $apiKey = '';

    public function __construct()
    {
        $this->apiKey  = config('setting.supported_payments.myfatourah.test_mode.api_key');
        $config_mode = config('setting.supported_payments.myfatourah.payment_mode');
        if ($config_mode == 'live_mode') {
            $this->apiKey = config('setting.supported_payments.myfatourah.live_mode.api_key');
            $this->paymentMode = 'live_mode';
            $this->test_mode = false;
            $this->paymentUrl = "https://api.myfatoorah.com/v2";
        }
    }

    public function send($order, $payment, $userToken = '', $type = 'order')
    {
        $url = $this->paymentUrls($type);
        $supplier_id = $order->vendors()->get();
        $supplier_ids = [];
        $postFields = [
            //Fill required data
            'NotificationOption' => 'Lnk', //'SMS', 'EML', or 'ALL'
            'InvoiceValue' => $order['total'],
            'CustomerName' => 'Guest',
            //Fill optional data
            'DisplayCurrencyIso' => 'KWD',
            'MobileCountryCode' => '+965',
            'CallBackUrl' => $url['success'],
            'ErrorUrl' => $url['failed'],
            'Language' => locale(),
            'CustomerReference' => $order->id,
            //'CustomerCivilId'    => 'CivilId',
            //'UserDefinedField'   => 'This could be string, number, or array',
            //'ExpiryDate'         => '', //The Invoice expires after 3 days by default. Use 'Y-m-d\TH:i:s' format in the 'Asia/Kuwait' time zone.
            //'SourceInfo'         => 'Pure PHP', //For example: (Laravel/Yii API Ver2.0 integration)
            //'CustomerAddress'    => $customerAddress,
            //'InvoiceItems'       => $invoiceItems,
        ];
        if(count($supplier_id)){
            foreach ($supplier_id as $one){
                $supplier_ids[] = [
                    'SupplierCode' => $one->supplier_id,
                    'InvoiceShare' => $order['total'],
//                    'ProposedShare' => 0,
                ];
            }
            $postFields['Suppliers'] = $supplier_ids;
        }

        if (auth()->check()) {
            $user = auth()->user();
            if ($user->name) {
                $postFields['CustomerName'] = $user->name;
            }
            if ($user->calling_code) {
                $postFields['MobileCountryCode'] = $user->country_code;
            }
            if ($user->mobile) {
                try {
                    $mobile = str_replace(' ', '', PhoneNumber::make($user->mobile, $user->country_code)->formatNational());
                    $postFields['CustomerMobile'] = $mobile;
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
            if ($user->email) {
                $postFields['CustomerEmail'] = $user->email;
            }
        }

        $curl = curl_init($this->paymentUrl . '/SendPayment');

        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postFields),
            CURLOPT_HTTPHEADER => array("Authorization: Bearer " . $this->apiKey . "", 'Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
        ));

        $response = curl_exec($curl);
        $curlErr = curl_error($curl);

        curl_close($curl);

        if ($curlErr) {
            //Curl is not working in your server
            die("Curl Error: $curlErr");
        }

        $error = $this->handleError($response);
        if ($error) {
            $response = [
                'status' => false,
                'message' => $error,
            ];
            return $response;
        }

        $response = json_decode($response);
        return ['status' => true, 'url' => $response->Data->InvoiceURL];
    }

    public function getTransactionDetails($Key, $KeyType)
    {
        $postFields = [
            'Key'     => $Key,
            'KeyType' => $KeyType
        ];
        $curl = curl_init($this->paymentUrl . '/getPaymentStatus');
        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($postFields),
            CURLOPT_HTTPHEADER     => array("Authorization: Bearer $this->apiKey", 'Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
        ));

        $response = curl_exec($curl);
        $curlErr  = curl_error($curl);

        curl_close($curl);

        if ($curlErr) {
            //Curl is not working in your server
            die("Curl Error: $curlErr");
        }

        $error = $this->handleError($response);
        if ($error) {
            die("Error: $error");
        }

        $response = json_decode($response);
        return (array) $response->Data;
    }

    public function paymentUrls($type)
    {


        // api . reservations . notify

        switch ($type) {
            case 'order':
                $url['success'] = route('frontend.myfatoorah.orders.success');
                $url['failed'] = route('frontend.myfatoorah.orders.failed');
                $url['webhooks'] = route('frontend.orders.webhooks');
                break;
            case 'api-order':
                $url['success'] = route('api.myfatoorah.orders.success');
                $url['failed'] = route('api.myfatoorah.orders.failed');
                $url['webhooks'] = route('api.orders.webhooks');

                break;
            default:
                $url = [];
                break;
        }

        return $url;
    }

    //------------------------------------------------------------------------------
    /*
     * Handle Endpoint Errors Function
     */

    private function handleError($response)
    {

        $json = json_decode($response);
        if (isset($json->IsSuccess) && $json->IsSuccess == true) {
            return null;
        }

        //Check for the errors
        if (isset($json->ValidationErrors) || isset($json->FieldsErrors)) {
            $errorsObj = isset($json->ValidationErrors) ? $json->ValidationErrors : $json->FieldsErrors;
            $blogDatas = array_column($errorsObj, 'Error', 'Name');

            $error = implode(', ', array_map(function ($k, $v) {
                return "$k: $v";
            }, array_keys($blogDatas), array_values($blogDatas)));
        } else if (isset($json->Data->ErrorMessage)) {
            $error = $json->Data->ErrorMessage;
        }

        if (empty($error)) {
            $error = (isset($json->Message)) ? $json->Message : (!empty($response) ? $response : 'API key or API URL is not correct');
        }

        return $error;
    }

    /* -------------------------------------------------------------------------- */
}
