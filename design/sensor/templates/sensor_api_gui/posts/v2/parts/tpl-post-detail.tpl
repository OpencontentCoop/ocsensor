{literal}
<script id="tpl-post-detail" type="text/x-jsrender">

    <p class="lead" style="white-space:pre-wrap;">{{:description}}</p>
    <ul class="list-inline">
        <li>{/literal}{'Pubblicata il'|i18n('sensor/post')}{literal} {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}</li>
        {{if ~formatDate(modified, 'X') > ~formatDate(published, 'X')}}
            <li>{/literal}{'Ultima modifica del'|i18n('sensor/post')}{literal} {{:~formatDate(modified, 'DD/MM/YYYY HH:mm')}}</li>
        {{/if}}
        <li>{{:comments.length}} {/literal}{'commenti'|i18n('sensor/post')}{literal} - {{:responses.length}} {/literal}{'risposte ufficiali'|i18n('sensor/post')}{literal}</li>
    </ul>
    <ul class="list-inline">
        {{if geoLocation.address}}
            <li><i class="fa fa-map-marker"></i> {{:geoLocation.address}}</li>
        {{/if}}
    </ul>

    <div class="row">
        {{if geoLocation.latitude && geoLocation.longitude}}
            <div class="col-md-{{if images.length}}6{{else}}10{{/if}}">
                <div class="post-map" style="width: 100%; height: 300px;"
                     data-lat="{{:geoLocation.latitude}}"
                     data-lng="{{:geoLocation.longitude}}"></div>
             </div>
        {{/if}}
        {{if images.length}}
        <div class="col-md-{{if geoLocation.latitude && geoLocation.longitude}}6{{else}}10{{/if}}">
            <div id="carousel-{{:id}}" class="carousel slide{{if geoLocation.latitude && geoLocation.longitude}} medium{{else}} large{{/if}}" data-ride="carousel">
              <div class="carousel-inner" role="listbox">
                {{for images}}
                <div class="item{{if #index == 0}} active{{/if}}">
                  <a href="{{:original}}" data-gallery><img src="{{:thumbnail}}" /></a>
                </div>
                {{/for}}
              </div>
              {{if images.length > 1}}
              <a class="left carousel-control" href="#carousel-{{:id}}" role="button" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
              </a>
              <a class="right carousel-control" href="#carousel-{{:id}}" role="button" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
              </a>
              {{/if}}
            </div>
        </div>
        {{/if}}
    </div>

</script>
{/literal}