{if is_set( $error )}
<div class="alert alert-danger">{$error}</div>
{else}

<section class="hgroup">
    <h1>
        <span class="label label-primary" id="current-post-id">{$sensor_post.object.id}</span>
        {$sensor_post.object.name|wash()}
    </h1>
    <ul class="breadcrumb pull-right" id="current-post-breadcrumb">
        <li>
            <span class="label label-{$sensor_post.type.css_class}">{$sensor_post.type.name}</span>
        </li>
    </ul>
</section>

    <div class="row">
        <div class="col-md-12">

            <div class="row">
                {if or($sensor_post.object|has_attribute('geo'), $sensor_post.object|has_attribute('area'))}
                    <div class="col-md-4">
                        <aside class="widget">
                            {include uri='design:sensor/post/map.tpl'}
                        </aside>
                    </div>
                {/if}
                <div class="col-md-8" id="current-post-detail">
                    <p>{attribute_view_gui attribute=$sensor_post.object|attribute('description')}</p>
                    {if $sensor_post.object|has_attribute('attachment')}
                        <p>{attribute_view_gui attribute=$sensor_post.object|attribute('attachment')}</p>
                    {/if}
                    {if $sensor_post.object|has_attribute('image')}
                        <figure>{attribute_view_gui attribute=$sensor_post.object|attribute('image') image_class='large' alignment=center}</figure>
                    {/if}
                    {if $sensor_post.object|has_attribute('main_document')}
                        {attribute_view_gui attribute=$sensor_post.object|attribute('main_document')}
                    {/if}
                </div>
            </div>

            <div class="row">
                <div class="col-md-12" id="current-post-messages">
                    <div id="post_messages">
                        {if $sensor_post.response_count}
                            <div class="comment">
                                <h4>{'Risposte ufficiali'|i18n('sensor/messages')}</h4>
                                {foreach $sensor_post.response_items as $item}
                                    {include uri='design:sensor/post/post_message/response.tpl' is_read=cond( $sensor_post.current_participant, $sensor_post.current_participant.last_read|gt($item.modified), true()) item_link=$item message=$item.simple_message}
                                {/foreach}
                            </div>
                        {/if}
                    </div>
                </div>
            </div>

        </div>
    </div>

{/if} {* if error *}
