<div class="row">
    <div class="col-md-9">

        <ul class="nav nav-pills" style="margin-bottom: 10px">

            <li role="presentation">
                <a href="#" data-status="unread">
                    {"Da leggere"|i18n('sensor/dashboard')}
                    <span class="badge"></span>
                </a>
            </li>

            <li role="presentation">
                <a href="#" data-status="processing">
                    {"In corso"|i18n('sensor/dashboard')}
                    <span class="badge"></span>
                </a>
            </li>

            <li role="presentation">
                <a href="#" data-status="closed">
                    {"Chiuse"|i18n('sensor/dashboard')}
                    <span class="badge"></span>
                </a>
            </li>
            <li role="presentation" class="pull-right">
                <a id="export-url" href="{'sensor/dashboard/(export)/'|ezurl(no)}">
                    <small><i class="fa fa-download"></i>
                        {"Esporta"|i18n('sensor/dashboard')}
                    </small>
                </a>
            </li>
        </ul>
        <div class="tab-pane active" data-contents></div>
    </div>

    <div class="col-md-3" id="sidebar">

        <div class="well dashboard-search">
            <form method="get" class="form">
                <div class="form-group">
                    <label for="searchId">{'Cerca per ID'|i18n('sensor/dashboard')}</label>
                    <input type="number" value=""
                           name="id"
                           id="searchId"
                           class="form-control">
                </div>

                <div class="form-group">
                    <label for="searchAuthor">{'Cerca per autore'|i18n('sensor/dashboard')}</label>
                    <input type="text" value=""
                           name="author"
                           id="searchAuthor"
                           class="form-control">
                </div>

                <div class="form-group">
                    <label for="searchSubject">{'Cerca per oggetto'|i18n('sensor/dashboard')}</label>
                    <input type="text" value=""
                           name="subject"
                           id="searchSubject"
                           class="form-control">
                </div>

                <div class="form-group">
                    <label for="searchCategory">{'Cerca per area tematica'|i18n('sensor/post')}</label>
                    <select name="category"
                            class="select form-control"
                            id='searchCategory'>
                        <option></option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="searchArea">{'Cerca per zona'|i18n('sensor/post')}</label>
                    <select name="area"
                            class="select form-control"
                            id='searchArea'>
                        <option></option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="searchOwner">{'Cerca per assegnatario'|i18n('sensor/post')}</label>
                    <select name="owner"
                            class="remote-select form-control"
                            data-type="operators"
                            id='searchOwner'>
                        <option></option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="searchObserver">{'Cerca per osservatore'|i18n('sensor/post')}</label>
                    <select name="observer"
                            class="remote-select form-control"
                            data-type="operators"
                            id='searchObserver'>
                        <option></option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="searchPublished" class="">{'Data creazione'|i18n('sensor/post')}</label>
                    <input type="text"
                           name="published"
                           class="form-control daterange"
                           id="searchPublished"
                           value=""/>
                </div>

                <div class="form-group">
                    <label for="searchExpiry" class="">{"Scadenza"|i18n('sensor/dashboard')}</label>
                    <input type="text"
                           name="expiry"
                           class="form-control daterange"
                           id="searchExpiry"
                           value=""/>
                </div>

                <div class="form-group">
                    <label for="searchPrivacy">{'Cerca per visibilità'|i18n('sensor/post')}</label>
                    <select name="privacy"
                            class="form-control"
                            id='searchPrivacy'>
                        <option></option>
                        <option value="private">{'Privato'|i18n('sensor/post')}</option>
                        <option value="public">{'Pubblico'|i18n('sensor/post')}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="searchModeration">{'Cerca per moderazione'|i18n('sensor/post')}</label>
                    <select name="moderation"
                            class="form-control"
                            id='searchModeration'>
                        <option></option>
                        <option value="skipped">{'Non necessita di moderazione'|i18n('sensor/post')}</option>
                        <option value="waiting">{'In attesa di moderazione'|i18n('sensor/post')}</option>
                        <option value="accepted">{'Moderato'|i18n('sensor/post')}</option>
                    </select>
                </div>

                <button class="btn btn-info" type="submit"><span class="fa fa-search"></span> {'Cerca'|i18n('sensor/post')}</button>
                <button class="btn btn-danger pull-right hide" type="reset"><span class="fa fa-close"></span> {'Annulla'|i18n('sensor/post')}</button>
            </form>
        </div>


    </div>
</div>

