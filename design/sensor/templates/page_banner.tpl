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
    <div class="hidden-xs">
        <div class="full_page_photo"><div id="map"></div><div id="map-spinner" style="position: absolute;bottom: 0;height: 2px;background: #f00;width: 0;z-index: 2000;"></div></div>
        <div style="background: #eee;padding-top: 15px" id="posts-search">
            <div class="container">
                <form class="row" role="search">
                    <div class="col-sm-2">
                        <input type="text" class="form-control" name="query" value="" placeholder="{'Ricerca testuale'|i18n('sensor/post')}">
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group" id="area-filter">
                            <select class="select form-control" name="area" data-placeholder="{'Area'|i18n('sensor/post')}">
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
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group" id="category-filter">
                            <select class="select form-control" name="category" data-placeholder="{'Categoria'|i18n('sensor/post')}" multiple="multiple">
                                {foreach $module_result.content_info.persistent_variable.categories.children as $item}
                                    <option value="{$item.id}"
                                            style="padding-left:{$item.level|mul(10)}px;{if $item.level|eq(0)}font-weight: bold;{/if}">{$item.name|wash()}</option>
                                    {foreach $item.children as $child}
                                        <option data-parent="{$item.id}" value="{$child.id}" style="padding-left:{$child.level|mul(10)}px;{if $child.level|eq(0)}font-weight: bold;{/if}">{$child.name|wash()}</option>
                                    {/foreach}
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group" id="type-filter">
                            <select class="select form-control" name="type" data-placeholder="{'Tipo'|i18n('sensor/post')}">
                                <option></option>
                                {foreach $module_result.content_info.persistent_variable.types as $item}
                                    <option value="{$item.identifier|wash()}">{$item.name|wash()}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <input type="text"
                                   name="published"
                                   class="form-control daterange"
                                   placeholder="{'Data creazione'|i18n('sensor/post')}"
                                   value=""/>
                        </div>
                    </div>
                    <div class="col-md-1" style="padding: 0">
                        <button type="submit" class="btn btn-info"><span class="fa fa-search"></span></button>
                        <button type="reset" class="btn btn-danger hide"><span class="fa fa-close"></span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/if}
{if sensor_settings().ShowSmartGui}
    {include name="add_post" uri='design:sensor_api_gui/add/add_post.tpl'}
{/if}
{undef $show_inbox_widget}