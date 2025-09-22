<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
?>

<!-- sample at https://codepen.io/YinkaEnoch/pen/PxqrZV -->

<!-- Main Content -->
<div class="container-fluid">
    <div class="row main-content bg-success text-center">
        <div class="col-md-4 text-center company__info">
            <span class="company__logo">
                <img src="<?= Yii::getAlias('@web'); ?>/img/logo.png" alt="uon logo" class="img-responsive">
            </span>
            <h4 class="company_title"></h4>
        </div>
        <div class="col-md-8 col-xs-12 col-sm-12 login_form ">
            <div class="container-fluid">
                <div class="row">
                    <div class="alert" id="alert" style="border-radius: 20px; margin-top:10px;"></div>
                    <?php
                    $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'class' => 'form-group'
                    ]);
                    ?>
                    <div class="alert" id="alert"></div>
                    <div class="row">
                        <?= $form->field($model, 'payrollNumber')
                            ->textInput([
                                'type' => 'number',
                                'placeholder' => 'Payroll Number',
                                'class' => 'form__input',
                                'autocomplete' => 'off',
                                'readonly' => true,
                                'onfocus' => "this.removeAttribute('readonly');",
                                'onblur' => "this.setAttribute('readonly','');"
                            ])
                            ->label(false);
                        ?>
                    </div>
                    <div class="row">
                        <?= $form->field($model, 'userPassword')
                            ->passwordInput([
                                'placeholder' => 'Password',
                                'class' => 'form__input',
                                'autocomplete' => 'off',
                                'readonly' => true,
                                'onfocus' => "this.removeAttribute('readonly');",
                                'onblur' => "this.setAttribute('readonly','');"
                            ])->label(false);
                        ?>
                    </div>
                    <div class="row">
                        <button class="btn login" id="btn-login" type="submit">Login</button>
                    </div>
                    <div class="row">
                        <p>Need help? <a href="mailto:graduate_support@uonbi.ac.ke">graduate-support@uonbi.ac.ke</a></p>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$ProcessLogin = Url::to(['/site/process-login']);
$loginJs = <<< JS
    $(document).ready(function(){
        let signinAction = '$ProcessLogin';
        $('#btn-login').click(function(e){
            e.preventDefault();
            $('#alert').removeClass('alert-danger');
            let loginLoader = '<h5 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h5>';
            $('#alert').html('');
            $('#alert').html(loginLoader);
            let formData = $('#login-form').serialize();
            $.ajax({
                type        :   'POST',
                url         :   signinAction,
                data        :   formData,
                dataType    :   'json',
                encode      :   true             
            })
            .done(function(data){
                $('#alert').html('');
                $('#alert').addClass('alert-danger');
                $('#alert').html('<h6>'+data.message+'</h6>');
            })
            .fail(function(data){});
        });
    });
JS;
$this->registerJs($loginJs, yii\web\View::POS_END);