{ezcss_require(array(
    'select2.min.css',
    'daterangepicker.css',
    'leaflet.0.7.2.css'
))}
{ezscript_require(array(
    'ezjsc::jquery', 'ezjsc::jqueryio', 'ezjsc::jqueryUI',
    'moment-with-locales.min.js',
    'js.cookie.js',
    'select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js'),
    'leaflet.0.7.2.js',
    'Leaflet.MakiMarkers.js',
    'daterangepicker.js',
    'jquery.opendataTools.js',
    'jsrender.js'
))}

{def $current_language = ezini('RegionalSettings', 'Locale')}
{def $current_locale = fetch( 'content', 'locale' , hash( 'locale_code', $current_language ))}
{def $moment_language = $current_locale.http_locale_code|explode('-')[0]|downcase()|extract_left( 2 )}

{include uri='design:sensor_api_gui/dashboard/parts/tpl-dashboard-results.tpl'}
{include uri='design:sensor_api_gui/dashboard/parts/tpl-dashboard-spinner.tpl'}
{include uri='design:sensor_api_gui/dashboard/parts/tpl-tree-option.tpl'}

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
    var dateRangePickerLocale = {ldelim}
        "format": "{'DD/MM/YYYY'|i18n('sensor/datepicker')}",
        "separator": "{' - '|i18n('sensor/datepicker')}",
        "applyLabel": "{'Applica'|i18n('sensor/datepicker')}",
        "cancelLabel": "{'Annulla'|i18n('sensor/datepicker')}",
        "fromLabel": "{'Da'|i18n('sensor/datepicker')}",
        "toLabel": "{'a'|i18n('sensor/datepicker')}",
        "customRangeLabel": "{'Personalizza'|i18n('sensor/datepicker')}",
        "weekLabel": "{'W'|i18n('sensor/datepicker')}",
        "daysOfWeek": [
            "{'Do'|i18n('sensor/datepicker')}",
            "{'Lu'|i18n('sensor/datepicker')}",
            "{'Ma'|i18n('sensor/datepicker')}",
            "{'Me'|i18n('sensor/datepicker')}",
            "{'Gi'|i18n('sensor/datepicker')}",
            "{'Ve'|i18n('sensor/datepicker')}",
            "{'Sa'|i18n('sensor/datepicker')}"
        ],
        "monthNames": [
            "{'Gennaio'|i18n('sensor/datepicker')}",
            "{'Febbraio'|i18n('sensor/datepicker')}",
            "{'Marzo'|i18n('sensor/datepicker')}",
            "{'Aprile'|i18n('sensor/datepicker')}",
            "{'Maggio'|i18n('sensor/datepicker')}",
            "{'Giugno'|i18n('sensor/datepicker')}",
            "{'Luglio'|i18n('sensor/datepicker')}",
            "{'Agosto'|i18n('sensor/datepicker')}",
            "{'Settembre'|i18n('sensor/datepicker')}",
            "{'Ottobre'|i18n('sensor/datepicker')}",
            "{'Novembre'|i18n('sensor/datepicker')}",
            "{'Dicembre'|i18n('sensor/datepicker')}"
        ],
        "firstDay": {'1'|i18n('sensor/datepicker')}
    {rdelim};
    var settings = {ldelim}
        'currentUserId': {fetch(user,current_user).contentobject_id|int()},
        'areas': '{$areas|wash(javascript)}',
        'categories': '{$categories|wash(javascript)}',
        'settings': '{$settings|wash(javascript)}'
    {rdelim};
{literal}
    $.views.helpers($.opendataTools.helpers);
    var form = $('form');
    var selectCategory = form.find('select[name="category"]');
    var selectArea = form.find('select[name="area"]');
    var toggles = $('[data-status]');
    var toggleBadges = toggles.find('.badge');
    var unreadToggle = $('[data-status="unread"]');
    var processingToggle = $('[data-status="processing"]');
    var closedToggle = $('[data-status="closed"]');
    var viewContainer = $('[data-contents]');
    var exportUrl = $('#export-url');
    var limitPagination = 15;
    var currentPage = 0;
    var queryPerPage = [];
    var template = $.templates('#tpl-dashboard-results');
    var spinner = $($.templates("#tpl-dashboard-spinner").render({}));
    var currentView = false;
    var findParams = {
        executionTimes: true,
        readingStatuses: true,
        capabilities: true,
        currentUserInParticipants: true
    };

    selectCategory.append($.templates('#tpl-tree-option').render(JSON.parse(settings.categories)));
    selectArea.append($.templates('#tpl-tree-option').render(JSON.parse(settings.areas)));
    form.find(".select").select2({
        width: '100%',
        templateResult: function (item) {
            var style = item.element ? $(item.element).attr('style') : '';
            return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
        }
    });
    form.find(".remote-select").each(function () {
        var that = $(this);
        that.select2({
            width: '100%',
            ajax: {
                url: $.opendataTools.settings('endpoint').sensor + '/' + that.data('type'),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        query: params.term,
                        limit: 50
                    };
                },
                processResults: function (data, params) {
                    var results = [];
                    $.each(data.items, function () {
                        var text = this.name;
                        if (this.description) {
                            text += ' (' + this.description + ')';
                        }
                        results.push({
                            id: this.id,
                            text: text
                        });
                    });
                    return {
                        results: results
                    };
                },
                cache: true
            },
            minimumInputLength: 4
        });
    });

    toggles.on('click', function (e) {
        currentPage = 0;
        queryPerPage = [];
        toggleBadges.each(function () {
            $(this).parents('li').removeClass('active');
        });
        $(this).parent().addClass('active');
        buildView($(this).data('status'));
        e.preventDefault();
    });

    form.find('input[name="published"]').daterangepicker({
        startDate: moment(),
        endDate: moment(),
        opens: 'left',
        locale: dateRangePickerLocale,
        ranges: {
            '{/literal}{'Oggi'|i18n('sensor/datepicker')}{literal}': [moment(), moment()],
            '{/literal}{'Ieri'|i18n('sensor/datepicker')}{literal}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '{/literal}{'Ultimi 7 giorni'|i18n('sensor/datepicker')}{literal}': [moment().subtract(6, 'days'), moment()],
            '{/literal}{'Ultimi 30 giorni'|i18n('sensor/datepicker')}{literal}': [moment().subtract(29, 'days'), moment()]
        }
    });

    form.find('input[name="expiry"]').daterangepicker({
        startDate: moment(),
        endDate: moment(),
        opens: 'left',
        locale: dateRangePickerLocale,
        ranges: {
            '{/literal}{'Oggi'|i18n('sensor/datepicker')}{literal}': [moment(), moment()],
            '{/literal}{'Ieri'|i18n('sensor/datepicker')}{literal}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '{/literal}{'Ultimi 7 giorni'|i18n('sensor/datepicker')}{literal}': [moment().subtract(6, 'days'), moment()],
            '{/literal}{'Ultimi 30 giorni'|i18n('sensor/datepicker')}{literal}': [moment().subtract(29, 'days'), moment()],
            '{/literal}{'Prossimi 7 giorni'|i18n('sensor/datepicker')}{literal}': [moment(), moment().add(6, 'days')],
            '{/literal}{'Prossimi 30 giorni'|i18n('sensor/datepicker')}{literal}': [moment(), moment().add(29, 'days')]
        }
    });

    $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    var find = function (query, cb, context) {
        var data = findParams;
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

    var fillExportUrl = function () {
        var params = findParams;
        params.q = buildQueryFilters()+' sort [modified=>desc]';
        var href = exportUrl.attr('href').split('?')[0];
        exportUrl.attr('href', href + '?' + jQuery.param(params));
    };

    var buildQueryFilters = function () {
        query = [];
        var searchId = form.find('#searchId').val();
        if (searchId.length > 0){
            query.push("id = '" + searchId + "'");
        }
        var searchAuthor = form.find('#searchAuthor').val().replace(/"/g, '').replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");;
        if (searchAuthor.length > 0){
            query.push("author_name = '" + searchAuthor + "'");
        }
        var searchSubject = form.find('#searchSubject').val().replace(/"/g, '').replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");
        if (searchSubject.length > 0){
            query.push("subject = '\"" + searchSubject + "\"'");
        }
        var searchCategory = form.find('#searchCategory').find(':selected').val();
        if (searchCategory){
            query.push("category.id in [" + searchCategory + "]");
        }
        var searchArea = form.find('#searchArea').find(':selected').val();
        if (searchArea){
            query.push("area.id in [" + searchArea + "]");
        }
        var searchOwner = form.find('#searchOwner').find(':selected').val();
        if (searchOwner){
            query.push("owner_id_list in [" + searchOwner + "]");
        }
        var searchObserver = form.find('#searchObserver').find(':selected').val();
        if (searchObserver){
            query.push("observer_id_list in [" + searchObserver + "]");
        }
        var searchPrivacy = form.find('#searchPrivacy').find(':selected').val();
        if (searchPrivacy){
            query.push("privacy in [" + searchPrivacy + "]");
        }
        var searchModeration = form.find('#searchModeration').find(':selected').val();
        if (searchModeration){
            query.push("moderation in [" + searchModeration + "]");
        }
        var searchPublished = form.find('#searchPublished');
        if (searchPublished.val().length > 0){
            query.push("published range [" + searchPublished.data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm') + "," + searchPublished.data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm') + "]");
        }
        var searchExpiry = form.find('#searchExpiry');
        if (searchExpiry.val().length > 0){
            query.push("(expiration range [" + searchExpiry.data('daterangepicker').startDate.format('YYYY-MM-DDTHH:mm:ss') + "Z," + searchExpiry.data('daterangepicker').endDate.format('YYYY-MM-DDTHH:mm:ss') + "Z] and workflow_status in [waiting,read,assigned,fixed])");
        }

        return query.length > 0 ? query.join(' and ') + ' and ' : '';
    };

    var buildDashboard = function() {
        selectCategory.find('option').attr('disabled', 'disabled').trigger('change');
        selectArea.find('option').attr('disabled', 'disabled').trigger('change');
        toggleBadges.each(function () {
            $(this).html('').parents('li').removeClass('active');
        });
        fillExportUrl();
        find(buildQueryFilters()+' facets [workflow_status,category.id,area.id] limit 1', function (response) {
            $.each(response.facets, function () {
                var facet = this;
                if (facet.name === 'category.id') {
                    $.each(facet.data, function (id, count) {
                        selectCategory.find('option[value="' + id + '"]').removeAttr('disabled');
                    });
                    selectCategory.trigger('change');
                } else if (facet.name === 'area.id') {
                    $.each(facet.data, function (id, count) {
                        selectArea.find('option[value="' + id + '"]').removeAttr('disabled');
                    });
                    selectArea.trigger('change');
                } else if (facet.name === 'workflow_status') {
                    toggleBadges.html('0');
                    var processingCount = 0;
                    $.each(facet.data, function (id, count) {
                        if (id === 'waiting') {
                            unreadToggle.find('.badge').html(count)
                        }else if (id === 'closed') {
                            closedToggle.find('.badge').html(count)
                        }else{
                            processingCount += count
                        }
                    });
                    processingToggle.find('.badge').html(processingCount)
                }
            });
            var firstView = false;
            toggleBadges.each(function () {
                if (parseInt($(this).html()) > 0){
                    if (firstView === false) {
                        firstView = $(this);
                    }
                }
            });

            if (currentView && $('[data-status="'+currentView+'"] .badge').text() !== '0'){
                $('[data-status="'+currentView+'"]').parent().addClass('active');
                buildView(currentView);
            }else if (firstView.length > 0){
                firstView.parents('li').addClass('active');
                buildView(firstView.parent().data('status'));
            }else{
                viewContainer.html('');
            }
        });
    };

    var buildView = function(viewIdentifier) {
        currentView = viewIdentifier;
        var baseQuery = buildQueryFilters();
        if (viewIdentifier === 'unread'){
            baseQuery += 'workflow_status in [waiting]';
        }else if (viewIdentifier === 'closed'){
            baseQuery += 'workflow_status in [closed]';
        }else if (viewIdentifier === 'processing'){
            baseQuery += 'workflow_status in [read,assigned,fixed]';
        }

        var paginatedQuery = baseQuery + ' and limit ' + limitPagination + ' offset ' + currentPage*limitPagination + ' sort [modified=>desc]';
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
                if (currentPage >= 0) buildView(viewIdentifier);
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

    var reset = function(){
        form[0].reset();
        form.find('.select, .remote-select').val(null).trigger('change');
        form.find('[type="reset"]').addClass('hide');
        viewContainer.html(spinner);
        buildDashboard();
    };

    form.find('[type="submit"]').on('click', function(e){
        form.find('[type="reset"]').removeClass('hide');
        viewContainer.html(spinner);
        buildDashboard();
        e.preventDefault();
    });
    form.find('[type="reset"]').on('click', function(e){
        reset();
        e.preventDefault();
    });

    reset();

{/literal}
{rdelim});
</script>