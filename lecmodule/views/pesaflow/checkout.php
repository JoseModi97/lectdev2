<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 8/13/2023
 * @time: 11:56 AM
 */

/** @var yii\web\View  $this */

$this->title = 'Pesaflow integration';

$apiClientID = '89';
// $apiClientID = '33';
$apiKey = 'HO5SuBkDzC9c0woJ';
// $apiKey = '4EppeQn20VECXaBc';
$secret = 'C93NcPinZa7w9dkI5MvotjzuEr6YGzdT';
// $secret = 'OZ1TSvM7cqtYTFKOjsKTW2aENOhxD3Cu';
$amount = '1.00';
$serviceID = '48659';
// $serviceID = '2798169';
$clientIDNumber = '33116529';
$currency = 'KES';
$billRefNumber = 'REG_APP_13';
$billDesc = 'REGISTRATION';
$clientName = 'JON DOE';
$format = 'iframe';
$sendSTK = 'true';
$clientMSISDN = '254714385056';
$clientEmail = 'idachirufus@gmail.com';
$callBackURLONSuccess = 'https://uonbi.ac.ke';
$notificationURL = 'https://posthere.io/246d-4e76-ab5c';
$pictureURL = 'https://example.com/client_image.jpg';
$actionUrl = 'https://test.pesaflow.com/PaymentAPI/iframev2.1.php';
// $actionUrl = 'https://payments.ecitizen.go.ke/PaymentAPI/iframev2.1.php';

$data = $apiClientID.$amount.$serviceID.$clientIDNumber.$currency.$billRefNumber.$billDesc.$clientName.$secret;
$secureHash = base64_encode(hash_hmac('sha256', $data, $apiKey));
?>

<div class="row">
    <form id="form-make-payment" action="<?=$actionUrl?>" method="post" target="pesaflow-checkout-iframe">
        <input type="hidden" class="form-control" placeholder="apiClientID" name="apiClientID" value="<?=$apiClientID?>">
        <input type="hidden" class="form-control" placeholder="billDesc" name="billDesc" value="<?=$billDesc?>">
        <input type="hidden" class="form-control" placeholder="billRefNumber" name="billRefNumber" value="<?=$billRefNumber?>">
        <input type="hidden" class="form-control" placeholder="currency" name="currency" value="<?=$currency?>">
        <input type="hidden" class="form-control" placeholder="serviceID" name="serviceID" value="<?=$serviceID?>">
        <input type="hidden" class="form-control" placeholder="clientMSISDN" name="clientMSISDN" value="<?=$clientMSISDN?>">
        <input type="hidden" class="form-control" placeholder="clientName" name="clientName" value="<?=$clientName?>">
        <input type="hidden" class="form-control" placeholder="clientIDNumber" name="clientIDNumber" value="<?=$clientIDNumber?>">
        <input type="hidden" class="form-control" placeholder="clientEmail" name="clientEmail" value="<?=$clientEmail?>">
        <input type="hidden" class="form-control" placeholder="callBackURLOnSuccess" name="callBackURLOnSuccess" value="<?=$callBackURLONSuccess?>">
        <input type="hidden" class="form-control" placeholder="pictureURL" name="pictureURL" value="<?=$pictureURL?>">
        <input type="hidden" class="form-control" placeholder="notificationURL" name="notificationURL" value="<?=$notificationURL?>">
        <input type="hidden" class="form-control" placeholder="amountExpected" name="amountExpected" value="<?=$amount?>">
        <input type="hidden" class="form-control" placeholder="secureHash" name="secureHash" value="<?=$secureHash?>">
        <input type="hidden" class="form-control" placeholder="format" name="format" value="iframe">
        <input type="hidden" class="form-control" placeholder="sendSTK" name="sendSTK" value="true">
        <button id="btn-render-checkout-page" class="btn btn-success">Make payment</button>
    </form>
</div>

<div class="row">
    <iframe id="pesaflow-checkout-iframe" name="pesaflow-checkout-iframe" width="720px" height="720px"></iframe>
</div>

<?php
$scriptJs = <<< JS
$('#btn-render-checkout-page').click(function (e){
    e.preventDefault();
    // $('#form-make-payment').submit();  
    void submitForm();
});

async function submitForm(){
    await $('#form-make-payment').submit();
    $('#btn-render-checkout-page').text('Reload payment page');
}
JS;
$this->registerJs($scriptJs, yii\web\View::POS_READY);





