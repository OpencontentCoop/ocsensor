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
            <li class="page-item {{if !nextPageQuery}}disabled{{/if}}">
                <a class="page-link nextPage" {{if nextPageQuery}}data-page="{{>nextPage}}"{{/if}} href="#">
                    <span class="text"><i class="fa fa-arrow-right"></i></span>
                </a>
            </li>
        </ul>
	</div>
	{{/if}}
	<table class="table table-striped table-hover table-condensed"{{if pageCount <= 1}} style="margin-top:40px"{{/if}}>
        <thead>
            <tr>
                <th colspan="2"></th>
                <th>{{:~sensorTranslate('ID')}}</th>
                <th>{{:~sensorTranslate('Status')}}</th>
                <th>{{:~sensorTranslate('Author and subject')}}</th>
                <th>{{:~sensorTranslate('Created at')}}</th>
                <th>{{:~sensorTranslate('Reference')}}</th>
                <th>{{:~sensorTranslate('Assignment')}}</th>
                <th>{{:~sensorTranslate('Observer')}}</th>
            </tr>
        </thead>
        <tbody>
        {{for searchHits}}
            <tr data-href="{{:~accessPath("/sensor/posts/")}}{{:id}}" {{if readingStatuses && (readingStatuses.unread_comments + readingStatuses.unread_private_messages + readingStatuses.unread_responses) > 0}}class="danger"{{/if}}>
                <td>
                    {{if privacy.identifier == 'private' || moderation.identifier == 'waiting'}}
                      <div><i class="fa fa-lock"></i></div>
                    {{/if}}
                    {{if comments.length > 0}}
                      <div><i class="fa fa-comments-o{{if readingStatuses && readingStatuses.unread_comments > 0}} faa-tada animated{{/if}}"></i></div>
                    {{/if}}
                    {{if commentsToModerate.length > 0}}
                      <div><i class="fa fa-commenting-o"></i></div>
                    {{/if}}
                    {{if privateMessages.length > 0}}
                      <div><i class="fa fa-comments{{if readingStatuses && readingStatuses.unread_private_messages > 0}} faa-tada animated{{/if}}"></i></div>
                    {{/if}}
                    {{if readingStatuses && readingStatuses.unread_timelines > 0}}
                      <div><i class="fa fa-exclamation-triangle faa-tada animated"></i></div>
                    {{/if}}
                </td>
                <td class="isSpecial" style="white-space:nowrap;">
                    <i style="font-size:1.2em" data-star="{{:id}}" class="fa fa-star{{if isSpecial}} text-primary{{else}}-o text-muted{{/if}}"></i>
                    {/literal}{if can_set_sensor_tag()}{literal}
                    <i style="margin-left:5px" data-important="{{:id}}" class="fa fa-bell{{if ~inArray('important', tags)}} text-primary{{else}}-o text-muted{{/if}}"></i>
                    {/literal}{/if}{literal}
                </td>
                <td><a href="{{:~accessPath("/sensor/posts/")}}{{:id}}">{{:id}}</a></td>
                <td data-preview="{{:id}}" data-href="{{:~accessPath("/sensor/posts/")}}{{:id}}" style="white-space:nowrap">
                    {{if workflowStatus.identifier == 'waiting'}}Da leggere
                    {{else workflowStatus.identifier == 'read'}}In attesa di assegnazione
                    {{else workflowStatus.identifier == 'assigned'}}Assegnato
                    {{else workflowStatus.identifier == 'closed'}}Chiuso
                    {{else workflowStatus.identifier == 'fixed'}}Intervento terminato
                    {{else workflowStatus.identifier == 'reopened'}}Riaperto
                    {{/if}}
                </td>
                <td data-preview="{{:id}}" data-href="{{:~accessPath("/sensor/posts/")}}{{:id}}">
                     {/literal}{if sensor_settings('HighlightSuperUserPosts')}{literal}{{if author.isSuperUser}}<span class="label label-info">{{:~sensorTranslate('internal')}}</span>{{/if}}{/literal}{/if}{literal}
                     <strong>{{:author.name}}{{if reporter.id != author.id}} <span class="text-muted">{{if channel && channel.icon}}<i title="{{:channel.name}}" class="{{:channel.icon}}"></i> {{/if}}{{:reporter.name}}</span>{{/if}}</strong>
                     <p>{{:subject}}</p>
                     <ul class="list-inline">
                        {{if areas.length > 0}}
                            <li><i class="fa fa-map-pin"></i> {{for areas}}{{:name}}{{/for}}</li>
                        {{/if}}
                        {{if categories.length > 0}}
                            <li><i class="fa fa-tags"></i> {{for categories}}{{:name}}{{/for}}</li>
                        {{/if}}
                        {{if geoLocation && geoLocation.address}}
                            <li><i class="fa fa-map-marker"></i> {{:geoLocation.address}}</li>
                        {{/if}}
                    </ul>
                </td>
                <td data-preview="{{:id}}" data-href="{{:~accessPath("/sensor/posts/")}}{{:id}}">
                    <span style="white-space:nowrap">{{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}</span>
                    {{if workflowStatus.identifier != 'closed'}}<br /><span class="label label-{{:expirationInfo.label}}" title="{{:~formatDate(expirationInfo.expirationDateTime, 'DD/MM/YYYY HH:mm')}}">{{:expirationInfo.text}}</span>{{/if}}
                </td>
                <td data-preview="{{:id}}" data-href="{{:~accessPath("/sensor/posts/")}}{{:id}}">
                    <ul class="list-inline">
                        {{for approvers}}
                            <li><img title="{{:name}}" src="/sensor/avatar/{{:id}}" class="img-circle" style="width: 30px; height: 30px; object-fit: cover; margin-right:5px" /></li>
                        {{/for}}
                    </ul>
                </td>
                <td data-preview="{{:id}}" data-href="{{:~accessPath("/sensor/posts/")}}{{:id}}">
                    <ul class="list-inline">
                        {{for owners ~count=owners.length}}
                            <li><img title="{{:name}}" src="/sensor/avatar/{{:id}}" class="img-circle" style="width: 30px; height: 30px; object-fit: cover; margin-right:5px" /></li>
                        {{/for}}
                    </ul>
                </td>
                <td data-preview="{{:id}}" data-href="{{:~accessPath("/sensor/posts/")}}{{:id}}">
                    <ul class="list-inline">
                        {{for observers ~count=owners.length}}
                            <li><img title="{{:name}}" src="/sensor/avatar/{{:id}}" class="img-circle" style="width: 30px; height: 30px; object-fit: cover; margin-right:5px" /></li>
                        {{/for}}
                    </ul>
                </td>
            </tr>
        {{/for}}
	    </tbody>
	</table>
	{{if pageCount > 1}}
	<div class="pagination-container text-center">
        <ul class="pagination">
            <li class="page-item {{if !prevPageQuery}}disabled{{/if}}">
                <a class="page-link prevPage" {{if prevPageQuery}}data-page="{{>prevPage}}"{{/if}} href="#">
                    <span class="text"><i class="fa fa-arrow-left"></i></span>
                </a>
            </li>
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
