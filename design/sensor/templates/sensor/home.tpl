{def $show_inbox_widget = cond(
    and(
        sensor_settings('SocketIsEnabled'),
        ezini('SensorConfig', 'ShowInboxWidget', 'ocsensor.ini')|eq('enabled'),
        fetch('user', 'has_access_to', hash('module','sensor','function','manage'))|not()
    ),
    true(),
    false()
)}
{def $show_homepage_blocks = cond(
    and(
        ezini('SensorConfig', 'CustomHomepageDashboard', 'ocsensor.ini')|eq('enabled'),
        $current_user.is_logged_in,
        $current_user.contentobject.class_identifier|eq('user')
    ),
    true(),
    false()
)}
<section class="hgroup noborder">
{if $show_inbox_widget}
    {include uri='design:sensor_api_gui/todo.tpl'}
{elseif $show_homepage_blocks}
    {include uri='design:sensor/home_blocks.tpl'}
{else}
    <div class="row">
        <div class="col-sm-12">
            {def $post_container = sensor_postcontainer()}
            {if $post_container|has_attribute( 'short_description' )}
                <div class="service_teaser vertical wow animated flipInX animated">
                    {if $post_container|has_attribute( 'image' )}
                        <div class="service_photo">
                            <figure style="background-image:url({$post_container|attribute( 'image' ).content.original.full_path|ezroot(no)})"></figure>
                        </div>
                    {/if}
                    <div class="service_details">
                        <h2 class="section_header skincolored">
                            {$post_container.data_map.name.content|wash()}
                        </h2>
                        {attribute_view_gui attribute=$post_container.data_map.short_description}
                        {*<div id="sensorgraph" style="width: 100%; height: 500px; margin: 0 auto; padding: 10px;"></div>*}
                        {if $current_user.is_logged_in|not()}
                            <a href="#login"
                               class="btn btn-primary btn-lg btn-block">{sensor_translate('Login', 'menu')}</a>
                        {else}
                            <a href="{'sensor/add'|ezurl(no)}"
                               class="btn btn-primary btn-lg btn-block">{sensor_translate('Create issue', 'report')}</a>
                        {/if}
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/if}
</section>
{undef $show_inbox_widget}
