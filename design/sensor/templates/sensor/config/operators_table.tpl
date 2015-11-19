{def $item_limit=30}
{def $search = fetch( ezfind, search, hash( query, $view_parameters.query, subtree_array, array( $operator_parent_node.node_id ), limit, $item_limit, offset, $view_parameters.offset, sort_by, hash( 'name', 'asc' ) ) )}

{def $operators_count = $search.SearchCount
     $operators = $search.SearchResult}

<table class="table table-hover">
    {foreach $operators as $operator}
        {def $userSetting = $operator|user_settings()}
        <tr>
            <td>
                {if $userSetting.is_enabled|not()}<span style="text-decoration: line-through">{/if}
                    {*<a href="{$operator.url_alias|ezurl(no)}">{$operator.name|wash()}</a>*}{$operator.name|wash()}
                    {if $userSetting.is_enabled|not()}</span>{/if}
            </td>
            <td width="1">
                {if fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'behalf', 'user_id', $operator.contentobject_id ) )}
                    <span title="{"L'utente può inserire segnalazioni per conto di altri"|i18n('sensor/config')}"><i class="fa fa-life-ring"></i></span>
                {/if}
            </td>
            <td>
                {foreach $operator.object.available_languages as $language}
                    {foreach $locales as $locale}
                        {if $locale.locale_code|eq($language)}
                            <img src="{$locale.locale_code|flag_icon()}" />
                        {/if}
                    {/foreach}
                {/foreach}
            </td>
            <td width="1">
                <a href="{concat('social_user/setting/',$operator.contentobject_id)|ezurl(no)}"><i class="fa fa-user"></i></a>
            </td>
            <td width="1">{include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$operator redirect_if_discarded='/sensor/config/operators' redirect_after_publish='/sensor/config/operators'}</td>
            <td width="1">{include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$operator redirect_if_cancel='/sensor/config/operators' redirect_after_remove='/sensor/config/operators'}</td>
            {*<td width="1">
              {if fetch( 'user', 'has_access_to', hash( 'module', 'user', 'function', 'setting' ))}
                <form name="Setting" method="post" action={concat( 'user/setting/', $operator.contentobject_id )|ezurl}>
                  <input type="hidden" name="is_enabled" value={if $userSetting.is_enabled|not()}"1"{else}""{/if} />
                  <button class="btn-link btn-xs" type="submit" name="UpdateSettingButton" title="{if $userSetting.is_enabled}{'Blocca'|i18n('sensor/config')}{else}{'Sblocca'|i18n('sensor/config')}{/if}">{if $userSetting.is_enabled}<i class="fa fa-ban"></i>{else}<i class="fa fa-check-circle"></i>{/if}</button>

                </form>
              {/if}
            </td>*}
        </tr>
        {undef $userSetting}
    {/foreach}
</table>

{include name=navigator
        uri='design:navigator/google.tpl'
        page_uri='sensor/config/operators'
        item_count=$operators_count
        view_parameters=$view_parameters
        item_limit=$item_limit}