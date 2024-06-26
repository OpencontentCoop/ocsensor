{def $social_pagedata = social_pagedata()}
{set-block scope=root variable=subject}{'Welcome to %1'|i18n('social_user/mail/registration',,array($social_pagedata.site_title))}{/set-block}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{$title}</title>
    <style type="text/css">
        {literal}
        @import url('http://fonts.googleapis.com/css?family=Open+Sans');
        body{font-family: 'Open Sans', 'Arial', 'Helvetica', sans-serif;}
        #outlook a {padding:0;}
        body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}
        .ExternalClass {width:100%;}
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
        #backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}
        img {outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;}
        a img {border:none;}
        .image_fix {display:block;}
        p {margin: 1em 0;}
        h1, h2, h3, h4, h5, h6 {color: black !important;}
        h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: blue !important;}
        h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {
            color: red !important; /* Preferably not the same color as the normal header link color.  There is limited support for psuedo classes in email clients, this was added just for good measure. */
        }
        h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {
            color: purple !important; /* Preferably not the same color as the normal header link color. There is limited support for psuedo classes in email clients, this was added just for good measure. */
        }
        table td {border-collapse: collapse;}
        table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }
        a {color: red;}
        @media only screen and (max-device-width: 480px) {
            a[href^="tel"], a[href^="sms"] {
                text-decoration: none;
                color: black; /* or whatever your want */
                pointer-events: none;
                cursor: default;
            }
            .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                text-decoration: default;
                color: red !important; /* or whatever your want */
                pointer-events: auto;
                cursor: default;
            }
        }
        @media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
            a[href^="tel"], a[href^="sms"] {
                text-decoration: none;
                color: blue; /* or whatever your want */
                pointer-events: none;
                cursor: default;
            }
            .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                text-decoration: default;
                color: red !important;
                pointer-events: auto;
                cursor: default;
            }
        }
        @media only screen and (-webkit-min-device-pixel-ratio: 2) {
            /* Put your iPhone 4g styles in here */
        }
        @media only screen and (-webkit-device-pixel-ratio:.75){
            /* Put CSS for low density (ldpi) Android layouts in here */
        }
        @media only screen and (-webkit-device-pixel-ratio:1){
            /* Put CSS for medium density (mdpi) Android layouts in here */
        }
        @media only screen and (-webkit-device-pixel-ratio:1.5){
            /* Put CSS for high density (hdpi) Android layouts in here */
        }
        {/literal}
    </style>

    <!--[if IEMobile 7]>
    <style type="text/css">

    </style>
    <![endif]-->

    <!--[if gte mso 9]>
    <style>
        /* Target Outlook 2007 and 2010 */
    </style>
    <![endif]-->
</head>
<body>
<table align='center' bgcolor='#f4f7f9' border='0' cellpadding='0' cellspacing='0' id='backgroundTable' style='background: #f4f7f9;' width='100%'>
    <tr>
        <td align='center'>
            <center>
                <table border='0' cellpadding='20' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
                    <tr>
                        <td align='center' valign='top'>
                            <img src="{$social_pagedata.asset_url}{$social_pagedata.logo_path|ezroot(no)}" alt="{$social_pagedata.site_title}" height="90" width="90" style="outline:none; text-decoration:none;border:none,display:block;">
                            <p>{$social_pagedata.logo_title} - {$social_pagedata.logo_subtitle}</p>
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
    <tr>
        <td align='center'>
            <center>
                <table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
                    <tr>
                        <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
                            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                                <tr>
                                    <td align='center' valign='top'>
                                        <h2>{'Thank you want to participate!'|i18n('social_user/mail/registration')}</h2>
                                    </td>
                                </tr>
                                <tr>
                                    <td align='center' valign='top'>
                                        <h4 style='color: #f90f00 !important'>{'Please find here your profile information'|i18n('social_user/mail/registration')}</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td align='center' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;' valign='top'>
                                        <p>
                                            <strong>{'Name'|i18n('social_user/mail/registration')}:</strong>
                                            {$user.contentobject.name|wash()}
                                        </p>
                                        <p>
                                            <strong>{'Email address'|i18n('social_user/mail/registration')}:</strong>
                                            {$user.email|wash()}
                                        </p>
                                    </td>
                                </tr>
                                {if is_set( $hash )}
                                    <tr>
                                        <td align='center' bgcolor='#f90f00' valign='top'>
                                            <h3>
                                                <a href="{concat('social_user/activate/', $hash, '/', $user.contentobject.main_node_id)|ezurl(no,full)}" style="color: #ffffff !important">
                                                    {'Click on the link to confirm your account'|i18n('social_user/mail/registration')}
                                                </a>
                                            </h3>
                                        </td>
                                    </tr>
                                {/if}
                                <tr>
                                    <td align='center' valign='top'>
                                        <p>
                                            {'If you want to change your profile settings Click %profile_link_start%here% profile_link_end%'|i18n('social_user/mail/registration',, hash( '%profile_link_start%', concat( '<a href="', '/user/edit'|ezurl(no,full), '"/>' ), '%profile_link_end%', '</a>' ))}<br />
                                            {'To enable or disable email notifications Click %notification_link_start% here% notification_link_end%'|i18n('social_user/mail/registration',, hash( '%notification_link_start%', concat( '<a href="', '/notification/settings'|ezurl(no,full), '"/>' ), '%notification_link_end%', '</a>' ))}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
</table>
</body>
</html>
