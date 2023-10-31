<form id="SearchForm">
    <div class="input-group">
        <input type="text" id="SearchText" class="form-control input-lg" value="" placeholder="{sensor_translate('Find in issues')}" />
        <span class="input-group-btn">
              <button type="submit" id="SearchButton" class="btn btn-primary btn-lg" title="{sensor_translate('Search')}">
                <span class="fa fa-search"></span>
              </button>
            </span>
    </div>
    <div style="margin: 10px 0">
        <div class="row">
            <div class="col-md-3">
                <select name="status"
                        class="select form-control"
                        data-placeholder="{sensor_translate('Status')}">
                    <option></option>
                    {foreach sensor_statuses() as $status}
                        <option value="{$status.identifier|wash()}">{$status.current_translation.name|wash()}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-md-3">
                <select class="select form-control" name="area" data-placeholder="{sensor_translate('Area')}">
                    <option></option>
                    {foreach sensor_areas().children as $item}
                        <option value="{$item.id}" style="padding-left:{$item.level|mul(10)}px;{if $item.level|eq(0)}font-weight: bold;{/if}">{$item.name|wash()}</option>
                        {foreach $item.children as $child}
                            <option data-parent="{$item.id}" value="{$child.id}"
                                    style="padding-left:{$child.level|mul(10)}px;{if $child.level|eq(0)}font-weight: bold;{/if}">{$child.name|wash()}</option>
                        {/foreach}
                    {/foreach}
                </select>
            </div>
            <div class="col-md-3">
                <select class="select form-control" name="type" data-placeholder="{sensor_translate('Type')}">
                    <option></option>
                    {foreach sensor_types() as $item}
                        <option value="{$item.identifier|wash()}">{$item.name|wash()}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-md-3">
                <input type="text"
                       name="published"
                       class="form-control daterange"
                       placeholder="{sensor_translate('Creation date')}"
                       value=""/>
            </div>
        </div>
    </div>
</form>
<div class="" data-contents></div>

{ezscript_require(array(
    'ezjsc::jquery', 'ezjsc::jqueryio', 'ezjsc::jqueryUI',
    'moment-with-locales.min.js',
    'select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js'),
    'daterangepicker.js',
    'jquery.opendataTools.js',
    'jsrender.js', 'jsrender.helpers.js'
))}
{ezcss_require(array('select2.min.css','daterangepicker.css'))}

