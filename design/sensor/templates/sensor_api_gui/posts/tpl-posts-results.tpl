{literal}
<script id="tpl-posts-results" type="text/x-jsrender">

	{{if pageCount > 1}}
	<div class="pagination-container text-center">
        <ul class="pagination">
            <li class="page-item disabled"><span class="text" style="cursor: auto;">{{:totalCount}} risultati</span></li>
        </ul>
        <ul class="pagination">
            <li class="page-item {{if !prevPageQuery}}disabled{{/if}}">
                <a class="page-link prevPage" {{if prevPageQuery}}data-page="{{>prevPage}}"{{/if}} href="#">
                    <span class="text"><i class="fa fa-arrow-left"></i></span>
                </a>
            </li>
            {{for pages ~current=currentPage}}
                <li class="page-item{{if ~current == query}} active{{/if}}"><a href="#" class="page-link page" data-page_number="{{:page}}" data-page="{{:query}}"{{if ~current == query}} data-current aria-current="page"{{/if}}>{{:page}}</a></li>
            {{/for}}
            <li class="page-item {{if !nextPageQuery}}disabled{{/if}}">
                <a class="page-link nextPage" {{if nextPageQuery}}data-page="{{>nextPage}}"{{/if}} href="#">
                    <span class="text"><i class="fa fa-arrow-right"></i></span>
                </a>
            </li>
        </ul>
	</div>
	{{else totalCount == 1}}
	    <div class="pagination-container text-center">
            <ul class="pagination">
                <li class="page-item disabled"><span class="text" style="cursor: auto;">Un risultato</span></li>
            </ul>
        </div>
	{{else totalCount > 0}}
	    <div class="pagination-container text-center">
            <ul class="pagination">
                <li class="page-item disabled"><span class="text" style="cursor: auto;">{{:totalCount}} risultati</span></li>
            </ul>
        </div>
    {{else}}
        <div class="pagination-container text-center">
            <ul class="pagination">
                <li class="page-item disabled"><span class="text" style="cursor: auto;">Nessun risultato</span></li>
            </ul>
        </div>
	{{/if}}

	<table class="table table-striped table-hover">
	{{for searchHits}}
        <div class="post-result service_teaser">
            <div class="row">
                 <div class="col-sm-12">
                    <h2 class="post-title">
                        <div class="post-identifier">
                            <span class="label label-{{:statusCss}}">{{:status.name}}</span>
                            <a href="{{:accessPath}}/sensor/posts/{{:id}}" class="label label-primary">{{:id}}</a>
                        </div>
                        <div>{{:subject}}</div>
                    </h2>
                    <ul class="list-inline">
                      <li>{{if !(privacy.identifier == 'public' && moderation.identifier != 'waiting')}}<i class="fa fa-lock"></i> {{/if}}<strong>{{:type.name}}</strong> di {{if canReadUsers}}<a href="/sensor/user/{{:author.id}}">{{:author.name}}</a>{{else}}{{:author.name}}{{/if}}</li>
                      <li><small><i class="fa fa-clock-o"></i> {/literal}{'Pubblicata il'|i18n('sensor/post')}{literal} {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}</small></li>
                      {{if ~formatDate(modified, 'X') > ~formatDate(published, 'X')}}
                          <li><small><i class="fa fa-clock-o"></i> {/literal}{'Ultima modifica del'|i18n('sensor/post')}{literal} {{:~formatDate(modified, 'DD/MM/YYYY HH:mm')}}</small></li>
                      {{/if}}
                  </ul>
                 </div>

                {{if images.length > 0}}
                <div class="service_photo col-sm-3 col-md-3">
                  <figure><img class="center-block" src="{{:images[0].thumbnail}}" /></figure>
                </div>
                {{/if}}

                <div class="service_details {{if images.length > 0}}col-sm-9 col-md-9{{else}}col-sm-12 col-md-12{{/if}}">
                  <ul class="list-inline">
                      {{if geoLocation && geoLocation.address}}
                          <li><small><i class="fa fa-map-marker"></i> {{:geoLocation.address}}</small></li>
                      {{else areas.length > 0}}
                          <li><small><i class="fa fa-map-marker"></i> {{for areas}}{{:name}}{{/for}}</small></li>
                      {{/if}}
                      {{if categories.length > 0}}
                        <li><small><i class="fa fa-tags"></i> {{for categories}}{{:name}}{{/for}}</small></li>
                      {{/if}}
                  </ul>
                  <p class="lead" style="white-space:pre-wrap;font-size: 18px;line-height: 1.25;">{{:description}}</p>
                  <a href="{{:accessPath}}/sensor/posts/{{:id}}" class="btn btn-default btn-bold pull-right btn-sm">{/literal}{"Dettagli"|i18n('sensor/dashboard')}{literal}</a>
              </div>
            </div>
        </div>
	{{/for}}

	{{if pageCount > 1}}
	<div class="pagination-container text-center">
        <ul class="pagination">
            <li class="page-item {{if !prevPageQuery}}disabled{{/if}}">
                <a class="page-link prevPage" {{if prevPageQuery}}data-page="{{>prevPage}}"{{/if}} href="#">
                    <span class="text"><i class="fa fa-arrow-left"></i></span>
                </a>
            </li>
            {{for pages ~current=currentPage}}
                <li class="page-item{{if ~current == query}} active{{/if}}"><a href="#" class="page-link page" data-page_number="{{:page}}" data-page="{{:query}}"{{if ~current == query}} data-current aria-current="page"{{/if}}>{{:page}}</a></li>
            {{/for}}
            <li class="page-item {{if !nextPageQuery}}disabled{{/if}}">
                <a class="page-link nextPage" {{if nextPageQuery}}data-page="{{>nextPage}}"{{/if}} href="#">
                    <span class="text"><i class="fa fa-arrow-right"></i></span>
                </a>
            </li>
        </ul>
	</div>
	{{/if}}
</script>
{/literal}