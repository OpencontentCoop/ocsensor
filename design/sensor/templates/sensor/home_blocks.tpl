{ezscript_require(array(
  'jquery.opendataTools.js',
  'jsrender.js', 'jsrender.helpers.js'
))}

<div class="row" data-root data-classes="sensor_block"></div>

{literal}
<script id="tpl-data-spinner" type="text/x-jsrender">
<div class="col-xs-12 spinner text-center">
    <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
</div>
</script>
<script id="tpl-data-results" type="text/x-jsrender">
{{if totalCount > 0}}
    {{for searchHits}}
        <div class="col-md-6">
            <div class="service_teaser vertical wow animated flipInX animated">
                <div class="service_details">
                    <h2 class="section_header skincolored"{{if ~i18n(data, 'color')}} style="color:{{:~i18n(data, 'color')}} !important"{{/if}}>{{:~i18n(data, 'title')}}</h2>
                    {{:~i18n(data, 'intro')}}
                    {{if ~i18n(data, 'button_link')}}
                    <a href="{{:~i18n(data, 'button_link')}}"
                       class="btn btn-lg btn-block btn-primary"{{if ~i18n(data, 'color')}} style="background-color:{{:~i18n(data, 'color')}} !important;border-color:{{:~i18n(data, 'color')}} !important"{{/if}}>{{:~sensorTranslate(~i18n(data, 'button_label'))}}</a>
                    {{/if}}
                </div>
            </div>
        </div>
    {{/for}}
{{/if}}
{{if pageCount > 1}}
<div class="row">
    <div class="col-xs-12">
        <div class="pagination-container text-center" aria-label="{{:~sensorTranslate('Navigation')}}">
            <ul class="pagination">
                <li class="page-item {{if !prevPageQuery}}disabled{{/if}}">
                    <a class="page-link prevPage" {{if prevPageQuery}}data-page="{{>prevPage}}"{{/if}} href="#">
                        <i class="fa fa-arrow-left"></i>
                        <span class="sr-only">{{:~sensorTranslate('Previous page')}}</span>
                    </a>
                </li>
                {{for pages ~current=currentPage}}
                    <li class="page-item{{if ~current == query}} active{{/if}}"><a href="#" class="page-link page" data-page_number="{{:page}}" data-page="{{:query}}"{{if ~current == query}} data-current aria-current="page"{{/if}}>{{:page}}</a></li>
                {{/for}}

                <li class="page-item {{if !nextPageQuery}}disabled{{/if}}">
                    <a class="page-link nextPage" {{if nextPageQuery}}data-page="{{>nextPage}}"{{/if}} href="#">
                        <span class="sr-only">{{:~sensorTranslate('Next page')}}</span>
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
{{/if}}
</script>
<script>
$.opendataTools.settings('endpoint', {
    search: "{/literal}{'/opendata/api/content/search/'|ezurl(no)}{literal}"
});
$(document).ready(function () {
    var resultsContainer = $('[data-root]');
    var subtree = resultsContainer.data('root');
    var classes = resultsContainer.data('classes');
    var currentPage = 0;
    var queryPerPage = [];
    var limitPagination = 50;
    var template = $.templates('#tpl-data-results');
    var spinner = $($.templates("#tpl-data-spinner").render({}));
    var buildQuery = function () {
        var classQuery = '';
        if (classes.length) {
            classQuery = 'classes [' + classes + ']';
        }
        var query = classQuery;
        query += ' sort [priority=>desc,published=>asc]';
        return query;
    };
    var loadContents = function () {
        var baseQuery = buildQuery();
        var paginatedQuery = baseQuery + ' and limit ' + limitPagination + ' offset ' + currentPage * limitPagination;
        resultsContainer.html(spinner);
        $.opendataTools.find(paginatedQuery, function (response) {
            queryPerPage[currentPage] = paginatedQuery;
            response.currentPage = currentPage;
            response.prevPage = currentPage - 1;
            response.nextPage = currentPage + 1;
            var pagination = response.totalCount > 0 ? Math.ceil(response.totalCount / limitPagination) : 0;
            var pages = [];
            var i;
            for (i = 0; i < pagination; i++) {
                queryPerPage[i] = baseQuery + ' and limit ' + limitPagination + ' offset ' + (limitPagination * i);
                pages.push({'query': i, 'page': (i + 1)});
            }
            response.pages = pages;
            response.pageCount = pagination;
            response.prevPageQuery = jQuery.type(queryPerPage[response.prevPage]) === "undefined" ? null : queryPerPage[response.prevPage];
            response.showType = classes.split(',').length > 1;
            var renderData = $(template.render(response));
            resultsContainer.html(renderData);
            resultsContainer.find('.page, .nextPage, .prevPage').on('click', function (e) {
                currentPage = $(this).data('page');
                if (currentPage >= 0) loadContents();
                e.preventDefault();
            });
            var more = $('<li class="page-item"><span class="page-link">...</span></li');
            var displayPages = resultsContainer.find('.page[data-page_number]');
            var currentPageNumber = resultsContainer.find('.page[data-current]').data('page_number');
            var length = 7;
            if (displayPages.length > (length + 2)) {
                if (currentPageNumber <= (length - 1)) {
                    resultsContainer.find('.page[data-page_number="' + length + '"]').parent().after(more.clone());
                    for (i = length; i < pagination; i++) {
                        resultsContainer.find('.page[data-page_number="' + i + '"]').parent().hide();
                    }
                } else if (currentPageNumber >= length) {
                    resultsContainer.find('.page[data-page_number="1"]').parent().after(more.clone());
                    var itemToRemove = (currentPageNumber + 1 - length);
                    for (i = 2; i < pagination; i++) {
                        if (itemToRemove > 0) {
                            resultsContainer.find('.page[data-page_number="' + i + '"]').parent().hide();
                            itemToRemove--;
                        }
                    }
                    if (currentPageNumber < (pagination - 1)) {
                        resultsContainer.find('.page[data-current]').parent().after(more.clone());
                    }
                    for (i = (currentPageNumber + 1); i < pagination; i++) {
                        resultsContainer.find('.page[data-page_number="' + i + '"]').parent().hide();
                    }
                }
            }
        });
    };
    loadContents();
});
</script>
{/literal}