{literal}
<script id="tpl-tree-option" type="text/x-jsrender">
{{for children}}
    <option value="{{:id}}" style="padding-left:calc(10px*{{:level}});{{if level == 0}}font-weight: bold;{{/if}};" disabled="disabled">{{:name}}</option>
    {{include tmpl="#tpl-tree-option"/}}
{{/for}}
</script>
<script id="tpl-dashboard-spinner" type="text/x-jsrender">
<div class="spinner text-center" style="margin-top:10px">
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
            <li class="page-item {{if !nextPageQuery}}disabled{{/if}}">
                <a class="page-link nextPage" {{if nextPageQuery}}data-page="{{>nextPage}}"{{/if}} href="#">
                    <span class="text"><i class="fa fa-arrow-right"></i></span>
                </a>
            </li>
        </ul>
	</div>
	{{/if}}
	<table class="table table-striped table-hover"{{if pageCount <= 1}} style="margin-top:40px"{{/if}}>
	{{for searchHits}}
        <tr {{if (readingStatuses.unread_comments + readingStatuses.unread_private_messages + readingStatuses.unread_responses) > 0}}class="danger"{{/if}}>
          <td style="vertical-align: middle;white-space: nowrap;" width="1">
            {{if !(privacy.identifier == 'public' && moderation.identifier != 'waiting')}}<p><i class="fa fa-lock"></i>{{/if}}
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
                <span class="label label-{{:typeCss}}">{{:type.name}}</span>
                <span class="label label-{{:statusCss}}">{{:status.name}}</span>
              </li>
            </ul>
            <ul class="list-inline">
              <li><small><strong>{{:~sensorTranslate('Created at')}}</strong> {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}</small></li>
              {{if ~formatDate(modified, 'X') > ~formatDate(published, 'X')}}<li><small><strong>{{:~sensorTranslate('Modified at')}}</strong> {{:~formatDate(modified, 'DD/MM/YYYY HH:mm')}}</small></li>{{/if}}
              {{if categories.length > 0}}
                <li><small><i class="fa fa-tags"></i> {{for categories}}{{:name}}{{/for}}</small></li>
              {{/if}}
              {{if areas.length > 0}}
                <li><small><i class="fa fa-map-pin"></i> {{for areas}}{{:name}}{{/for}}</small></li>
              {{/if}}
            </ul>
            <p>
              {{:subject}}
            </p>
          </td>
          <td class="text-center" style="vertical-align: middle;">
              <p><a href="{{:~accessPath("/sensor/posts/")}}{{:id}}" class="btn btn-info btn-sm">{{:~sensorTranslate('Details')}}</a></p>
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
    var dateRangePickerLocale = {ldelim}
        "format": "DD/MM/YYYY",
        "separator": " - ",
        "applyLabel": $.sensorTranslate.translate('Apply'),
        "cancelLabel": $.sensorTranslate.translate('Cancel'),
        "fromLabel": $.sensorTranslate.translate('From'),
        "toLabel": $.sensorTranslate.translate('To'),
        "customRangeLabel": $.sensorTranslate.translate('Custom'),
        "weekLabel": "W",
        "daysOfWeek": $.sensorTranslate.translate('Su_Mo_Tu_We_Th_Fr_Sa').split('_'),
        "monthNames": $.sensorTranslate.translate('January_February_March_April_May_June_July_August_September_October_November_December').split('_'),
        "firstDay": 1
    {rdelim};
{literal}
    var viewContainer = $('[data-contents]');
    var limitPagination = 15;
    var currentPage = 0;
    var queryPerPage = [];
    var template = $.templates('#tpl-dashboard-results');
    var spinner = $($.templates("#tpl-dashboard-spinner").render({}));
    var form = $('#SearchForm');
    form.find("select").select2({
        allowClear: true,
        templateResult: function (item) {
            var style = item.element ? $(item.element).attr('style') : '';
            return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
        }
    }).on('select2:select', function (e) {
        viewContainer.html(spinner);
        buildView();
    }).on('select2:unselect', function (e) {
        $(this).val('');
        viewContainer.html(spinner);
        buildView();
    });

    form.find('input[name="published"]').daterangepicker({
        startDate: moment(),
        endDate: moment(),
        opens: 'left',
        locale: dateRangePickerLocale,
        ranges: {
            '{/literal}{sensor_translate('Today')}{literal}': [moment(), moment()],
            '{/literal}{sensor_translate('Yesterday')}{literal}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '{/literal}{sensor_translate('Last 7 days')}{literal}': [moment().subtract(6, 'days'), moment()],
            '{/literal}{sensor_translate('Last 30 days')}{literal}': [moment().subtract(29, 'days'), moment()]
        }
    }).on('change', function (e) {
        viewContainer.html(spinner);
        buildView();
    }).val('');

    $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        viewContainer.html(spinner);
        buildView();
    });
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

    var selectArea = form.find('select[name="area"]');
    var selectType = form.find('select[name="type"]');
    var selectStatus = form.find('select[name="status"]');

    var buildView = function() {
        var query = [];
        var searchText = '';
        var queryString = $('#SearchText').val().replace(/"/g, '').replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");
        if (queryString.length > 0){
            query.push("(id = '" + queryString + "' or subject = '" + queryString + "' or description = '" + queryString + "')");
        }
        var searchArea = selectArea.val();
        if (searchArea){
            var searchAreaList = [searchArea];
            selectArea.find('[data-parent="'+searchArea+'"]').each(function () {
                searchAreaList.push($(this).attr('value'));
            })
            query.push("area.id in [" + searchAreaList.join(',') + "]");
        }
        var searchType = selectType.val();
        if (searchType){
            query.push("type in [" + searchType + "]");
        }
        var searchPublished = form.find('[name="published"]');
        if (searchPublished.val().length > 0){
            query.push("published range [" + searchPublished.data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm') + "," + searchPublished.data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm') + "]");
        }

        if (selectStatus.length > 0) {
            var searchStatus = selectStatus.find(':selected').val();
            if (searchStatus) {
                query.push("status in [" + searchStatus + "]");
            }
        }

        var queryAsString = query.length > 0 ? query.join(' and ') + ' and ' : '';
        var paginatedQuery = queryAsString + 'limit ' + limitPagination + ' offset ' + currentPage*limitPagination + ' sort [modified=>desc]';
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

    form.on('submit', function (e){
        viewContainer.html(spinner);
        buildView();
        e.preventDefault();
    });

{/literal}
{rdelim});
</script>
