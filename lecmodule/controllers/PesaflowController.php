<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 8/13/2023
 * @time: 11:37 AM
 */
declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;

final class PesaflowController extends Controller
{
    public function init()
    {
        parent::init();

        $this->enableCsrfValidation = false;
    }

    public function actionIndex(): string
    {
        $this->layout = 'pesaflow';

        return $this->render('index');
    }

    public function actionPay()
    {
        $apiClientID = '89';
        $apiKey = 'HO5SuBkDzC9c0woJ';
        $secret = 'C93NcPinZa7w9dkI5MvotjzuEr6YGzdT';
        $amount = '1.00';
        $serviceID = '48659';
        $clientMSISDN = '254714385056';
        $clientIDNumber = '33116529';
        $clientEmail = 'idachirufus@gmail.com';
        $currency = 'KES';
        $billRefNumber = 'UON_REG_APP_10023';
        $billDesc = 'REGISTRATION';
        $clientName = 'JON DOE';
        $format = 'iframe';
        $callBackURLONSuccess = 'https://d264-197-136-96-242.ngrok-free.app/pesaflow/web/pesaflow/pay';
        $notificationURL = 'https://d264-197-136-96-242.ngrok-free.app/pesaflow/web/pesaflow/log-response';
        $notificationURL = 'https://posthere.io/246d-4e76-ab5c';
        $pictureURL = 'https://example.com/client_image.jpg';
        $pictureURL = 'https://lecturersdev.uonbi.ac.ke/img/cover_uon.JPG';

        $data = $apiClientID.$amount.$serviceID.$clientIDNumber.$currency.$billRefNumber.$billDesc.$clientName.$secret;
        $secureHash = base64_encode(hash_hmac('sha256', $data, $apiKey));

        print_r($secureHash) . PHP_EOL;

        $payload = [
            "apiClientID" => $apiClientID,
            "billDesc" => $billDesc,
            "billRefNumber" => $billRefNumber,
            "currency" => $currency,
            "serviceID" => $serviceID,
            "clientMSISDN" => $clientMSISDN,
            "clientName" => $clientName,
            "clientIDNumber" => $clientIDNumber,
            "clientEmail" => $clientEmail,
            "callBackURLOnSuccess" => $callBackURLONSuccess,
            "pictureURL" => $pictureURL,
            "notificationURL" => $notificationURL,
            "amountExpected" => $amount,
            "secureHash" => $secureHash,
            "format" => $format,
            "sendSTK" => true
        ];

        $url = 'https://test.pesaflow.com/PaymentAPI/iframev2.1.php';

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response ?? null;
    }

    public function actionLogResponse()
    {
        $response = file_get_contents('php://input');

//        $response = json_decode($response, true);

        $fileName = Yii::getAlias('@app') . '/logs/pesaflow_log.txt';

        file_put_contents($fileName, $response, FILE_APPEND);
    }

    public function actionQuery()
    {
        $data_string = '89'. 'REG_APP_13';
        $hash = base64_encode(hash_hmac('sha256', $data_string, 'HO5SuBkDzC9c0woJ'));
        echo $hash;
    }

    public function actionCheckout(): string
    {
        $this->layout = 'pesaflow';

        return $this->render('checkout');
    }


    /**
     * {"status":"settled","secure_hash":"NzFmMjg5Zjg5MmQ3
ZWZjMTUzN2JkZjM4NjQxNzE4MjNhYjcyNGZhYmI4NmM3MDhhN2MxODdlOWZkZDEwNDNlNA==","pa
    yment_reference":[{"payment_reference":"RH3763J3G5","payment_date":"2023-08-0
    3T15:02:01Z","inserted_at":"2023-08-03T15:02:01","currency":"KES","amount":"1
    .00"}],"payment_date":"2023-08-03 15:02:03Z","payment_channel":"MPesa","last_
    payment_amount":"1.00","invoice_number":"VPQPAR","invoice_amount":"1.00","cur
    rency":"KES","client_invoice_ref":"KFCBK4gtsm","amount_paid":"1.00"}
     */
    /**
     *
     *
     * {
    "payment_channel" : "Mpesa",
    "client_invoice_ref" : "CGREGSHG63",
    "payment_reference" : [ {
    "payment_reference" : "MPESA1234",
    "payment_date" : "2023-08-01T16:07:40Z",
    "inserted_at" : "2023-08-01T16:07:40",
    "currency" : "KES",
    "amount" : "1.00"
    } ],
    "currency" : "KES",
    "amount_paid" : "1.00",
    "invoice_amount" : "1.00",
    "status" : "settled",
    "invoice_number" : "GWVYVX",
    "payment_date" : "2023-08-01 16:07:41Z",
    "token_hash" : "NTIzOTA5MWU0MWQwNDljYTI0M2ZlYzViOTkwZDA0NzVmYTM3ZmU3MTRiM2RiNWM5Y2M1MTQ3MDNlNTFmZDBkZQ==",
    "last_payment_amount" : "1.00"
    }
     */
}