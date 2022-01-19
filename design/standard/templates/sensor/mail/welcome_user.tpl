{def $social_pagedata = social_pagedata('sensor')}

{set-block scope=root variable=subject}[{$social_pagedata.site_title}] {sensor_translate('Welcome')}{/set-block}
{set-block scope=root variable=body}
    <table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
        <tr>
            <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
                <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                    <tr>
                        <td align='center' valign='top'>
                            <h4>{sensor_translate('A issue for your %sitename account has been registered',,hash('%sitename', $social_pagedata.site_title))}</h4>
                            <p>{sensor_translate('By accessing the system, you can check the resolution of your issues and insert new ones')}</p>
                        </td>
                    </tr>
                    <tr>
                        <td align='center' bgcolor='#f90f00' valign='top'>
                            <h3>
                                {sensor_translate('%password_link_start%Click here to generate your personal password%password_link_end%',,
                                hash( '%password_link_start%', concat( '<a style="color: #ffffff !important" href=https://', $social_pagedata.site_url, $generate_password_link, '>' ), '%password_link_end%', '</a>' ))}
                            </h3>
                        </td>
                    </tr>
                    <tr>
                        <td align='left' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;line-height: 1.5' valign='top'>
                            {if $hash_key_lifetime}
                                <p>
                                    {sensor_translate("This link is valid up to %1.",,hash('%1',$hash_key_lifetime))}
                                </p>
                            {/if}
                            <p>
                                {sensor_translate('If the button does not work, paste the following address in the address bar of your browser: %password_link',,
                                hash( '%password_link', concat('https://', $social_pagedata.site_url, $generate_password_link) ))}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
{/set-block}
