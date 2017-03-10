<tr>
    {if $item.node.class_identifier|eq('user_group')}
        <td>
            <span style="padding-left:{$recursion|mul(20)}px">
            {if $recursion|eq(0)}<strong>{/if}
                {$item.node.name|wash()}
            {if $recursion|eq(0)}</strong>{/if}
          </span>
        </td>

        <td>
            {foreach $item.node.object.available_languages as $language}
                {foreach fetch( 'content', 'translation_list' ) as $locale}
                    {if $locale.locale_code|eq($language)}
                        <img src="{$locale.locale_code|flag_icon()}" />
                    {/if}
                {/foreach}
            {/foreach}
        </td>

        <td width="1">
            {if $item.children|count()|gt(0)}
                <a href={concat("/websitetoolbar/sort/",$item.node.node_id)|ezurl()}><i class="fa fa-sort-alpha-asc "></i>
            {/if}
        </td>

        <td width="1">
            <div class="dropdown" data-user="{$item.node.contentobject_id}">
                <div class="button-group">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-cog" aria-hidden="true"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu pull-right">
                        <p class="dropdown-header">{"Azioni gruppo"|i18n('sensor/config')}</p>
                        <table>
                            <tr>
                                <td>
                                    {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$item.node redirect_if_discarded=$redirect_if_discarded redirect_after_publish=$redirect_after_publish}
                                </td>
                                <td><small>{"Modifica"|i18n('sensor/config')}</small></td>
                            </tr>
                            <tr>
                                <td>
                                    <a class="btn btn-link btn-xs" title="{'Aggiungi'|i18n('social_user/config')}  {$operator_class.name} in {$item.node.name|wash()}" href="{concat('openpa/add/', $operator_class.identifier, '/?parent=',$item.node.node_id)|ezurl(no)}"><i class="fa fa-plus"></i></a>
                                </td>
                                <td><small>{'Aggiungi'|i18n('social_user/config')}  {$operator_class.name} in {$item.node.name|wash()}</small></td>
                            </tr>
                            <tr>
                                <td>
                                    {include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$item.node redirect_if_cancel='/sensor/config/operators' redirect_after_remove='/sensor/config/operators'}
                                </td>
                                <td><small>{"Elimina"|i18n('sensor/config')}</small></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </td>


    {else}
        {def $userSetting = $operator|user_settings()}
        <td>
            <span style="padding-left:{$recursion|mul(20)}px">
                {*if $userSetting.is_enabled|not()}<span style="text-decoration: line-through">{/if*}
                {include uri='design:content/view/sensor_person.tpl' sensor_person=$item.node.object}
                {*if $userSetting.is_enabled|not()}</span>{/if*}
            </span>
            {if fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'behalf', 'user_id', $item.node.contentobject_id ) )}
                <span title="{"L'utente può inserire segnalazioni per conto di altri"|i18n('sensor/config')}"><i class="fa fa-life-ring"></i></span>
            {/if}
        </td>

        <td>
            {foreach $item.node.object.available_languages as $language}
                {foreach $locales as $locale}
                    {if $locale.locale_code|eq($language)}
                        <img src="{$locale.locale_code|flag_icon()}" />
                    {/if}
                {/foreach}
            {/foreach}
        </td>

        {*<td width="1">
            {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$item.node redirect_if_discarded='/sensor/config/operators' redirect_after_publish='/sensor/config/operators'}
        </td>
        <td width="1">
            <a href="{concat('social_user/setting/',$item.node.contentobject_id)|ezurl(no)}"><i class="fa fa-user"></i></a>
        </td>*}

        <td width="1">
            <div class="notification-dropdown-container dropdown" data-user="{$item.node.contentobject_id}">
                <div class="button-group">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell"></i> <span class="caret"></span>
                    </button>
                    <ul class="notification-dropdown-menu dropdown-menu pull-right">
                    </ul>
                </div>
            </div>
        </td>
        <td width="1">
            <div class="dropdown" data-user="{$item.node.contentobject_id}">
                <div class="button-group">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-cog" aria-hidden="true"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu pull-right">
                        <p class="dropdown-header">{"Azioni gruppo"|i18n('sensor/config')}</p>
                        <table>
                            <tr>
                                <td>
                                    {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$item.node redirect_if_discarded='/sensor/config/operators' redirect_after_publish='/sensor/config/operators'}
                                </td>
                                <td><small>{"Impostazioni utente"|i18n('sensor/config')}</small></td>
                            </tr>
                            <tr>
                                <td>
                                    <a class="btn btn-link btn-xs" href="{concat('social_user/setting/',$item.node.contentobject_id)|ezurl(no)}"><i class="fa fa-user"></i></a>
                                </td>
                                <td><small>{"Modifica"|i18n('sensor/config')}</small></td>
                            </tr>
                            <tr>
                                <td>
                                    {include name=trash uri='design:parts/toolbar/node_move.tpl' current_node=$item.node}
                                </td>
                                <td><small>{"Sposta"|i18n('sensor/config')}</small></td>
                            </tr>
                            <tr>
                                <td>
                                    {include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$item.node redirect_if_cancel='/sensor/config/operators' redirect_after_remove='/sensor/config/operators'}
                                </td>
                                <td><small>{"Elimina"|i18n('sensor/config')}</small></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </td>
        {*<td width="1">
            {include name=trash uri='design:parts/toolbar/node_move.tpl' current_node=$item.node}
            {include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$item.node redirect_if_cancel='/sensor/config/operators' redirect_after_remove='/sensor/config/operators'}
        </td>*}
        {*<td width="1">
          {if fetch( 'user', 'has_access_to', hash( 'module', 'user', 'function', 'setting' ))}
            <form name="Setting" method="post" action={concat( 'user/setting/', $operator.contentobject_id )|ezurl}>
              <input type="hidden" name="is_enabled" value={if $userSetting.is_enabled|not()}"1"{else}""{/if} />
              <button class="btn-link btn-xs" type="submit" name="UpdateSettingButton" title="{if $userSetting.is_enabled}{'Blocca'|i18n('sensor/config')}{else}{'Sblocca'|i18n('sensor/config')}{/if}">{if $userSetting.is_enabled}<i class="fa fa-ban"></i>{else}<i class="fa fa-check-circle"></i>{/if}</button>

            </form>
          {/if}
        </td>*}
        {undef $userSetting}
    {/if}
</tr>

{if $item.children|count()|gt(0)}
    {set $recursion = $recursion|inc()}
    {foreach $item.children as $item_child}
        {include name=itemtree uri='design:sensor/config/walk_item_operators_table.tpl' redirect_if_discarded=$redirect_if_discarded redirect_after_publish=$redirect_after_publish item=$item_child recursion=$recursion operator_class=$operator_class}
    {/foreach}
{/if}