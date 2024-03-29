{def $handlers=$handler.collaboration_handlers $selection=$handler.collaboration_selections}
{def $access_groups = array('standard')}
{if fetch('user', 'has_access_to', hash('module','sensor','function','manage'))}
    {set $access_groups = array('standard','operator')}
{/if}


<input type="hidden" name="CollaborationHandlerSelection" value="1"/>
{foreach $handlers as $current_handler}
    {if $current_handler.info.type-identifier|eq(sensor_collaboration_identifier())}

        {if is_array($current_handler.info.notification-types)}

            {if and(fetch('user', 'has_access_to', hash('module','sensor','function','manage')), sensor_settings('SocketIsEnabled'))}
            <div class="panel panel-default">
                <table class="table">
                    <tr id="desktop-notification-settings" class="hide">
                        <td width="1"><i class="fa fa-desktop"></i></td>
                        <td>
                            <span class="notificationPermissionStatus granted hide">
                                {sensor_translate('Desktop notifications enabled')}
                                <br/>
                                <small>{sensor_translate('Use your browser settings to disable desktop notifications')}</small>
                            </span>
                            <span class="notificationPermissionStatus denied hide">
                                {sensor_translate('Desktop notifications disabled')}
                                <br/>
                                <small>{sensor_translate('Use your browser settings to allow desktop notifications to be enabled')}</small>
                            </span>
                            <span class="notificationPermissionStatus default hide">
                                <a href="#" class="enableNotificationButton btn btn-xs btn-success">{sensor_translate('Enable desktop notifications')}</a>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            {/if}

            <div class="panel panel-default">
                <table class="table table-striped desktopNotifications">
                {foreach $current_handler.info.notification-types as $type}
                    {if $access_groups|contains($type.group)}
                        <tr>
                            <td width="1">
                                <input type="checkbox"
                                       name="CollaborationHandlerSelection_{$handler.id_string}[]"
                                       value="{$current_handler.info.type-identifier}_{$type.identifier}" {if $selection|contains(concat($current_handler.info.type-identifier,'_',$type.identifier))} checked="checked"{/if} />
                            </td>
                            <td>
                                {$type.name|wash}
                                {if is_set($type.description)}<br/><small>{$type.description|wash()}</small>{/if}
                            </td>
                            <td style="vertical-align: middle">
                                <div class="{$type.identifier} hide desktopNotificationType">
                                    <i class="fa fa-desktop"></i>
                                </div>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
                </table>
                <div class="panel-footer">
                    <input class="button btn btn-xs btn-success" type="submit" name="Store"
                           value="{sensor_translate('Save your settings')}"/>
                </div>
            </div>
        {/if}
        {/if}
    {/foreach}



