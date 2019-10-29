{def $social_pagedata = social_pagedata('sensor')}
{set-block scope=root variable=message_id}{concat('<post.', $object.id,'.sensor','@',$social_pagedata.site_url,'>')}{/set-block}
{set-block scope=root variable=reply_to}{concat('<post.', $object.id,'.sensor','@',$social_pagedata.site_url,'>')}{/set-block}

{set-block scope=root variable=subject}[{$social_pagedata.site_title}] #{$object.id}: {$subject|wash()}{/set-block}
{set-block scope=root variable=body}
    <table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
        <tr>
            <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
                <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                    <tr>
                        <td align='center' valign='top'>                            
                            {include uri='design:sensor/mail/parts/text.tpl'}
                        </td>
                    </tr>
                    <tr>
                        <td align='left' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;' valign='top'>
                            {include uri='design:sensor/mail/parts/post_info.tpl'}
                        </td>
                    </tr>
                    <tr>
                        <td align='center' bgcolor='#f90f00' valign='top'>
                            <h3><a href="http://{$social_pagedata.site_url}/sensor/posts/{$object.id}" style="color: #ffffff !important">{"Vai alla segnalazione"|i18n('sensor/mail/post')}</a></h3>
                        </td>
                    </tr>
                    <tr>
                        <td align='center' valign='top'>
                            {include uri='design:sensor/mail/parts/sensor_links.tpl'}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
{/set-block}