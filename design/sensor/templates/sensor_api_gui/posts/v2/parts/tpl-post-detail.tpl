{literal}
<script id="tpl-post-detail" type="text/x-jsrender">

    <p class="lead" style="white-space:pre-wrap;">{{:~boldify(description)}}</p>

    {{if files.length}}
        <ul class="list-inline">
        {{for files ~capabilities=capabilities}}
            <li>
                <a href="{{:downloadUrl}}" title="{{:filename}}">
                    <img src="{{:icon}}" /> {{:filename}}
                </a>
                {{if ~capabilities.can_remove_file}}
                    <span data-action-wrapper>
                        <input type="hidden" data-value="files" value="{{:downloadUrl}}" />
                        <a href="#"
                           data-action="remove_file" data-parameters="files" data-confirmation="{{:~sensorTranslate('Are you sure to delete this file?')}}"
                           class="btn btn-danger btn-icon btn-xs"><i class="fa fa-trash"></i></a>
                    </span>
                  {{/if}}
            </li>
        {{/for}}
        </ul>
    {{/if}}

    <ul class="list-inline">
        <li>{{:~sensorTranslate('Created at')}} {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}</li>
        {{if ~formatDate(modified, 'X') > ~formatDate(published, 'X')}}
            <li>{{:~sensorTranslate('Last modified at')}} {{:~formatDate(modified, 'DD/MM/YYYY HH:mm')}}</li>
        {{/if}}
        <li>{{:comments.length}} {{:~sensorTranslate('comments')}} - {{:responses.length}} {{:~sensorTranslate('official replies')}}    </li>
    </ul>

    {{if (capabilities.is_a == 'sensor_operator' || capabilities.can_behalf_of) && reporter.id != author.id}}
    <ul class="list-inline">
        <li>
        {{:~sensorTranslate('Issue reported by')}} {{if reporter.type == 'sensor_operator'}}<i class="fa fa-user-circle"></i>{{/if}} {{:reporter.name}}
            {{if reporter.isSuperUser}}{{for reporter.userGroups}}<span class="label label-default" style="display:none" data-usergroup="{{:#data}}">...</span>{{/for}}{{/if}}
            {{if channel && channel.icon}} {{:~sensorTranslate('via')}} <i class="{{:channel.icon}}"></i> {{:channel.name}}{{/if}}
        </li>
    </ul>
    {{/if}}

    {{if relatedItems && relatedItems.length > 0}}
    <ul class="list-inline">
        <li>{{:~sensorTranslate('Related issues')}}</li>
        {{for relatedItems}}<li><a href="{{:~accessPath("/sensor/posts/")}}{{:#data}}" class="label label-primary">{{:#data}}</a></li>{{/for}}
    </ul>
    {{/if}}

    {{if version > 1 && author.id == currentUserId}}
    <ul class="list-inline">
        <li>{{:~sensorTranslate('Il testo della proposta è stato modificato')}} <a href="#" data-original="{{:id}}">{{:~sensorTranslate('visualizza il testo originale')}}</a></li>
    </ul>
    <div id="modal-original" class="modal fade text-left">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-body">
            <div class="clearfix">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div id="data-original" class="clearfix"></div>
          </div>
        </div>
      </div>
    </div>
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
                           data-action="remove_image" data-parameters="files" data-confirmation="{{:~sensorTranslate('Are you sure to delete this image?')}}"
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
{include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-subscription.tpl'}