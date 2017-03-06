{def $social_pagedata = social_pagedata('iter')}
{set-block scope=root variable=message_id}{concat('<post.',$node.contentobject_id,'.sensor','@',$social_pagedata.site_url,'>')}{/set-block}
{set-block scope=root variable=reply_to}{concat('<post.',$node.contentobject_id,'.sensor','@',$social_pagedata.site_url,'>')}{/set-block}

{set-block scope=root variable=subject}[{$social_pagedata.site_title}] {$node.contentobject_id}: {'Aggiunto osservatore'|i18n('sensor/mail/post')}{/set-block}
{set-block scope=root variable=body}
    <table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
        <tr>
            <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
                <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                    <tr>
                        <td align='center' valign='top'>
                            <h2>{'Un osservatore è stata aggiunto alla segnalazione'|i18n('sensor/mail/post')}</h2>
                        </td>
                    </tr>
                    <tr>
                        <td align='left' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;' valign='top'>
                            <p><strong>ID:</strong> <a href="http://{$social_pagedata.site_url}/sensor/posts/{$object.id}">{$node.contentobject_id}</a></p>
                            <p><strong>{$node.data_map.subject.contentclass_attribute_name}:</strong> {$node.name|wash()}</p>
                            <p><strong>{$node.data_map.type.contentclass_attribute_name}:</strong> {attribute_view_gui attribute=$node.data_map.type}</p>
                            {if $node|has_attribute('geo')}
                                <p><strong>{$node.data_map.geo.contentclass_attribute_name}:</strong> {$node.data_map.geo.content.address}</p>
                            {elseif $node|has_attribute('area')}
                                <p><strong>{$node.data_map.area.contentclass_attribute_name}:</strong> {attribute_view_gui attribute=$node.data_map.area}</p>
                            {/if}
                            <p><strong>{$node.data_map.description.contentclass_attribute_name}:</strong> <small>{attribute_view_gui attribute=$node.data_map.description}</small></p>
                            {if $node|has_attribute('attachment')}
                                <p><strong>{$node.data_map.attachment.contentclass_attribute_name}:</strong> {$node.data_map.attachment.content.original_filename|wash()}</p>
                            {/if}
                        </td>
                    </tr>
                    {if and( count( $event_details )|gt(0), is_set( $event_details.observers ) )}
                    <tr>
                        <td align='left' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;' valign='top'>
                            <p><strong>{"Osservatore"|i18n('sensor/mail/post')}:</strong> {foreach $event_details.observers as $observer_id}{fetch( content, object, hash( object_id, $observer_id )).name|wash()}{delimiter}, {/delimiter}{/foreach}</p>
                        </td>
                    </tr>
                    {/if}
                    <tr>
                        <td align='center' bgcolor='#f90f00' valign='top'>
                            <h3><a href="http://{$social_pagedata.site_url}/sensor/posts/{$object.id}" style="color: #ffffff !important">{"Vai alla segnalazione"|i18n('sensor/mail/post')}</a></h3>
                        </td>
                    </tr>
                    <tr>
                        <td align='center' valign='top'>
                            <p>
                                {'Per vedere tutte le tue segnalazioni clicca %dashboard_link_start%qui%dashboard_link_end%'|i18n('sensor/mail/post',, hash( '%dashboard_link_start%', concat( '<a href=http://', $social_pagedata.site_url, '/sensor/dashboard/>' ), '%dashboard_link_end%', '</a>' ))}<br />
                                {'Per disabilitare le notifiche email clicca %notification_link_start%qui%notification_link_end%'|i18n('sensor/mail/post',, hash( '%notification_link_start%', concat( '<a href=http://', $social_pagedata.site_url, '/notification/settings/>' ), '%notification_link_end%', '</a>' ))}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
{/set-block}