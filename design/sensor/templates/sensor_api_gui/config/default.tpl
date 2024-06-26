<table class="table table-striped">
    {if $root.can_edit}
        <tr>
            <th>{sensor_translate('General Settings', 'config')}</th>
            <td class="text-center">
                <a class="btn btn-default edit"
                   data-object="{$root.contentobject_id}"
                   href="{concat('/content/edit/', $root.contentobject_id, '/f')|ezurl(no)}">
                    {sensor_translate('Edit')}
                </a>
            </td>
        </tr>
    {/if}
    {if $post_container_node.can_edit}
        <tr>
            <th>{sensor_translate('OpenSegnalazioni Settings', 'config')}</th>
            <td class="text-center">
                <a class="btn btn-default" href="{concat('/content/edit/', $post_container_node.contentobject_id, '/f')|ezurl(no)}">{sensor_translate('Edit')}</a>
            </td>
        </tr>
    {/if}
    <tr>
        <th>
            {sensor_translate('Reference for the citizen', 'config')}
            {def $default_approvers = sensor_default_approvers()}
            {if count($default_approvers)|gt(0)}
                {foreach $default_approvers as $approver}{include uri='design:content/view/sensor_person.tpl' sensor_person=$approver}{delimiter}, {/delimiter}{/foreach}
            {/if}
            <br /><small>{sensor_translate('This option identifies the operator who takes charge of the issues in the first instance')}</small>
        </th>
        <td class="text-center">
            <form class="form-inline" style="display: inline" action="{'sensor/config/operators'|ezurl(no)}" method="post">
                <button class="btn btn-default" name="SelectDefaultApprover" type="submit">{sensor_translate('Change')}</button>
            </form>
        </td>
    </tr>
    <tr{if $moderation_is_enabled} class="warning"{/if}>
        <th>
            {sensor_translate('Set each new issue as private')}
            <br /><small>{sensor_translate('If this option is enabled, new issues are not publicly visible')}</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $moderation_is_enabled}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="Moderation"{else}disabled{/if}>
        </td>
    </tr>
    <tr>
        <th>
            {sensor_translate('Hide publication consent from the reporter')}
            <br /><small>{sensor_translate('If the option is enabled, the reporter is not asked for consent to make the issue public: operators will not be able to make it public in any way.')}</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.HidePrivacyChoice}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="HidePrivacyChoice"{else}disabled{/if}>
        </td>
    </tr>
    <tr>
        <th>
            {sensor_translate('Hide the choice of the type of issue from the reporter')}
            <br /><small>{sensor_translate('If the option is activated, the reporter is not asked to choose the type of issue')}</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.HideTypeChoice}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="HideTypeChoice"{else}disabled{/if}>
        </td>
    </tr>
    <tr>
        <th>
            {sensor_translate('Displays the ajax input interface')}
            <br /><small>{sensor_translate('The new input interface is exposed to the reporter')}</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.ShowSmartGui}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="ShowSmartGui"{else}disabled{/if}>
        </td>
    </tr>
    <tr>
        <th>
            {sensor_translate('Hide the detailed timeline from the public')}
            <br /><small>{sensor_translate('If the option is activated, only the acceptance and closure events will be shown in the history')}</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.HideTimelineDetails}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="HideTimelineDetails"{else}disabled{/if}>
        </td>
    </tr>
    <tr>
        <th>
            {sensor_translate('Hide the operators name from the public')}
            <br /><small>{sensor_translate('If the option is enabled, the names of the operators will be replaced with a generic string')} <em>{sensor_translate('Operator')}</em></small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.HideOperatorNames}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="HideOperatorNames"{else}disabled{/if}>
        </td>
    </tr>
    {def $can_hide_user_name = false()}
    {foreach sensor_root_class().fields as $field}
        {if $field.identifier|eq('hide_user_name')}
            {set $can_hide_user_name = true()}
            {break}
        {/if}
    {/foreach}
    {if $can_hide_user_name}
    <tr>
        <th>
            {sensor_translate('Hide the author name from the public')}
            <br /><small>{sensor_translate('If the option is enabled, the names of the useres will be hidden')}</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.HideUserNames}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="HideUserNames"{else}disabled{/if}>
        </td>
    </tr>
    {/if}

    <tr><td colspan="2" style="background: #fff"></td></tr>

    <tr>
        <th>
            {sensor_translate('When an issue is fixed, it always resets the reference for the citizen')}
            {foreach $default_approvers as $approver}{include uri='design:content/view/sensor_person.tpl' sensor_person=$approver}{delimiter}, {/delimiter}{/foreach}
            {def $force_is_enabled = cond($sensor_settings.ForceUrpApproverOnFix, true(), false())}
            {if $force_is_enabled}
                {set $force_is_enabled = false()}
                {foreach $default_approvers as $approver}
                    {if array('user', 'sensor_operator')|contains($approver.class_identifier)}
                        {set $force_is_enabled = true()}
                    {/if}
                {/foreach}
                {if $force_is_enabled|not}<p class="text-danger">{sensor_translate('This configuration is not applicable')}</p>{/if}
            {/if}
        </th>
        <td class="text-center">
            <input type="checkbox" {if $force_is_enabled}checked{/if} disabled data-toggleconfig>
        </td>
    </tr>
    <tr>
        <th>{sensor_translate('The reporter can reopen a closed issue')}</th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.AuthorCanReopen}checked{/if} disabled data-toggleconfig>
        </td>
    </tr>
    <tr>
        <th>{sensor_translate('The reference for the citizen can reopen a closed issue')}</th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.ApproverCanReopen}checked{/if} disabled data-toggleconfig>
        </td>
    </tr>
    {*
    <tr>
      <th>{sensor_translate('Prevent the area selection')}</th>
      <td class="text-center">
        <input type="checkbox" {if ezini( 'SensorConfig', 'ReadOnlySelectArea', 'ocsensor.ini' )|eq('enabled')}checked{/if} disabled data-toggleconfig>
      </td>
    </tr>
    *}
    <tr>
        <th>
            {sensor_translate('Allow public comments')}
            <br /><small>{sensor_translate('If this option is enabled, authenticated users can post comments to public issyes')}</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.CommentsAllowed}checked{/if} disabled data-toggleconfig>
        </td>
    </tr>
    <tr>
        <th>
            {sensor_translate('Exclusive recipients of private notes')}
            <br /><small>{sensor_translate('If this option is enabled, you can select which operators can read each note')}</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.UseDirectPrivateMessage}checked{/if} disabled data-toggleconfig>
        </td>
    </tr>
    <tr>
        <th>{sensor_translate('Show Inbox shortcuts')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.UseInboxContextActions}checked{/if} disabled data-toggleconfig></td>
    </tr>
    <tr>
        <th>{sensor_translate('Enable Inbox filter')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.UseInboxFilters}checked{/if} disabled data-toggleconfig></td>
    </tr>
    <tr>
        <th>{sensor_translate('Enable approver edit')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.AllowChangeApprover}checked{/if} disabled data-toggleconfig></td>
    </tr>
    <tr>
        <th>{sensor_translate('Show faq categories')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.ShowFaqCategories}checked{/if} disabled data-toggleconfig></td>
    </tr>
    <tr>
        <th>{sensor_translate('Show all private messages in Inbox')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.ShowInboxAllPrivateMessage}checked{/if} disabled data-toggleconfig></td>
    </tr>
    <tr>
        <th>{sensor_translate('Enable smart duplication gui')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.SmartDuplicationGui}checked{/if} disabled data-toggleconfig></td>
    </tr>
    <tr>
        <th>{sensor_translate('Private note mandatory before reassign')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.AddPrivateMessageBeforeReassign}checked{/if} disabled data-toggleconfig></td>
    </tr>
    <tr>
        <th>{sensor_translate('Add reporter operator as observer')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.AddBehalfOfUserAsObserver}checked{/if} disabled data-toggleconfig></td>
    </tr>
    <tr>
        <th>{sensor_translate('Enable super groups')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.AllowAdditionalMemberGroups}checked{/if} disabled data-toggleconfig></td>
    </tr>
    <tr>
        <th>{sensor_translate('Auto close super groups posts')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.CloseOnUserGroupPostFix}checked{/if} disabled data-toggleconfig></td>
    </tr>
    <tr>
        <th>{sensor_translate('Category mandatory for super groups')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.RequireCategoryForAdditionalMemberGroups}checked{/if} disabled data-toggleconfig></td>
    </tr>
    <tr>
        <th>{sensor_translate('Add super groups operator as observer')}</th>
        <td class="text-center"><input type="checkbox" {if $sensor_settings.AddOperatorSuperUserAsObserver}checked{/if} disabled data-toggleconfig></td>
    </tr>

</table>

{if fetch( user, has_access_to, hash( module, settings, function, view ) )}
    <p class="pull-right">
        <a target="_blank" href="{'/settings/view/ita_sensor/ocsensor.ini/'|ezurl(no)}"><small>{'Ini settings'|i18n('design/admin/parts/setup/menu')}</small></a>
    </p>
{/if}
