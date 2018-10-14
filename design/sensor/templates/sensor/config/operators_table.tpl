{def $item_limit=20}
{def $query = false()}
{if $view_parameters.query}
    {set $query = concat('(*',$view_parameters.query|downcase(),'*) OR ',$view_parameters.query|downcase())}
{/if}
{def $search = fetch( ezfind, search, hash( query, $query, 
                                            subtree_array, array( $operator_parent_node.node_id ), 
                                            limit, $item_limit, offset, $view_parameters.offset, 
                                            filter, array('-meta_class_identifier_ms:user_group'),
                                            sort_by, hash( 'name', 'asc' ) ) )}

{def $operators_count = $search.SearchCount
     $operators = $search.SearchResult}

<table class="table table-hover">
    {foreach $operators as $operator}
        {def $userSetting = $operator|user_settings()}
        <tr>
            <td>
                {if $userSetting.is_enabled|not()}<span style="text-decoration: line-through">{/if}
                    {*<a href="{$operator.url_alias|ezurl(no)}">{$operator.name|wash()}</a>*}{include uri='design:content/view/sensor_person.tpl' sensor_person=$operator.object}
                    {if $userSetting.is_enabled|not()}</span>{/if}
            </td>
            <td width="1">
                <div class="notification-dropdown-container dropdown" data-user="{$operator.contentobject_id}">
                    <div class="button-group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-bell"></i> <span class="caret"></span>
                        </button>
                        <ul class="notification-dropdown-menu dropdown-menu">
                        </ul>
                    </div>
                </div>
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

{literal}
<script>
    $(document).ready(function(){

        var baseUrl = "{/literal}{'sensor/notifications'|ezurl(no)}/{literal}";

        var onOptionClick = function( event ) {
            var $target = $( event.currentTarget );
            var identifier = $target.data('identifier');
            var user = $target.data('user');
            var menu = $target.parents('.notification-dropdown-container .notification-dropdown-menu');

            $(event.target).blur();
            var enable = $(event.target).prop('checked');
            if ($(event.target).attr('type') == 'checkbox') {
                jQuery.ajax({
                    url: baseUrl + user + '/' + identifier,
                    type: enable ? 'post' : 'delete',
                    success: function (response) {
                        buildNotificationMenu(user, menu);
                    }
                });
            }

            event.stopPropagation();
            event.preventDefault();
        };

        var buildNotificationMenu = function(user, menu){
            menu.html('<li style="padding: 50px; text-align: center; font-size: 2em;"><i class="fa fa-gear fa-spin fa2x"></i></li>');
            $.get(baseUrl+user, function(response){
                if (response.result && response.result == 'success'){
                    menu.html('');
                    var add = $('<li><a href="#" class="small" data-user="'+user+'" data-identifier="all" tabIndex="-1"><input type="checkbox"/><b> Attiva tutto</b></a></li>');
                    add.find('a').on('click', function(e){onOptionClick(e)});
                    menu.append(add);
                    var remove = $('<li><a href="#" class="small" data-user="'+user+'" data-identifier="none" tabIndex="-1"><input type="checkbox"/><b> Disattiva tutto</b></a></li>');
                    remove.find('a').on('click', function(e){onOptionClick(e)});
                    menu.append(remove);
                    $.each(response.data, function(){
                        var item = $('<li><a href="#" class="small" data-user="'+user+'" data-identifier="'+this.identifier+'" tabIndex="-1"><input type="checkbox"/>&nbsp;'+this.name+'</a></li>');
                        if (this.enabled){
                            item.find('input').attr( 'checked', true );
                        }
                        item.find('a').on('click', function(e){onOptionClick(e)});
                        menu.append(item);
                    })
                }else{
                    console.log(response);
                }
            });
        };

        $('.notification-dropdown-container').on('show.bs.dropdown', function () {
            var user = $(this).data('user');
            var menu = $(this).find('.notification-dropdown-menu');
            buildNotificationMenu(user, menu);
        });

    })
</script>
{/literal}
