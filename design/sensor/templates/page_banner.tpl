{if is_set($social_pagedata)|not()}{def $social_pagedata = social_pagedata()}{/if}

{def $show_inbox_widget = cond(
    and(
        sensor_settings('SocketIsEnabled'),
        ezini('SensorConfig', 'ShowInboxWidget', 'ocsensor.ini')|eq('enabled'),
        fetch('user', 'has_access_to', hash('module','sensor','function','manage'))|not()
    ),
    true(),
    false()
)}

{if and( $show_inbox_widget|not(), is_set( $module_result.content_info.persistent_variable.sensor_home ), $social_pagedata.banner_path )}
    <div class="full_page_photo hidden-xs" style='background-image: url({$social_pagedata.banner_path|ezroot()});'>
        <div class="container">
            <section class="call_to_action">
                <h3 class="animated bounceInDown">{$social_pagedata.banner_title}</h3>
                <h4 class="animated bounceInUp skincolored">{$social_pagedata.banner_subtitle}</h4>
            </section>
        </div>
    </div>
{elseif and( is_set( $module_result.node_id ), $module_result.node_id|eq( sensor_postcontainer().node_id ) )}
    <div class="full_page_photo hidden-xs"><div id="map"></div><div id="map-spinner" style="position: absolute;bottom: 0;height: 2px;background: #f00;width: 0;z-index: 1000;"></div></div>
    <div style="background: #eee;padding: 15px 0 10px" id="posts-search">
        <div class="container">
            <form role="search">
                <div class="form-group-container">
                    <div class="form-group">
                        <input type="text" class="form-control" name="query" value="" placeholder="{sensor_translate('Search text')}">
                    </div>
                    <div class="form-group" id="area-filter">
                        <select class="select form-control" name="area" data-placeholder="{sensor_translate('Area')}">
                            <option></option>
                            {foreach $module_result.content_info.persistent_variable.areas.children as $item}
                                <option value="{$item.id}" style="padding-left:{$item.level|mul(10)}px;{if $item.level|eq(0)}font-weight: bold;{/if}">{$item.name|wash()}</option>
                                {foreach $item.children as $child}
                                    <option data-parent="{$item.id}" value="{$child.id}"
                                            style="padding-left:{$child.level|mul(10)}px;{if $child.level|eq(0)}font-weight: bold;{/if}">{$child.name|wash()}</option>
                                {/foreach}
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group" id="category-filter">
                        <select class="select form-control" name="category" data-placeholder="{sensor_translate('Category')}" multiple="multiple">
                            {foreach $module_result.content_info.persistent_variable.categories.children as $item}
                                <option value="{$item.id}"
                                        style="padding-left:{$item.level|mul(10)}px;{if $item.level|eq(0)}font-weight: bold;{/if}">{$item.name|wash()}</option>
                                {foreach $item.children as $child}
                                    <option data-parent="{$item.id}" value="{$child.id}" style="padding-left:{$child.level|mul(10)}px;{if $child.level|eq(0)}font-weight: bold;{/if}">{$child.name|wash()}</option>
                                {/foreach}
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group" id="type-filter">
                        <select class="select form-control" name="type" data-placeholder="{sensor_translate('Type')}">
                            <option></option>
                            {foreach $module_result.content_info.persistent_variable.types as $item}
                                <option value="{$item.identifier|wash()}">{$item.name|wash()}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text"
                               name="published"
                               class="form-control daterange"
                               placeholder="{sensor_translate('Creation date')}"
                               value=""/>
                    </div>
                    <div class="form-group hidden-xs hidden-sm">
                        <button type="submit" class="btn btn-info"><span class="fa fa-search"></span> <span class="hidden-sm hidden-md hidden-lg">{sensor_translate('Search')}</span></button>
                        <button type="reset" class="btn btn-danger hide"><span class="fa fa-close"></span> <span class="hidden-sm hidden-md hidden-lg">{sensor_translate('Cancel')}</span></button>
                    </div>
                </div>

                {if fetch('user', 'has_access_to', hash('module','sensor','function','manage'))}
                <div class="form-group-container">
                    <div class="form-group">
                        <select name="status"
                                class="select form-control"
                                data-placeholder="{sensor_translate('Status')}">
                            <option></option>
                            {foreach sensor_statuses() as $status}
                                <option value="{$status.identifier|wash()}">{$status.current_translation.name|wash()}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text"
                               name="author"
                               class="form-control daterange"
                               placeholder="{sensor_translate('Author')}"
                               value=""/>
                    </div>
                    {def $usergroups = fetch(content, list, hash( parent_node_id, ezini("UserSettings", "DefaultUserPlacement"),
                                                                  limitation, array(),
                                                                  attribute_filter, array(array('contentobject_id', '!=', sensor_operators_root_node().contentobject_id)),
                                                                  class_filter_type, 'include',
                                                                  class_filter_array, array('user_group'),
                                                                  order_by, array('name', true()) ) )}
                    {if count($usergroups)|gt(0)}
                    <div class="form-group">
                        <select name="usergroup"
                                class="select form-control"
                                data-placeholder="{sensor_translate('Author group')}">
                            <option></option>
                            <option value="0">{sensor_translate('No group')}</option>
                            {foreach $usergroups as $group}
                                <option value="{$group.contentobject_id}">{$group.name|wash()}</option>
                            {/foreach}
                        </select>
                    </div>
                    {/if}
                    {undef $usergroups}
                    <div class="form-group">
                        <select name="owner"
                                class="select select-operator form-control"
                                data-placeholder="{sensor_translate('Operator in charge')}"
                                data-type="operators">
                            <option value="'0'" style="font-style: italic">Nessun operatore incaricato</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="owner_group"
                                class="select select-group form-control"
                                data-placeholder="{sensor_translate('Group in charge')}"
                                data-type="groups">
                            <option value="'0'" style="font-style: italic">Nessun gruppo incaricato</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="observer"
                                class="select select-operator form-control"
                                data-placeholder="{sensor_translate('Observer')}"
                                data-type="operators">
                            <option></option>
                        </select>
                    </div>
                </div>
                {/if}

                <div class="form-group hidden-md hidden-lg">
                    <button type="submit" class="btn btn-info"><span class="fa fa-search"></span> <span class="hidden-sm hidden-md hidden-lg">{sensor_translate('Search')}</span></button>
                    <button type="reset" class="btn btn-danger hide"><span class="fa fa-close"></span> <span class="hidden-sm hidden-md hidden-lg">{sensor_translate('Cancel')}</span></button>
                </div>

            </form>
        </div>
    </div>
{/if}
{if sensor_settings().ShowSmartGui}
    {include name="add_post" uri='design:sensor_api_gui/add/add_post.tpl'}
{/if}
{undef $show_inbox_widget}
