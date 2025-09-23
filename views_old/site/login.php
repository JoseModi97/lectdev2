<div class="uon-body">
    <div class="uon-floating-elements">
        <div class="uon-floating-circle"></div>
        <div class="uon-floating-circle"></div>
        <div class="uon-floating-circle"></div>
    </div>

    <div class="uon-login-container">
        <div class="uon-brand-section">
            <div class="uon-logo-container ">
                <div class="uon-logo">
                    <img class="uon-logo-img" src="<?= Yii::getAlias('@web'); ?>/img/logo.png" alt="UoN">
                </div>
            </div>
            <h1 class="uon-brand-title">University of Nairobi</h1>
            <p class="uon-brand-subtitle">Lecturer Portal System<br>Excellence in Education & Research</p>
        </div>

        <div class="uon-form-section">
            <div class="uon-form-header">
                <h2 class="uon-form-title">Welcome Back</h2>
                <p class="uon-form-subtitle">Sign in to access your lecturer dashboard</p>
                <div style="margin-top: 0px; margin-bottom: 0px; " class="uon-alert uon-alert-info" id="uon-alert">
                    <div style="font-size: 13px;" id="uon-alert-content"></div>
                </div>
            </div>

            <!-- <div style="margin-top: 0px; " class="uon-alert uon-alert-info" id="uon-alert">
                <div style="font-size: 13px;" id="uon-alert-content"></div>
            </div> -->

            <form id="uon-login-form" class="uon-form-group">
                <div class="uon-input-container">
                    <input
                        type="number"
                        name="payrollNumber"
                        id="uon-payroll"
                        class="uon-form-input"
                        placeholder="Enter your payroll number"
                        autocomplete="off"
                        readonly
                        onfocus="this.removeAttribute('readonly');"
                        onblur="this.setAttribute('readonly','');"
                        required />
                    <i class="fas fa-id-badge uon-input-icon"></i>
                </div>

                <div class="uon-input-container">
                    <input
                        type="password"
                        name="userPassword"
                        id="uon-password"
                        class="uon-form-input"
                        placeholder="Enter your password"
                        autocomplete="off"
                        readonly
                        onfocus="this.removeAttribute('readonly');"
                        onblur="this.setAttribute('readonly','');"
                        required />
                    <i class="fas fa-lock uon-input-icon"></i>
                </div>

                <button type="submit" id="uon-btn-login" class="uon-login-btn">
                    <span id="uon-btn-text">Sign In to Portal</span>
                </button>
            </form>

            <div class="uon-support-text">
                Need assistance? Contact
                <a href="mailto: lec-support@uonbi.ac.ke" class="uon-support-link">
                    lec-support@uonbi.ac.ke
                </a>
            </div>
        </div>
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        let signinAction = '/site/process-login';

        $('#uon-btn-login').click(function(e) {
            e.preventDefault();

            const alert = $('#uon-alert');
            const alertContent = $('#uon-alert-content');
            const btnText = $('#uon-btn-text');
            const btn = $(this);

            alert.hide().removeClass('uon-alert-danger uon-alert-info');

            btn.addClass('uon-btn-loading');
            alert.addClass('uon-alert-info').show();
            alertContent.html('<i class="fas fa-spinner fa-pulse uon-loader"></i>Authenticating your credentials...');
            btnText.html('<i class="fas fa-spinner fa-pulse uon-loader"></i>Signing In...');

            let formData = $('#uon-login-form').serialize();

            $.ajax({
                type: 'POST',
                url: signinAction,
                data: formData,
                dataType: 'json',
                success: function(data) {
                    btn.removeClass('uon-btn-loading');
                    btnText.html('Sign In to Portal');

                    if (data.success === true) {
                        // Login successful
                        alert.removeClass('uon-alert-danger').addClass('uon-alert-info').show();
                        alertContent.html('<i class="fas fa-check-circle"></i> ' + data.message);

                        // Redirect after brief delay
                        setTimeout(function() {
                            window.location.href = data.redirect || '/';
                        }, 500);
                    } else {
                        // Login failed
                        alert.removeClass('uon-alert-info').addClass('uon-alert-danger').show();
                        alertContent.html('<i class="fas fa-exclamation-triangle"></i> ' + data.message);
                    }

                },
                error: function(xhr, status, error) {
                    // Reset button state

                    btn.removeClass('uon-btn-loading');
                    btnText.html('Sign In to Portal');

                    // Handle actual connection errors
                    //alert.removeClass('uon-alert-info').addClass('uon-alert-danger').show();

                    if (xhr.status === 404) {
                        alert.removeClass('uon-alert-info').addClass('uon-alert-danger').show();
                        alertContent.html('<i class="fas fa-exclamation-triangle"></i>  An error occurred. Please try again.');
                    } else
                    if (xhr.status === 500) {
                        alertContent.html('<i class="fas fa-exclamation-triangle"></i> Server error. Please try again later.');
                    }
                    //  else {
                    //     alertContent.html('<i class="fas fa-exclamation-triangle"></i> An error occurred. Please try again.');
                    // }
                }
            });
        });

        // Enhanced form validation
        $('.uon-form-input').on('input', function() {
            const input = $(this);
            const value = input.val().trim();

            if (value) {
                input.css('border-color', '#10b981');
            } else {
                input.css('border-color', '#e2e8f0');
            }
        });

        // Enter key submission
        $('.uon-form-input').keypress(function(e) {
            if (e.which === 13) {
                $('#uon-btn-login').click();
            }
        });
    });
</script>