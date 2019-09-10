{literal}
<script id="tpl-post-detail" type="text/x-jsrender">
<div class="row">
    <div class="col-md-4">
      <aside class="widget">
        <div class="post-map" style="width: 100%; height: 200px;"
             data-lat={{:geoLocation.latitude}}
             data-lng={{:geoLocation.longitude}}></div>
         <small><i class="fa fa-map-marker"></i> {{:geoLocation.address}}</small>
      </aside>
    </div>
    <div class="col-md-8">
        <p>{{:description}}</p>
        {{for images}}
            <figure><a href="{{:original}}" data-gallery><img class="center-block" src="{{:thumbnail}}" /></a></figure>
        {{/for}}
        <ul class="list-inline">
            <li><small><i class="fa fa-clock-o"></i> {/literal}{'Pubblicata il'|i18n('sensor/post')}{literal} {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}</small></li>
            {{if ~formatDate(modified, 'X') > ~formatDate(published, 'X')}}
                <li><small><i class="fa fa-clock-o"></i> {/literal}{'Ultima modifica del'|i18n('sensor/post')}{literal} {{:~formatDate(modified, 'DD/MM/YYYY HH:mm')}}</small></li>
            {{/if}}
        </ul>
        <ul class="list-inline">
            {{if owners.length > 0}}
                <li><small><i class="fa fa-user"></i> {/literal}{'In carico a'|i18n('sensor/post')}{literal} {{for owners}}{{:name}}{{if description}} ({{:description}}){{/if}}{{/for}}</small></li>
            {{/if}}
            <li><small><i class="fa fa-comments"></i> {{:comments.length}} {/literal}{'commenti'|i18n('sensor/post')}{literal}</small></li>
            <li><small><i class="fa fa-comment"></i> {{:responses.length}} {/literal}{'risposte ufficiali'|i18n('sensor/post')}{literal}</small></li>
            {{if categories.length > 0}}
                <li><small><i class="fa fa-tags"></i> {{for categories}}{{:name}}{{/for}}</small></li>
            {{/if}}
            {{if areas.length > 0}}
                <li><small><i class="fa fa-map-pin"></i> {{for areas}}{{:name}}{{/for}}</small></li>
            {{/if}}
          </ul>
    </div>
</div>
</script>
{/literal}