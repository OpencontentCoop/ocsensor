{def $social_pagedata = social_pagedata('sensor')}
{set-block scope=root variable=message_id}{sensor_message_id($object.id)}{/set-block}
{set-block scope=root variable=reply_to}{sensor_message_id($object.id)}{/set-block}

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
                {*if and( sensor_settings('HideOperatorNames')|not(), sensor_settings('HideTimelineDetails')|not(), count( $event_details )|gt(0), is_set( $event_details.owner_groups ) )}
                    <tr>
                        <td align='left' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;' valign='top'>
                            <p><strong>{sensor_translate('In charge of')}:</strong> {foreach $event_details.owner_groups as $owner_id}{fetch( content, object, hash( object_id, $owner_id )).name|wash()}{delimiter}, {/delimiter}{/foreach}</p>
                        </td>
                    </tr>
                {/if*}
                <tr>
                    <td align='center' bgcolor='#f90f00' valign='top'>
                        <h3><a href="https://{$social_pagedata.site_url}/sensor/posts/{$object.id}" style="color: #ffffff !important">{sensor_translate('Check the resolution trend')}</a></h3>
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
