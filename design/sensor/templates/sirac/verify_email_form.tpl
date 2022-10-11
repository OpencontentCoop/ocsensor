<section class="hgroup">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            <div class="alert alert-info text-center">
                <i class="fa fa-envelope-o fa-5x"></i>
                <h3>{sensor_translate('Verify your account')}</h3>
                <p class="lead">{sensor_translate('Enter the code that was sent to your inbox')}</p>
                <form action="{'/verify_sirac_user/verify_email'|ezurl(no)}" method="post">
                    <div class="form-group">
                        <label for="VerifyCode">{sensor_translate('Verification code')}</label>
                        <input id="VerifyCode" name="VerifyCode" type="text" class="form-control input-lg" />
                    </div>
                    <div class="form-group clearfix">
                        <input type="submit" name="VerifyCodeAction" class="btn btn-lg btn-info pull-right" value="{sensor_translate('Verify')}" />
                    </div>
                </form>
            </div>


        </div>
    </div>
</section>