{literal}
<script id="tpl-dashboard-results" type="text/x-jsrender">
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
        <tr {{if (readingStatuses.unread_comments + readingStatuses.unread_private_messages + readingStatuses.unread_responses) > 0}}class="danger"{{/if}}>
          <td style="vertical-align: middle;white-space: nowrap;" width="1">
            {{if comments.length > 0}}
              <p><i class="fa fa-comments-o{{if readingStatuses.unread_comments > 0}} faa-tada animated{{/if}}"> </i></p>
            {{/if}}
            {{if privateMessages.length > 0}}
              <p><i class="fa fa-comments{{if readingStatuses.unread_private_messages > 0}} faa-tada animated{{/if}}"> </i></p>
            {{/if}}
            {{if readingStatuses.unread_timelines > 0}}
              <p><i class="fa fa-exclamation-triangle faa-tada animated"></i></p>
            {{/if}}
          </td>
          <td>
            <ul class="list-inline">
              <li><strong>{{:id}}</strong></li>
              <li>
                {{if privacy.identifier == 'private'}}
                  <span class="label label-default">{{:privacy.name}}</span>
                {{/if}}
                {{if privacy.moderation == 'waiting'}}
                  <span class="label label-danger">{{:moderation.name}}</span>
                {{/if}}
                <span class="label label-{{:typeCss}}">{{:type.name}}</span>
                <span class="label label-{{:statusCss}}">{{:status.name}}</span>
              </li>
            </ul>
            <ul class="list-inline">
              <li><small><strong>{/literal}{"Creata"|i18n('sensor/dashboard')}{literal}</strong> {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}</small></li>
              {{if ~formatDate(modified, 'X') > ~formatDate(published, 'X')}}<li><small><strong>{/literal}{"Modificata"|i18n('sensor/dashboard')}{literal}</strong> {{:~formatDate(modified, 'DD/MM/YYYY HH:mm')}}</small></li>{{/if}}
              {/literal}{if fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'manage' ) )}{literal}
              {{if workflowStatus.identifier != 'closed'}}
                <li><small><strong>{/literal}{"Scadenza"|i18n('sensor/dashboard')}{literal}</strong></small> <span class="label label-{{:expirationInfo.label}}" title="{{:~formatDate(expirationInfo.expirationDateTime, 'DD/MM/YYYY HH:mm')}}">{{:expirationInfo.text}}</span></li>
              {{/if}}
              {/literal}{/if}{literal}
            </ul>
            <p>
              {{:subject}}
            </p>
            <ul class="list-unstyled">
              <li><small><strong>{/literal}{"Autore"|i18n('sensor/dashboard')}{literal}</strong> {{:author.name}}{{if reporter.id != author.id}} ({{:reporter.name}}){{/if}}</small></li>
                {{if categories.length > 0}}
                    <li><small><i class="fa fa-tags"></i> {{for categories}}{{:name}}{{/for}}</small></li>
                {{/if}}
                {{if areas.length > 0}}
                    <li><small><i class="fa fa-map-pin"></i> {{for areas}}{{:name}}{{/for}}</small></li>
                {{/if}}
                {{if owners.length > 0}}
                    <li><small><strong>{/literal}{'In carico a'|i18n('sensor/post')}{literal}</strong> {{for owners}}{{:name}}{{if description}} ({{:description}}){{/if}}{{/for}}</small></li>
                {{else lastTimelineItem}}
                    <li><small>{{:lastTimelineItem.text}}</small></li>
                {{/if}}
            </ul>
          </td>
          <td class="text-left">
              <p><a href="{{:accessPath}}/sensor/posts/{{:id}}" class="btn btn-info btn-sm">{/literal}{"Dettagli"|i18n('sensor/dashboard')}{literal}</a></p>
              {{if capabilities.can_edit}}
                  <p><a class="btn btn-warning btn-sm" href="{{:accessPath}}/sensor/edit/{{:id}}" data-post="{{:id}}">{/literal}{'Edit'|i18n( 'design/admin/node/view/full' )}{literal}</a></p>
              {{/if}}
              {{if capabilities.can_remove}}
                  <p><a class="btn btn-danger btn-sm" href="#"
                     data-remove
                     data-confirmation="{/literal}{'Sei sicuro di voler rimuovere questo contenuto?'|i18n( 'sensor/messages' )|wash(javascript)}{literal}"
                     data-post="{{:id}}">{/literal}{'Remove'|i18n( 'design/admin/node/view/full' )}{literal}</a></p>
              {{/if}}
          </td>
        </tr>
	{{/for}}
	</table>
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