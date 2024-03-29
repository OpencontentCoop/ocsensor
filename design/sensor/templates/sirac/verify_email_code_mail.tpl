{set-block scope=root variable=subject}{sensor_translate('Verify your account')}{/set-block}
<table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
    <tr>
        <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr>
                    <td align='center' valign='top'>
                        <h2>{sensor_translate('Verify your account'}</h2>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#f90f00' valign='top'>
                        <h3>
                            <a href="{concat('verify_sirac_user/verify_email?VerifyCode=', $hash)|ezurl(no,full)}" style="color: #ffffff !important">
                                {sensor_translate('Click on the link to confirm your account')}
                            </a>
                        </h3>
                    </td>
                </tr>
                <tr>
                    <td align='center' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;' valign='top'>
                        <p>
                            <strong>{sensor_translate('Verification code')}:</strong>
                            {$hash|wash()}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>