{literal}
<script id="tpl-post-timeline" type="text/x-jsrender">
{{if timelineItems.length > 0}}
    <aside class="widget timeline" id="current-post-timeline">
        <h4>{/literal}{'Cronologia'|i18n('sensor/post')}{literal}</h4>
        <ol class="list-unstyled">
            {{for timelineItems}}
                <li>
                    <div class="icon"><i class="fa fa-clock-o"></i></div>
                    <div class="title">{{:creator.name}} &middot; {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}</div>
                    <div class="content"><small>{{:text}}</small></div>
                </li>
            {{/for}}
            </dl>
    </aside>
{{/if}}
</script>
{/literal}