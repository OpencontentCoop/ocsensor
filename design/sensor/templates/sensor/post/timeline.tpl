{if $post.timelineItems.count|gt(0)}
    <aside class="widget timeline" id="current-post-timeline">
        <h4>{'Cronologia'|i18n('sensor/post')}</h4>
        <ol class="list-unstyled">
            {foreach $post.timelineItems.messages as $message}
                <li>
                    <div class="icon"><i class="fa fa-clock-o"></i></div>
                    <div class="title">{$message.published|sensor_datetime(format, shortdatetime)}</div>
                    <div class="content"><small>{$message.text|wash()}</small></div>
                </li>
            {/foreach}
        </ol>
    </aside>
{/if}