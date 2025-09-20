<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 8/13/2023
 * @time: 11:56 AM
 */

/** @var yii\web\View  $this */

$this->title = 'Pesaflow integration';

$apiClientID = '89';
$apiKey = 'HO5SuBkDzC9c0woJ';
$secret = 'C93NcPinZa7w9dkI5MvotjzuEr6YGzdT';
$amount = '10.00';
$serviceID = '48659';
$clientIDNumber = '33116529';
$currency = 'KES';
$billRefNumber = 'UON_REG_APP_1001';
$billDesc = 'REGISTRATION';
$clientName = 'JON DOE';
$format = 'iframe';
$sendSTK = 'true';
$clientMSISDN = '254714385056';
$clientEmail = 'idachirufus@gmail.com';
$callBackURLONSuccess = 'https://lecturersdev.uonbi.ac.ke/pesaflow';
$notificationURL = 'https://lecturersdev.uonbi.ac.ke/pesaflow/log-response';
$pictureURL = 'https://example.com/client_image.jpg';

$data = $apiClientID.$amount.$serviceID.$clientIDNumber.$currency.$billRefNumber.$billDesc.$clientName.$secret;
$secureHash = base64_encode(hash_hmac('sha256', $data, $apiKey));
//$secureHash = 'ODk1YjhkYTI1ZjQxZjk4OThhNTM0ODY4ZTUxMWFkZDIzN2E3MmQyYjJmMGE0MmUzMGE3NThjMGE0NjRiZGQyMg==';
?>

<div class="row" style="margin-top: 100px;">
    <div class="col-8 offset-2">
        <iframe id="myFrame" src="https://test.pesaflow.com/PaymentAPI/iframev2.1.php"></iframe>
    </div>
</div>

<?php
$scriptJs = <<< JS
const callBackURLOnSuccess = '$callBackURLONSuccess';
const pictureURL = '$pictureURL';
const notificationURL = '$notificationURL';
const secureHash = '$secureHash';

const iframe = document.getElementById('myFrame');
const jsonData = {
    "apiClientID": "89",
    "billDesc": "REGISTRATION",
    "billRefNumber": "UON_REG_APP_1001",
    "currency": "KES",
    "serviceID": "48659",
    "clientMSISDN": "254714385056",
    "clientName": "JON DOE",
    "clientIDNumber": "33116529",
    "clientEmail": "idachirufus@gmail.com",
    "callBackURLOnSuccess": callBackURLOnSuccess,
    "pictureURL": pictureURL,
    "notificationURL": notificationURL,
    "amountExpected": "10.00",
    "secureHash": secureHash,
    "format": "iframe",
    "sendSTK": "true"
 };
iframe.onload = function() {
    iframe.contentWindow.postMessage(jsonData, '*'); // '*' means all origins are accepted
}
JS;
$this->registerJs($scriptJs, yii\web\View::POS_END);





