{literal}
<script id="tpl-posts-results" type="text/x-jsrender">

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

	<table class="table table-striped table-hover">
	{{for searchHits}}
        <div class="post">
            <div class="row">
              <div class="col-md-12">
                <section class="hgroup" style="margin-top:0">
                  <h2 class="section_header skincolored" style="margin-bottom: 0;border: none">
                  <a href="{{:accessPath}}/sensor/posts/{{:id}}">
                    <span class="label label-primary">{{:id}}</span>
                    {{:subject}}
                  </a>
                  <small>{{:author.name}}</small>
                  </h2>
                  <ul class="breadcrumb pull-right">
                  <li>
                    <span class="label label-{{:typeCss}}">{{:type.name}}</span>
                    <span class="label label-{{:statusCss}}">{{:status.name}}</span>
                    {{if privacy.identifier == 'private'}}
                      <span class="label label-default">{{:privacy.name}}</span>
                    {{/if}}
                    {{if privacy.moderation == 'waiting'}}
                      <span class="label label-danger">{{{:moderation.name}}</span>
                    {{/if}}
                    </li>
                  </ul>
                </section>
              </div>
            </div>
            <div class="row service_teaser" style="margin-bottom: 10px;">
                {{if images.length > 0}}
                <div class="service_photo col-sm-4 col-md-4">
                  <figure><a href="{{:images[0].original}}" data-gallery><img class="center-block" src="{{:images[0].thumbnail}}" /></a></figure>
                </div>
                {{/if}}
                <div class="service_details {{if images.length > 0}}col-sm-8 col-md-8{{else}}col-sm-12 col-md-12{{/if}}">
                  <div class="clearfix">
                      <p class="pull-left">
                          {{if geoLocation}}
                              <i class="fa fa-map-marker"></i> {{:geoLocation.address}}
                          {{else areas.length > 0}}
                              {{for areas}}{{:name}}{{/for}}
                          {{/if}}
                      </p>
                  </div>
                  <p>{{:description}}</p>
                  <ul class="list-inline">
                      <li><small><i class="fa fa-clock-o"></i> {/literal}{'Pubblicata il'|i18n('sensor/post')}{literal} {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}</small></li>
                      {{if ~formatDate(modified, 'X') > ~formatDate(published, 'X')}}
                          <li><small><i class="fa fa-clock-o"></i> {/literal}{'Ultima modifica del'|i18n('sensor/post')}{literal} {{:~formatDate(modified, 'DD/MM/YYYY HH:mm')}}</small></li>
                      {{/if}}
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
                  <a href="{{:accessPath}}/sensor/posts/{{:id}}" class="btn btn-info btn-sm">{/literal}{"Dettagli"|i18n('sensor/dashboard')}{literal}</a>
                  {{if capabilities.can_edit}}
                      <a class="btn btn-warning btn-sm" href="{{:accessPath}}/sensor/edit/{{:id}}" data-post="{{:id}}">{/literal}{'Edit'|i18n( 'design/admin/node/view/full' )}{literal}</a>
                  {{/if}}
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