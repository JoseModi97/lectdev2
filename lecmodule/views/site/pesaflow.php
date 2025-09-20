<?php

/** @var yii\web\View $this */

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <div class="body-content">

        <div class="row">
            <div class="col-lg-8 mb-3">

                <form action="https://test.pesaflow.com/PaymentAPI/iframev2.1.php" method="post" target="output_frame">

                    <!-- <input type="text" name="api_key" value="HO5SuBkDzC9c0woJ"> -->

                    <input type="text" name="payment_channel" value="card">

                    <input type="text" name="billRefNumber" value="123456">

                    <input type="text" name="currency" value="KES">

                    <input type="text" name="amount_paid" value="20.00">

                    <input type="text" name="invoice_amount" value="60.00">

                    <input type="text" name="last_payment_amount" value="20.00">

                    <input type="text" name="notificationURL" value="https://lecturersdev.uonbi.ac.ke/site/ipn">

                    <button type="submit">pay</button>
                    
                </form>

                <iframe name="output_frame" src="" id="output_frame" width="600" height="600" frameborder="0"></iframe>

            </div>
        </div>

    </div>
</div>
