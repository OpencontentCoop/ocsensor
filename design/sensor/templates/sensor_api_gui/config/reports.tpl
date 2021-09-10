<div class="tab-pane active" id="reports">
    <div class="row" id="reports-buttons">
        <div class="col-xs-12">
            <div class="pull-left">
                <div class="checkbox">
                    <label>
                        <input id="hide-archive" type="checkbox" checked="checked" /> {'Nascondi archiviati'|i18n('sensor/config')}
                    </label>
                </div>
            </div>
            <div class="pull-right">
                <a class="btn btn-danger" id="addReport" data-add-parent="{$report_parent_node.node_id}" data-add-class="sensor_report" href="#">
                    <i class="fa fa-plus"></i> {'Aggiungi'|i18n('sensor/config')}
                </a>
            </div>
        </div>
    </div>
    <div data-items></div>
</div>

{literal}
<script id="tpl-data-spinner" type="text/x-jsrender">
<div class="col-xs-12 spinner text-center">
    <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
</div>
</script>
<script id="tpl-data-reports" type="text/x-jsrender">
<div class="row">
    {{if totalCount == 0}}
        <div class="col-xs-12 text-center">
            <i class="fa fa-times"></i> {/literal}{'Nessun contenuto'|i18n('sensor')}{literal}
        </div>
    {{else}}
    <div class="col-xs-12">
        <table class="table table-striped">
            <thead>
                <th width="1">#</th>
                <th>Titolo</th>
                <th>Link</th>
                <th>Password</th>
                <th class="text-center">Abilitato</th>
                <th>Staticizzato il</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </thead>
            <tbody>
            {{for searchHits ~baseUrl=baseUrl}}
                <tr>
                    <th>
                        {{:metadata.mainNodeId}}
                    </th>
                    <td>
                        {{:~i18n(data, 'title')}}
                        {{if ~i18n(data, 'override_link_parameters')}}
                            <ul class="list-unstyled" style="font-size: .8em;">
                            {{for ~i18n(data, 'override_link_parameters')}}
                                <li><strong>{{:key}}:</strong> {{:value}}</li>
                            {{/for}}
                            </ul>
                        {{/if}}
                    </td>
                    <td>
                        <a href="{{:~baseUrl}}/{{:metadata.remoteId}}">Link al report</a>
                    </td>
                    <td>
                        {{if ~i18n(data, 'password')}}<code>{{:~i18n(data, 'password')}}</code>{{/if}}
                    </td>
                    <td class="text-center">
                        {{if ~i18n(data, 'enabled')}}<i class="fa fa-check"></i>{{/if}}
                    </td>
                    <td>
                        {{if ~i18n(data, 'static_at')}}{{:~formatDate(~i18n(data, 'static_at'), 'DD/MM/YYYY HH:mm')}}{{/if}}
                    </td>
                    <td width="1">
                        <a href="#" title="Esplora" data-report="{{:metadata.mainNodeId}}"><i class="fa fa-folder-open"></i></a>
                    </td>
                    <td width="1" style="text-align: center;">
                        <form method="post" action="/content/copysubtree/{{:metadata.mainNodeId}}" style="text-align: center;display: inline;">
                            <input type="hidden" name="SelectedNodeID" value="{/literal}{$report_parent_node.node_id}{literal}" />
                            <button class="btn btn-link btn-sm" style="padding:0" type="submit" name="CopyButton"><i class="fa fa-copy"></i></button>
                        </form>
                    </td>
                    <td width="1">
                        {{if metadata.userAccess.canEdit}}
                            <a href="#" title="Modifica" data-edit={{:metadata.id}}><i class="fa fa-pencil"></i></a>
                        {{/if}}
                    </td>
                    <td width="1">
                        {{if metadata.userAccess.canRemove}}
                            <a href="#" title="Elimina" data-remove={{:metadata.id}}><i class="fa fa-trash"></i></a>
                        {{/if}}
                    </td>
                    <td width="1">
                        <a href="#" title="Staticizza" data-make_static="{{:metadata.id}}"><i class="fa fa-cloud-download"></i></a>
                    </td>
                    <td width="1">
                        {{if metadata.userAccess.canEdit}}
                            <a href="#" title="{{if metadata.stateIdentifiers.indexOf('privacy.private') > -1}}Rimuovi dall'archivio{{else}}Archivia{{/if}}" data-change_visibility="{{:metadata.id}}">
                                {{if metadata.stateIdentifiers.indexOf('privacy.private') > -1}}<i class="fa fa-times"></i>{{else}}<i class="fa fa-check"></i>{{/if}}
                            </a>
                        {{/if}}
                    </td>
                </tr>
            {{/for}}
            </tbody>
        </table>
    </div>
    {{/if}}
