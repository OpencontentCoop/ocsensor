{ezpagedata_set('areas', $areas)}
{ezpagedata_set('categories', $categories)}
{ezcss_require(array(
    'daterangepicker.css',
    'leaflet/MarkerCluster.css',
    'leaflet/MarkerCluster.Default.css',
    'leaflet.0.7.2.css'
))}
{ezscript_require(array(
    'ezjsc::jquery', 'ezjsc::jqueryio', 'ezjsc::jqueryUI',
    'moment-with-locales.min.js',
    'leaflet.0.7.2.js',
    'leaflet.markercluster.js',
    'Leaflet.MakiMarkers.js',
    'daterangepicker.js',
    'jquery.opendataTools.js',
    'jsrender.js'
))}


<section class="service_teasers">
    <div class="row">
        <div class="col-xs-12" data-contents style="min-height: 50px"></div>
    </div>
</section>

{include uri='design:sensor_api_gui/posts/parts/tpl-posts-results.tpl'}
{include uri='design:sensor_api_gui/posts/parts/tpl-post-popup.tpl'}
{include uri='design:sensor_api_gui/posts/parts/tpl-spinner.tpl'}

{def $current_language = ezini('RegionalSettings', 'Locale')}
{def $current_locale = fetch( 'content', 'locale' , hash( 'locale_code', $current_language ))}
{def $moment_language = $current_locale.http_locale_code|explode('-')[0]|downcase()|extract_left( 2 )}
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
    var centerMap = new L.latLng({$areas.children[0].geo.coords[0]}, {$areas.children[0].geo.coords[1]});
{literal}
    $.views.helpers($.opendataTools.helpers);
    var form = $('#posts-search form');
    var selectCategory = form.find('select[name="category"]');
    var selectArea = form.find('select[name="area"]');
    var selectType = form.find('select[name="type"]');
    var viewContainer = $('[data-contents]');
    var exportUrl = $('#export-url');
    var limitPagination = 15;
    var currentPage = 0;
    var queryPerPage = [];
    var template = $.templates('#tpl-posts-results');
    var spinner = $($.templates("#tpl-spinner").render({}));
    var mapSpinner = $('#map-spinner');
    var hasClickOnMarker = false;
    var templatePopup = $.templates('#tpl-post-popup');
    var currentQueryId = 0;
    var currentGeoRequest;
    var findParams = {
        capabilities: true
    };
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
    $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    var find = function (query, cb, context) {
        var data = $.extend(true, {}, findParams);
        data.q = query;
        currentGeoRequest = $.ajax({
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
    var geoFind = function (query, cb, context) {
        var data = $.extend(true, {}, findParams);
        data.q = query;
        data.format = 'geojson';
        $.ajax({
            type: "GET",
            url: $.opendataTools.settings('endpoint').search,
            data: data,
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function (response,textStatus,jqXHR) {
                if(response.error_message || response.error_code){
                    console.log(response);
                }else {
                    cb.call(context, response);
                }
            },
            error: function (jqXHR) {
                var error = {
                    error_code: jqXHR.status,
                    error_message: jqXHR.statusText
                };
                console.log(jqXHR);
            }
        });
    };

    var geoRecursiveFind = function (queryId, cb, context) {
        var query = buildQueryFilters() + ' sort [published=>desc]';
        var features = [];
        var getSubRequest = function (query) {
            geoFind(query, function (data) {
                if (queryId === currentQueryId) {
                    cb.call(context, data);
                    parseSubResponse(data);
                }
            })
        };
        var parseSubResponse = function (response) {
            if (response.features.length > 0 && queryId === currentQueryId) {
                $.each(response.features, function () {
                    features.push(this);
                });
            }
            if (response.nextPageQuery && queryId === currentQueryId) {
                getSubRequest(response.nextPageQuery);
            } else {
                var featureCollection = {
                    'type': 'FeatureCollection',
                    'features': features
                };
            }
        };
        getSubRequest(query);
    };

    var buildQueryFilters = function () {
        query = [];
        var queryString = form.find('[name="query"]').val().replace(/"/g, '').replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");
        if (queryString.length > 0){
            query.push("(subject = '" + queryString + "' or description = '" + queryString + "')");
        }
        var searchCategory = selectCategory.val();
        if (searchCategory){
            query.push("category.id in [" + searchCategory + "]");
        }
        var searchArea = selectArea.val();
        if (searchArea){
            query.push("area.id in [" + searchArea + "]");
        }
        var searchType = selectType.val();
        if (searchType){
            query.push("type in [" + searchType + "]");
        }
        var searchPublished = form.find('[name="published"]');
        if (searchPublished.val().length > 0){
            query.push("published range [" + searchPublished.data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm') + "," + searchPublished.data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm') + "]");
        }
        return query.length > 0 ? query.join(' and ') + ' and ' : '';
    };

    var resetMarkers = function() {
        currentGeoRequest.abort();
        mapSpinner.stop(true,false).css({'width': '0'}).show();
        markers.clearLayers();
    };

    var buildMarkers = function() {
        currentQueryId++;
        var ajaxTime = new Date().getTime();
        var currentPercentage;
        geoRecursiveFind(currentQueryId, function (response) {
            if (response.features.length > 0) {
                var geoLayer = L.geoJson(response, {
                    pointToLayer: function (feature, latlng) {
                        var customIcon = L.MakiMarkers.icon({icon: "circle", size: "l"});
                        return L.marker(latlng, {icon: customIcon});
                    },
                    onEachFeature: function (feature, layer) {
                        var post = feature.properties;
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
                        var popup = new L.Popup({maxHeight: 360});
                        popup.setContent($(templatePopup.render(post)).html());
                        layer.bindPopup(popup);
                    }
                });
                var totalTime = new Date().getTime() - ajaxTime;
                ajaxTime = new Date().getTime();
                markers.addLayer(geoLayer);
                if (!hasClickOnMarker) {
                    map.fitBounds(markers.getBounds());
                }
                var currentCount = markers.getLayers().length;
                currentPercentage = Math.ceil(100/response.totalCount * currentCount);
            }else{
                currentPercentage = 100;
            }

            mapSpinner.animate({'width': currentPercentage+'%'}, totalTime);
            if (currentPercentage >= 100){
                mapSpinner.fadeOut(1000);
            }
        });
    };

    var buildView = function() {
        var baseQuery = buildQueryFilters();
        var paginatedQuery = baseQuery + ' limit ' + limitPagination + ' offset ' + currentPage*limitPagination + ' sort [published=>desc]';
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
                if (currentPage >= 0) buildView();
                e.preventDefault();
            });
            var more = $('<li class="page-item"><span class="page-link">...</span></li');
            var displayPages = viewContainer.find('.page[data-page_number]');

            var currentPageNumber = viewContainer.find('.page[data-current]').data('page_number');
            var length = 5;
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
        currentPage = 0;
        queryPerPage = [];
        buildView();
        buildMarkers();
    };

    form.find('[type="submit"]').on('click', function(e){
        form.find('[type="reset"]').removeClass('hide');
        currentPage = 0;
        queryPerPage = [];
        resetMarkers();
        buildView();
        buildMarkers();
        e.preventDefault();
    });
    form.find('[type="reset"]').on('click', function(e){
        resetMarkers();
        reset();
        e.preventDefault();
    });

    var tiles = L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 18,attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'});
    var map = L.map('map').addLayer(tiles);
    map.scrollWheelZoom.disable();
    var markers = new L.markerClusterGroup();
    map.setView(centerMap, 13);
    map.addLayer(markers);
    markers.on('clusterclick', function(){hasClickOnMarker = true});

    reset();

{/literal}
{rdelim});
</script>