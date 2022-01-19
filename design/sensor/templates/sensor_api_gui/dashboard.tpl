{if $current_user_has_notifications|not()}
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle"></i>
        {sensor_translate("Warning: you have no active notification and you will not receive any email. You can enable notifications on the page dedicated to %open_notification_url%notificationsettings%close_notification_url%.", '',
            hash( '%open_notification_url%', concat('<a href="', 'notification/settings'|ezurl(no), '">'), '%close_notification_url%', '</a>' )
        )}
    </div>
{/if}
<section class="hgroup">
    <h1>
        {sensor_translate('My activities', 'dashboard')}
    </h1>

    <ul class="list-inline" style="font-size: .8em;margin-top: 10px">
        <li><strong>{sensor_translate("Legend:")}</strong></li>
        <li><i class="fa fa-comments-o"></i> {sensor_translate("the issue contains comments")}</li>
        {if $simplified_dashboard|not()}
            <li>
                <i class="fa fa-commenting-o"></i> {sensor_translate("the issue contains comments pending moderation")}
            </li>
            <li><i class="fa fa-comments"></i> {sensor_translate("the issue contains private messages")}</li>
        {/if}
        <li><i class="fa fa-exclamation-triangle"></i> {sensor_translate("the issue contains unread timeline")}</li>
    </ul>

</section>

{if $simplified_dashboard}
    {include uri='design:sensor_api_gui/dashboard/parts/simplified_dashboard.tpl'}
{else}
    {include uri='design:sensor_api_gui/dashboard/parts/full_dashboard.tpl'}
{/if}
