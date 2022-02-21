<table class="table table-bordered">
    <tr>
        <th rowspan="2"></th>
        {foreach $roles as $role}
            <th class="text-center" colspan="2">{$role.name|wash()}</th>
        {/foreach}
    </tr>
    <tr>
        {foreach $roles as $role}
            <th class="text-center">User</th>
            <th class="text-center">Group</th>
        {/foreach}
    </tr>
    {foreach $notification_types as $notification_type}
        <tr>
            <th>{$notification_type.name|wash()}</th>
            {foreach $roles as $role}
                <td>
                    <input type="checkbox"
                           data-attribute="notification-target-user-{$notification_type.identifier|wash()}-{$role.identifier|wash()}"
                           {if $notification_type.targets[$role.identifier]|contains('user')}checked="checked"{/if}
                           data-toggleconfig>
                </td>
                <td>
                    <input type="checkbox"
                           data-attribute="notification-target-group-{$notification_type.identifier|wash()}-{$role.identifier|wash()}"
                           {if $notification_type.targets[$role.identifier]|contains('group')}checked="checked"{/if}
                           data-toggleconfig>
                </td>
            {/foreach}
        </tr>
    {/foreach}
</table>
