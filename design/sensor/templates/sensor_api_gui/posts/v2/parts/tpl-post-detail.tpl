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

    {{if capabilities.is_a == 'sensor_operator' && reporter.id != author.id}}
    <ul class="list-inline">
        <li>{/literal}{'Segnalazione raccolta da'|i18n('sensor/post')}{literal} {{:reporter.name}}{{if channel && channel.icon}} {/literal}{'via'|i18n('sensor/post')}{literal} <i class="{{:channel.icon}}"></i> {{:channel.name}}{{/if}}</li>
    </ul>
    {{/if}}

    {{if relatedItems && relatedItems.length > 0}}
    <ul class="list-inline">
        <li>{/literal}{'Segnalazioni correlate'|i18n('sensor/post')}{literal}</li>
        {{for relatedItems}}<li><a href="/sensor/posts/{{:#data}}" class="label label-primary">{{:#data}}</a></li>{{/for}}
    </ul>
    {{/if}}

    {{if geoLocation && geoLocation.address}}
    <ul class="list-inline">
        <li><i class="fa fa-map-marker"></i> {{:geoLocation.address}}</li>
    </ul>
    {{/if}}

    <div class="row">
        {{if geoLocation && geoLocation.latitude && geoLocation.longitude}}
            <div class="col-md-{{if images.length}}6{{else}}10{{/if}}">
                <div class="post-map" style="width: 100%; height: 300px;"
                     data-lat="{{:geoLocation.latitude}}"
                     data-lng="{{:geoLocation.longitude}}"></div>
             </div>
        {{/if}}
        {{if images.length}}
        <div class="col-md-{{if geoLocation && geoLocation.latitude && geoLocation.longitude}}6{{else}}10{{/if}}">
            <div id="carousel-{{:id}}" class="carousel slide{{if geoLocation && geoLocation.latitude && geoLocation.longitude}} medium{{else}} large{{/if}}" data-ride="carousel">
              <div class="carousel-inner" role="listbox">
                {{for images ~capabilities=capabilities}}
                <div class="item{{if #index == 0}} active{{/if}}">
                  <a href="{{:original}}" data-gallery><img src="{{:thumbnail}}" /></a>
                  {{if ~capabilities.can_remove_image}}
                    <div data-action-wrapper>
                        <input type="hidden" data-value="files" value="{{:original}}" />
                        <a href="#" style="position:absolute;bottom:3px; right:3px; z-index:1000"
                           data-action="remove_image" data-parameters="files" data-confirmation="{/literal}{"Confermi l'eliminazione dell'immagine?"|i18n('sensor/post')|wash()}{literal}"
                           class="btn btn-danger btn-icon"><i class="fa fa-trash"></i></a>
                    </div>
                  {{/if}}
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