</div>
{{if pageCount > 1}}
<div class="row">
    <div class="col-xs-12">
        <div class="pagination-container text-center" aria-label="Esempio di navigazione della pagina">
            <ul class="pagination">
                <li class="page-item {{if !prevPageQuery}}disabled{{/if}}">
                    <a class="page-link prevPage" {{if prevPageQuery}}data-page="{{>prevPage}}"{{/if}} href="#">
                        <i class="fa fa-arrow-left"></i>
                        <span class="sr-only">Pagina precedente</span>
                    </a>
                </li>
                {{for pages ~current=currentPage}}
                    <li class="page-item{{if ~current == query}} active{{/if}}"><a href="#" class="page-link page" data-page_number="{{:page}}" data-page="{{:query}}"{{if ~current == query}} data-current aria-current="page"{{/if}}>{{:page}}</a></li>
                {{/for}}
                <li class="page-item {{if !nextPageQuery}}disabled{{/if}}">
                    <a class="page-link nextPage" {{if nextPageQuery}}data-page="{{>nextPage}}"{{/if}} href="#">
                        <span class="sr-only">Pagina successiva</span>
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
{{/if}}
</script>
<script id="tpl-data-report" type="text/x-jsrender">
<div class="row">
    <div class="col-xs-8">
        <span class="h3">Report #{{:reportNodeId}}</span>
    </div>
    <div class="col-xs-4">
        <div class="pull-right">
            <a class="btn btn-danger" id="addReportItem" data-add-parent="{{:reportNodeId}}" data-add-class="sensor_report_item" href="#">
                {/literal}<i class="fa fa-plus"></i> {'Aggiungi'|i18n('sensor/config')}{literal}
            </a>
            <a class="btn btn-info" href="#" id="closeReportItem">
                <i class="fa fa-times"></i> Chiudi
            </a>
        </div>
    </div>
</div>
<div class="row">
    {{if totalCount == 0}}
        <div class="col-xs-12 text-center">
            <i class="fa fa-times"></i> {/literal}{'Nessun contenuto'|i18n('sensor')}{literal}
        </div>
    {{else}}
    <div class="col-xs-12">
        <table class="table table-striped">
            <thead>
                <th width="1">#</th>
                <th>Titolo</th>
                <th>Link</th>
                <th>Priorità</th>
                <th>Static?</th>
                <th></th>
                <th></th>
            </thead>
            <tbody>
            {{for searchHits}}
                <tr>
                    <th>
                        {{:metadata.id}}
                    </th>
                    <td>
                        {{:~i18n(data, 'title')}}
                    </td>
                    <td>
                        {{if ~i18n(data, 'link')}}{{:~i18n(data, 'link')}}{{/if}}
                    </td>
                    <td class="text-center">
                        {{:~i18n(data, 'priority')}}
                    </td>
                    <td class="text-center">
                        {{if ~i18n(data, 'data').length > 0}}<i class="fa fa-check"></i>{{/if}}
                    </td>
                    <td width="1">
                        {{if metadata.userAccess.canEdit}}
                            <a href="#" data-edit={{:metadata.id}}><i class="fa fa-pencil"></i></a>
                        {{/if}}
                    </td>
                    <td width="1">
                        {{if metadata.userAccess.canRemove}}
                            <a href="#" data-remove={{:metadata.id}}><i class="fa fa-trash"></i></a>
                        {{/if}}
                    </td>
                </tr>
            {{/for}}
            </tbody>
        </table>
    </div>
    {{/if}}
