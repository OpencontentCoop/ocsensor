<div class="" data-contents></div>


{ezscript_require(array(
    'ezjsc::jquery', 'ezjsc::jqueryio', 'ezjsc::jqueryUI',
    'moment-with-locales.min.js',
    'jquery.opendataTools.js',
    'jsrender.js'
))}
{def $current_language = ezini('RegionalSettings', 'Locale')}
{def $current_locale = fetch( 'content', 'locale' , hash( 'locale_code', $current_language ))}
{def $moment_language = $current_locale.http_locale_code|explode('-')[0]|downcase()|extract_left( 2 )}

{literal}
<script id="tpl-tree-option" type="text/x-jsrender">
{{for children}}
    <option value="{{:id}}" style="padding-left:calc(10px*{{:level}});{{if level == 0}}font-weight: bold;{{/if}};" disabled="disabled">{{:name}}</option>
    {{include tmpl="#tpl-tree-option"/}}
{{/for}}
</script>
<script id="tpl-dashboard-spinner" type="text/x-jsrender">
<div class="spinner text-center">
    <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
    <span class="sr-only">Loading...</span>
</div>
</script>
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
                <li><small><strong>{/literal}{"Scadenza"|i18n('sensor/dashboard')}{literal}</strong></small> <span class="label label-{{:expirationInfo.label}}">{{:expirationInfo.text}}</span></li>
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
                  <p><a class="btn btn-warning btn-sm" href="{{:accessPath}}/sensor/edit/{{:id}}" data-post="{{:id}}">{/literal}{'Edit'|i18n( 'design/admin/node/view/full' )}{literal}</a>
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
<script>
$(document).ready(function () {ldelim}
    $.opendataTools.settings('accessPath', "{''|ezurl(no,full)}");
    $.opendataTools.settings('language', "{$current_language}");
    $.opendataTools.settings('languages', ['{ezini('RegionalSettings','SiteLanguageList')|implode("','")}']);
    $.opendataTools.settings('locale', "{$moment_language}");
    $.opendataTools.settings('endpoint',{ldelim}
        'search': '/api/sensor_gui/posts/search',
        'sensor': '/api/sensor_gui',
    {rdelim});
    var settings = {ldelim}
        'currentUserId': {fetch(user,current_user).contentobject_id|int()},
        'areas': '{$areas|wash(javascript)}',
        'categories': '{$categories|wash(javascript)}',
        'settings': '{$settings|wash(javascript)}'
    {rdelim};
{literal}
    $.views.helpers($.opendataTools.helpers);
    var viewContainer = $('[data-contents]');
    var limitPagination = 15;
    var currentPage = 0;
    var queryPerPage = [];
    var template = $.templates('#tpl-dashboard-results');
    var spinner = $($.templates("#tpl-dashboard-spinner").render({}));


    var find = function (query, cb, context) {
        $.ajax({
            type: "GET",
            url: $.opendataTools.settings('endpoint').search,
            data: {
                q: query,
                executionTimes: true,
                readingStatuses: true,
                capabilities: true,
                currentUserInParticipants: true
            },
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function (response,textStatus,jqXHR) {
                if(response.error_message || response.error_code){
                    console.log(response.error_message);
                }else {
                    cb.call(context, response);
                }
            },
            error: function (jqXHR) {
                var error = {
                    error_code: jqXHR.status,
                    error_message: jqXHR.statusText
                };
                console.log(error.error_message);
            }
        });
    };

    var buildView = function() {

        var paginatedQuery = 'limit ' + limitPagination + ' offset ' + currentPage*limitPagination + ' sort [modified=>desc]';
        viewContainer.html(spinner);
        find(paginatedQuery, function (response) {
            queryPerPage[currentPage] = paginatedQuery;
            response.currentPage = currentPage;
            response.prevPage = currentPage - 1;
            response.nextPage = currentPage + 1;
            var pagination = response.totalCount > 0 ? Math.ceil(response.totalCount/limitPagination) : 0;
            var pages = [];
            var i;
            for (i = 0; i < pagination; i++) {
                queryPerPage[i] = 'limit ' + limitPagination + ' offset ' + (limitPagination*i);
                pages.push({'query': i, 'page': (i+1)});
            }
            response.pages = pages;
            response.pageCount = pagination;

            response.prevPageQuery = jQuery.type(queryPerPage[response.prevPage]) === "undefined" ? null : queryPerPage[response.prevPage];

            $.each(response.searchHits, function(){
                var post = this;
                var statusCss = 'info';
                if (post.status.identifier === 'pending') {
                    statusCss = 'danger';
                } else if (post.status.identifier === 'open') {
                    statusCss = 'warning';
                } else if (post.status.identifier === 'close') {
                    statusCss = 'success';
                }
                post.statusCss = statusCss;

                var typeCss = 'info';
                if (post.type.identifier === 'suggerimento') {
                    typeCss = 'warning';
                } else if (post.type.identifier === 'reclamo') {
                    typeCss = 'danger';
                }
                post.typeCss = typeCss;

                var timelineItems = post.timelineItems;
                if (timelineItems.length > 0) {
                    post.lastTimelineItem = timelineItems.pop();
                }else{
                    post.lastTimelineItem = false;
                }
            });
            var renderData = $(template.render(response));
            viewContainer.html(renderData);

            viewContainer.find('.page, .nextPage, .prevPage').on('click', function (e) {
                currentPage = $(this).data('page');
                if (currentPage >= 0) buildView();
                e.preventDefault();
            });
            var more = $('<li class="page-item"><span class="page-link">...</span></li');
            var displayPages = viewContainer.find('.page[data-page_number]');

            var currentPageNumber = viewContainer.find('.page[data-current]').data('page_number');
            var length = 7;
            if (displayPages.length > (length+2)){
                if (currentPageNumber <= (length-1)){
                    viewContainer.find('.page[data-page_number="'+length+'"]').parent().after(more.clone());
                    for (i = length; i < pagination; i++) {
                        viewContainer.find('.page[data-page_number="'+i+'"]').parent().hide();
                    }
                }else if (currentPageNumber >= length ){
                    viewContainer.find('.page[data-page_number="1"]').parent().after(more.clone());
                    var itemToRemove = (currentPageNumber+1-length);
                    for (i = 2; i < pagination; i++) {
                        if (itemToRemove > 0){
                            viewContainer.find('.page[data-page_number="'+i+'"]').parent().hide();
                            itemToRemove--;
                        }
                    }
                    if (currentPageNumber < (pagination-1)){
                        viewContainer.find('.page[data-current]').parent().after(more.clone());
                    }
                    for (i = (currentPageNumber+1); i < pagination; i++) {
                        viewContainer.find('.page[data-page_number="'+i+'"]').parent().hide();
                    }
                }
            }
        });
    };

    viewContainer.html(spinner);
    buildView();

{/literal}
{rdelim});
</script>