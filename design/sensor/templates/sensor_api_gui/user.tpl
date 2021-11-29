<section class="hgroup">
    <a href="#" class="reload pull-right"><i class="fa fa-refresh fa-2x"></i></a>
    <h1>{$user.name|wash()}</h1>
</section>

{ezscript_require(array(
    'jquery.opendataTools.js',
    'jsrender.js',
    'moment-with-locales.min.js'
))}

<div class="row">
    <div class="col-md-12">
        <form id="SearchForm">
            <div class="input-group">
                <input type="text" id="SearchText" class="form-control input-lg" value="" placeholder="Cerca nelle segnalazioni" />
                <span class="input-group-btn">
              <button type="submit" id="SearchButton" class="btn btn-primary btn-lg" title="{'Search'|i18n('design/ezwebin/content/search')}">
                <span class="fa fa-search"></span>
              </button>
            </span>
            </div>
        </form>
        <div data-contents></div>
    </div>
</div>

{include uri='design:sensor_api_gui/dashboard/parts/tpl-dashboard-results.tpl'}
{include uri='design:sensor_api_gui/dashboard/parts/tpl-dashboard-spinner.tpl'}

{def $current_language = ezini('RegionalSettings', 'Locale')}
{def $current_locale = fetch( 'content', 'locale' , hash( 'locale_code', $current_language ))}
{def $moment_language = $current_locale.http_locale_code|explode('-')[0]|downcase()|extract_left( 2 )}

{literal}
<style>
    td.isSpecial i{display: none}
</style>
<script>
    $(document).ready(function () {
        $.views.helpers($.opendataTools.helpers);
        var limitPagination = 15;
        var currentPage = 0;
        var queryPerPage = [];
        var viewContainer = $('[data-contents]');
        var template = $.templates('#tpl-dashboard-results');
        var spinner = $($.templates("#tpl-dashboard-spinner").render({}));

        {/literal}
        $.opendataTools.settings('accessPath', "{''|ezurl(no,full)}");
        $.opendataTools.settings('language', "{$current_language}");
        $.opendataTools.settings('languages', ['{ezini('RegionalSettings','SiteLanguageList')|implode("','")}']);
        $.opendataTools.settings('locale', "{$moment_language}");
        $.opendataTools.settings('endpoint',{ldelim}
            'search': '/api/sensor_gui/posts/search',
            'sensor': '/api/sensor_gui',
        {rdelim});
        {literal}

        var find = function (query, cb, context) {
            var data = {
                executionTimes: false,
                readingStatuses: false,
                capabilities: false,
                currentUserInParticipants: false
            };
            data.q = query;
            $.ajax({
                type: "GET",
                url: $.opendataTools.settings('endpoint').search,
                data: data,
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

        var loadUserPosts = function() {
            var searchText = '';
            var queryString = $('#SearchText').val().replace(/"/g, '').replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");
            if (queryString.length > 0){
                searchText = "(id = '" + queryString + "' or subject = '" + queryString + "' or description = '" + queryString + "') ";
            }
            var baseQuery = searchText+"author_id = '{/literal}{$user.id}{literal}'";

            var paginatedQuery = baseQuery + ' and limit ' + limitPagination + ' offset ' + currentPage*limitPagination + ' sort [published=>desc]';
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
                    queryPerPage[i] = baseQuery + ' and limit ' + limitPagination + ' offset ' + (limitPagination*i);
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
                });
                var renderData = $(template.render(response));
                viewContainer.html(renderData);
                viewContainer.find('.page, .nextPage, .prevPage').on('click', function (e) {
                    currentPage = $(this).data('page');
                    if (currentPage >= 0) loadUserPosts();
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

        $('a.reload').on('click', function (e){
            loadUserPosts();
            e.preventDefault();
        });

        $('#SearchForm').on('submit', function (e){
            loadUserPosts();
            e.preventDefault();
        });

        loadUserPosts();
    });
</script>
{/literal}