</div>
{{if pageCount > 1}}
<div class="row">
    <div class="col-xs-12">
        <div class="pagination-container text-center" aria-label="Esempio di navigazione della pagina">
            <ul class="pagination">
                <li class="page-item {{if !prevPageQuery}}disabled{{/if}}">
                    <a class="page-link prevPage" {{if prevPageQuery}}data-page="{{>prevPage}}"{{/if}} href="#">
                        <i class="fa fa-arrow-left"></i>
                        <span class="sr-only">Pagina precedente</span>
                    </a>
                </li>
                {{for pages ~current=currentPage}}
                    <li class="page-item{{if ~current == query}} active{{/if}}"><a href="#" class="page-link page" data-page_number="{{:page}}" data-page="{{:query}}"{{if ~current == query}} data-current aria-current="page"{{/if}}>{{:page}}</a></li>
                {{/for}}
                <li class="page-item {{if !nextPageQuery}}disabled{{/if}}">
                    <a class="page-link nextPage" {{if nextPageQuery}}data-page="{{>nextPage}}"{{/if}} href="#">
                        <span class="sr-only">Pagina successiva</span>
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
    $.views.helpers($.opendataTools.helpers);
    $(document).ready(function () {
        var wrapper = $('#reports');
        var resultsContainer = wrapper.find('[data-items]');
        var limitPagination = 20;
        var currentPage = 0;
        var currentPageReport = 0;
        var queryPerPage = [];
        var queryPerPageReport = [];
        var reportsTemplate = $.templates('#tpl-data-reports');
        var reportTemplate = $.templates('#tpl-data-report');
        var spinner = $($.templates("#tpl-data-spinner").render({}));
        var reportBaseUrl = {/literal}{'/sensor/report'|ezurl(yes,full)}{literal};
        var hideArchiveSelector = $('#hide-archive');
        var reportsButtons = $('#reports-buttons');

        var buildReportQuery = function (nodeId) {
            var query = '';
            query += 'classes [sensor_report_item] subtree ['+nodeId+'] ';
            query += 'sort [priority=>asc]';
            return query;
        };
        var loadReport = function (nodeId) {
            reportsButtons.hide();
            var baseQuery = buildReportQuery(nodeId);
            var paginatedQuery = baseQuery + ' and limit ' + limitPagination + ' offset ' + currentPageReport * limitPagination;
            resultsContainer.html(spinner);
            $.opendataTools.find(paginatedQuery, function (response) {
                queryPerPageReport[currentPageReport] = paginatedQuery;
                response.reportNodeId = nodeId;
                response.currentPage = currentPageReport;
                response.prevPage = currentPageReport - 1;
                response.nextPage = currentPageReport + 1;
                var pagination = response.totalCount > 0 ? Math.ceil(response.totalCount / limitPagination) : 0;
                var pages = [];
                var i;
                for (i = 0; i < pagination; i++) {
                    queryPerPageReport[i] = baseQuery + ' and limit ' + limitPagination + ' offset ' + (limitPagination * i);
                    pages.push({'query': i, 'page': (i + 1)});
                }
                response.pages = pages;
                response.pageCount = pagination;
                response.prevPageQuery = jQuery.type(queryPerPageReport[response.prevPage]) === "undefined" ? null : queryPerPageReport[response.prevPage];
                var renderData = $(reportTemplate.render(response));
                resultsContainer.html(renderData);
                renderData.find('[data-edit]').on('click', function(e){
                    $('#item').opendataFormEdit({
                        object: $(this).data('edit')
                    },{
                        onBeforeCreate: function(){
                            $('#modal').modal('show');
                        },
                        onSuccess: function () {
                            $('#modal').modal('hide');
                            loadReport(nodeId);
                        }
                    });
                    e.preventDefault();
                });
                renderData.find('[data-remove]').on('click', function(e){
                    $('#item').opendataFormDelete({
                        object: $(this).data('remove')
                    },{
                        onBeforeCreate: function(){
                            $('#modal').modal('show');
                        },
                        onSuccess: function () {
                            $('#modal').modal('hide');
                            loadReport(nodeId);
                        }
                    });
                    e.preventDefault();
                });
                resultsContainer.find('.page, .nextPage, .prevPage').on('click', function (e) {
                    currentPageReport = $(this).data('page');
                    if (currentPageReport >= 0) loadReport(nodeId);
                    $('html, body').stop().animate({
                        scrollTop: form.offset().top
                    }, 1000);
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
                renderData.find('#closeReportItem').on('click', function(e){
                    currentPageReport = 0;
                    loadReports();
                    e.preventDefault();
                });
                renderData.find('#addReportItem').on('click', function(e){
                    $('#item').opendataFormCreate({
                        class: $(this).data('add-class'),
                        parent: $(this).data('add-parent')
                    },{
                        onBeforeCreate: function(){
                            $('#modal').modal('show');
                        },
                        onSuccess: function () {
                            $('#modal').modal('hide');
                            loadReport(nodeId);
                        }
                    });
                    e.preventDefault();
                });
            });
        };

        var buildReportsQuery = function () {
            var query = '';
            query += 'classes [sensor_report] ';
            if (hideArchiveSelector.is(':checked')){
                query += ' and state = \'privacy.public\' ';
            }
            query += 'sort [published=>desc]';
            return query;
        };
        var loadReports = function () {
            reportsButtons.show();
            var baseQuery = buildReportsQuery();
            var paginatedQuery = baseQuery + ' and limit ' + limitPagination + ' offset ' + currentPage * limitPagination;
            resultsContainer.html(spinner);
            $.opendataTools.find(paginatedQuery, function (response) {
                queryPerPage[currentPage] = paginatedQuery;
                response.baseUrl = reportBaseUrl
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
                var renderData = $(reportsTemplate.render(response));
                resultsContainer.html(renderData);
                renderData.find('[data-report]').on('click', function(e){
                    var reportNodeId = $(this).data('report');
                    loadReport(reportNodeId);
                    e.preventDefault();
                });
                renderData.find('[data-edit]').on('click', function(e){
                    $('#item').opendataFormEdit({
                        object: $(this).data('edit')
                    },{
                        onBeforeCreate: function(){
                            $('#modal').modal('show');
                        },
                        onSuccess: function () {
                            $('#modal').modal('hide');
                            loadReports();
                        }
                    });
                    e.preventDefault();
                });
                renderData.find('[data-remove]').on('click', function(e){
                    $('#item').opendataFormDelete({
                        object: $(this).data('remove')
                    },{
                        onBeforeCreate: function(){
                            $('#modal').modal('show');
                        },
                        onSuccess: function () {
                            $('#modal').modal('hide');
                            loadReports();
                        }
                    });
                    e.preventDefault();
                });
                renderData.find('[data-make_static]').on('click', function(e){
                    $(this).find('i').addClass('fa-spin');
                    $id = $(this).data('make_static');
                    $.get('/sensor/config/reports', {make_static: $id}, function (){
                        loadReports();
                    })
                    e.preventDefault();
                });
                renderData.find('[data-change_visibility]').on('click', function(e){
                    $(this).find('i').addClass('fa-spin');
                    $id = $(this).data('change_visibility');
                    $.get('/sensor/config/reports', {change_visibility: $id}, function (){
                        loadReports();
                    })
                    e.preventDefault();
                });
                resultsContainer.find('.page, .nextPage, .prevPage').on('click', function (e) {
                    currentPage = $(this).data('page');
                    if (currentPage >= 0) loadReports();
                    $('html, body').stop().animate({
                        scrollTop: form.offset().top
                    }, 1000);
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

        $('#addReport').on('click', function(e){
            $('#item').opendataFormCreate({
                class: $(this).data('add-class'),
                parent: $(this).data('add-parent')
            },{
                onBeforeCreate: function(){
                    $('#modal').modal('show');
                },
                onSuccess: function () {
                    $('#modal').modal('hide');
                    loadReports();
                }
            });
            e.preventDefault();
        });

        hideArchiveSelector.on('change', function (){
            currentPageReport = 0;
            loadReports();
        });

        loadReports();

    });
</script>
{/literal}
