{def $social_pagedata = social_pagedata('sensor')}

{set-block scope=root variable=subject}[{$social_pagedata.site_title}] {'Ti diamo il benvenuto'|i18n('sensor/mail/welcome_operator')}{/set-block}
{set-block scope=root variable=body}
    <table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
        <tr>
            <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
                <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                    <tr>
                        <td align='center' valign='top'>
                            <h4>{'È stata effettuata la registrazione del tuo indirizzo email nel team di %sitename'|i18n('sensor/mail/welcome_operator',,hash('%sitename', $social_pagedata.site_title))}</h4>
                        </td>
                    </tr>
                    <tr>
                        <td align='center' bgcolor='#f90f00' valign='top'>
                            <h3>
                                {'%password_link_start%Clicca qui per generare la tua password personale%password_link_end%'|i18n('sensor/mail/welcome_operator',,
                                hash( '%password_link_start%', concat( '<a style="color: #ffffff !important" href=https://', $social_pagedata.site_url, $generate_password_link, '>' ), '%password_link_end%', '</a>' ))}
                            </h3>
                        </td>
                    </tr>
                    <tr>
                        <td align='left' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;' valign='top'>
                            <p>
                                {'Se il bottone non dovesse funzionare incolla il seguente indirizzo nella barra degli indirizzi del tuo browser: %password_link'|i18n('sensor/mail/welcome_operator',,
                                    hash( '%password_link', concat('https://', $social_pagedata.site_url, $generate_password_link) ))}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
{/set-block}