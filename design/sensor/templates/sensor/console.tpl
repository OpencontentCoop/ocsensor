{ezscript_require(array('query-builder.standalone.min.js'))}
{ezcss_require(array('query-builder.default.min.css'))}



{foreach $queries as $index => $query}
    <div class="widget post-result service_teaser" style="padding:15px">
        <div class="input-group">
            <input type="text" class="form-control" name="query" value="{$query.query|wash()}">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-success" name="RunQuery">
                    <i class="fa fa-search"></i>
                </button>
            </span>
        </div>
        <div class="builder-container" style="margin-top: 20px; display: none">
            <div class="builder" data-builder="filters" style="margin-bottom: 20px"></div>
        </div>
        <div class="results" style="margin-top: 20px">
            <div class="items" id="items-{$index}"></div>
        </div>
    </div>
{/foreach}

{literal}
<script id="tpl-data-spinner" type="text/x-jsrender">
<div class="col-xs-12 spinner text-center" style="margin-top: 20px">
    <i class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></i>
</div>
</script>
<script id="tpl-data-results" type="text/x-jsrender">
<div class="row">
    {{if totalCount == 0}}
        <div class="col-xs-12 text-center">
            <i class="fa fa-times"></i> {{:~sensorTranslate('No content')}}
        </div>
    {{else}}
    <div class="col-xs-12">
        <table class="table table-striped">
            <tbody>
            {{for searchHits}}
                <tr>
                    <td>{{:id}}</td>
                    <td>{{:subject}}</td>
                </tr>
            {{/for}}
            </tbody>
        </table>
    </div>
    {{/if}}
</div>
<div class="row">
    <div class="col-xs-12">
        {{if pageCount > 1}}
        <div class="pagination-container text-center" aria-label="{{:~sensorTranslate('Navigation')}}">
            <ul class="pagination">
                <li class="page-item"><a href="/sensor/export/?source=posts&query={{:baseQuery}}" class="text" style="cursor: pointer;"><i class="fa fa-download"></i> {{:totalCount}} {{:~sensorTranslate('issues')}}</a></li>
            </ul>
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
        {{else totalCount == 1}}
            <div class="pagination-container text-center">
                <ul class="pagination">
                    <li class="page-item"><a href="/sensor/export/?source=posts&query={{:baseQuery}}" class="text" style="cursor: pointer;"><i class="fa fa-download"></i> {{:~sensorTranslate('One issue')}}</a></li>
                </ul>
            </div>
        {{else totalCount > 0}}
            <div class="pagination-container text-center">
                <ul class="pagination">
                    <li class="page-item"><a href="/sensor/export/?source=posts&query={{:baseQuery}}" class="text" style="cursor: pointer;"><i class="fa fa-download"></i> {{:totalCount}} {{:~sensorTranslate('issues')}}</a></li>
                </ul>
            </div>
        {{/if}}
    </div>
</div>
</script>
<script>
    $(document).ready(function () {
        var demo = {
            condition: 'AND',
            rules: [{
                id: 'price',
                operator: 'less',
                value: 10.25
            }, {
                condition: 'OR',
                rules: [{
                    id: 'category',
                    operator: 'equal',
                    value: 2
                }, {
                    id: 'category',
                    operator: 'equal',
                    value: 1
                }]
            }]
        };
        $.opendataTools.settings('endpoint', {search: '/sensor/console/run/'});
        var analyze = function (query, cb, context) {
            $.ajax({
                type: "GET",
                url: '/sensor/console/analyze/',
                data: {q: query},
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                success: function (data, textStatus, jqXHR) {
                    cb.call(context, data);
                },
                error: function (jqXHR) {
                    var error = {
                        error_code: jqXHR.status,
                        error_message: jqXHR.statusText
                    };
                    console.log(error, jqXHR);
                }
            });
        }

        var getRules = function (cb, context) {
            $.ajax({
                type: "GET",
                url: '/sensor/console/rules/',
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                success: function (data, textStatus, jqXHR) {
                    cb.call(context, data);
                },
                error: function (jqXHR) {
                    var error = {
                        error_code: jqXHR.status,
                        error_message: jqXHR.statusText
                    };
                    console.log(error, jqXHR);
                }
            });
        }

        var spinner = $($.templates("#tpl-data-spinner").render({}));
        var template = $.templates('#tpl-data-results');
        var loadContents = function (baseQuery, resultsContainer) {
            var queryPerPage = resultsContainer.data('queryPerPage') || [];
            var currentPage = resultsContainer.data('currentPage') || 0;
            var limitPagination = resultsContainer.data('limit') || 10;
            var paginatedQuery = baseQuery + ' and limit ' + limitPagination + ' offset ' + currentPage * limitPagination;
            resultsContainer.html(spinner);
            $.opendataTools.find(paginatedQuery, function (response) {
                queryPerPage[currentPage] = paginatedQuery;
                response.baseQuery = baseQuery;
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
                var renderData = $(template.render(response));
                resultsContainer.data('currentPage', currentPage)
                resultsContainer.data('queryPerPage', queryPerPage)
                resultsContainer.html(renderData);
                resultsContainer.find('.page, .nextPage, .prevPage').on('click', function (e) {
                    currentPage = $(this).data('page');
                    if (currentPage >= 0){
                        resultsContainer.data('currentPage', currentPage);
                        loadContents(baseQuery, resultsContainer);
                    }
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

        getRules(function (rules) {
            $('.widget').each(function () {
                var widget = $(this);
                var query = widget.find('[name="query"]').val();
                // analyze(query, function () {
                //     widget.find('.builder').each(function () {
                //         var type = $(this).data('builder');
                //         $(this).queryBuilder($.extend({}, {
                //             'filters': rules,
                //             'display_empty_filter': false
                //         }, {
                //             // rules: demo
                //         }));
                //     });
                //     widget.find('.builder-container').show();
                // });

                var loadWidgetContent = function () {
                    var resultsContainer = widget.find('.items');
                    resultsContainer.data('currentPage', 0);
                    resultsContainer.data('queryPerPage', []);
                    loadContents(query, resultsContainer);
                }
                widget.find('button[name="RunQuery"]').on('click', function () {
                    query = widget.find('[name="query"]').val();
                    loadWidgetContent();
                })
            })
        });
    });
</script>
{/literal}
