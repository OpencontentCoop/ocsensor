{ezpagedata_set('sensor_post_container', 1)}

{ezcss_require(array(
    'daterangepicker.css',
<<<<<<< HEAD
    'select2.min.css'
=======
    'select2.min.css',
    'leaflet/MarkerCluster.css',
    'leaflet/MarkerCluster.Default.css',
    'leaflet.0.7.2.css',
    'plugins/blueimp/blueimp-gallery.css',
    'jquery.fileupload.css'
>>>>>>> master
))}
{ezscript_require(array(
    'ezjsc::jquery', 'ezjsc::jqueryio', 'ezjsc::jqueryUI',
    'js.cookie.js',
    'moment-with-locales.min.js',
    'plugins/blueimp/jquery.blueimp-gallery.min.js',
    'select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js'),
<<<<<<< HEAD
=======
    'jquery.fileupload.js',
    'leaflet.0.7.2.js',
    'leaflet.markercluster.js',
    'Leaflet.MakiMarkers.js',
>>>>>>> master
    'daterangepicker.js',
    'jquery.opendataTools.js',
    'jsrender.js', 'jsrender.helpers.js',
    'jquery.sensorpost.js'
))}


<section id="post-list" class="service_teasers">
    <div class="row">
        <div class="col-xs-12" data-contents style="min-height: 50px"></div>
    </div>
</section>

<div id="preview" style="display: none">
    <div id="post-preview" class="post-gui" style="position: relative;min-height: 400px;"></div>
    {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post.tpl'}
    {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-title.tpl'}
    {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-detail.tpl'}
    {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-messages.tpl'}
    {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-sidebar.tpl'}
    {include uri='design:sensor_api_gui/posts/tpl-alerts.tpl'}
    {include uri='design:sensor_api_gui/posts/tpl-spinner.tpl'}
    {include uri='design:sensor_api_gui/posts/tpl-post-gallery.tpl'}
</div>

{include uri='design:sensor_api_gui/posts/tpl-posts-results.tpl'}
{include uri='design:sensor_api_gui/posts/tpl-post-popup.tpl'}

{literal}
<script id="tpl-posts-spinner" type="text/x-jsrender">
<div class="spinner text-center" style="margin-top:20px">
    <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
    <span class="sr-only">Loading...</span>
</div>
</script>
<script id="tpl-post-tree-option" type="text/x-jsrender">
{{for children}}
    <option value="{{:id}}" style="padding-left:calc(10px*{{:level}});">{{:name}}</option>
    {{include tmpl="#tpl-post-tree-option"/}}
{{/for}}
</script>
{/literal}
<script>
$(document).ready(function () {ldelim}
    $.opendataTools.settings('endpoint',{ldelim}
        'search': '/api/sensor_gui/posts/search',
        'sensor': '/api/sensor_gui',
    {rdelim});
    $.opendataTools.settings('canReadUsers', {cond(fetch('user', 'has_access_to', hash('module','sensor','function','user_list')), 'true', 'false')});

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

    var centerMap = {if is_set($areas.children[0].geo.coords[0])}{*
        *}new L.latLng({$areas.children[0].geo.coords[0]}, {$areas.children[0].geo.coords[1]}){*
    *}{elseif is_set($areas.children[0].bounding_box)}{*
        *}'{$areas.children[0].bounding_box.geo_json}'{*
    *}{else}{*
       *}false{*
    *}{/if};
    var additionalWMSLayers = [];
    {foreach sensor_additional_map_layers() as $layer}
    additionalWMSLayers.push({ldelim}
        baseUrl: '{$layer.baseUrl}',
        version: '{$layer.version}',
        layers: '{$layer.layers}',
        format: '{$layer.format}',
        transparent: {cond($layer.transparent, 'true', 'false')},
        attribution: '{$layer.attribution}'
    {rdelim});
    {/foreach}
    var operators = '{$operators|wash(javascript)}';
    var groups = '{$grouped_groups|wash(javascript)}';
{literal}
    var postList = $('#post-list');
    var postGui = $('#preview');
    var postWindow = $('#post-preview');
    var postSearch = $('#posts-search');
    var postMap = $('.full_page_photo');
    var currentPageLink = $('[data-location="sensor-posts"]');
    var sensorPostViewer = postWindow.sensorPost({
        'apiEndPoint': '/api/sensor_gui',
        'sensorPostDefinition': '{/literal}{sensor_post_class()|json_encode()|wash(javascript)}{literal}',
        'currentUserId': {/literal}{fetch(user,current_user).contentobject_id|int()}{literal},
        'areas': '{/literal}{sensor_areas()|json_encode()|wash(javascript)}{literal}',
        'categories': '{/literal}{sensor_categories()|json_encode()|wash(javascript)}{literal}',
        'operators': '{/literal}{sensor_operators()|json_encode()|wash(javascript)}{literal}',
        'groups': '{/literal}{sensor_groups()|json_encode()|wash(javascript)}{literal}',
        'settings': '{/literal}{sensor_settings()|json_encode()|wash(javascript)}{literal}',
        'spinnerTpl': '#tpl-spinner',
        'postTpl': '#tpl-post',
        'alertsEndPoint': '{/literal}{'social_user/alert'|ezurl(no)}{literal}'
    }).data('plugin_sensorPost');
    var onOpenPost = function (){
        postMap.hide();
        postSearch.hide();
        postList.hide();
        postGui.show();
    };
    var onClosePost = function(){
        postMap.show();
        map.invalidateSize(false);
        postSearch.show();
        postList.show();
        postGui.hide();
        postWindow.html('');
    };
    var form = $('#posts-search form');
    var selectCategory = form.find('select[name="category"]');
    var selectArea = form.find('select[name="area"]');
    var selectType = form.find('select[name="type"]');
    var selectOwner = form.find('select[name="owner"]').append($.templates('#tpl-post-tree-option').render(JSON.parse(operators)));
    var selectGroup = form.find('select[name="owner_group"]').append($.templates('#tpl-post-tree-option').render(JSON.parse(groups)));
    var selectObserver = form.find('select[name="observer"]').append($.templates('#tpl-post-tree-option').render(JSON.parse(operators)));
    var selectStatus = form.find('select[name="status"]');
    var selectUserGroup = form.find('select[name="usergroup"]');
    form.find("select").select2({
        allowClear: true,
        templateResult: function (item) {
            var style = item.element ? $(item.element).attr('style') : '';
            return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
        }
    });
    var viewContainer = $('[data-contents]');
    var exportUrl = $('#export-url');
    var limitPagination = 10;
    var currentPage = 0;
    var queryPerPage = [];
    var template = $.templates('#tpl-posts-results');
    var spinner = $($.templates("#tpl-posts-spinner").render({}));
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
            '{/literal}{sensor_translate('Today')}{literal}': [moment(), moment()],
            '{/literal}{sensor_translate('Yesterday')}{literal}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '{/literal}{sensor_translate('Last 7 days')}{literal}': [moment().subtract(6, 'days'), moment()],
            '{/literal}{sensor_translate('Last 30 days')}{literal}': [moment().subtract(29, 'days'), moment()]
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
        var query = buildQueryFilters(true) + ' sort [published=>desc]';
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
    var buildQueryFilters = function (onlyActive) {
        var queryData = [];
        query = [];
        var queryString = form.find('[name="query"]').val().replace(/"/g, '').replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");
        if (queryString.length > 0){
            query.push("(id = '" + queryString + "' or subject = '" + queryString + "' or description = '" + queryString + "' or raw[ezf_df_text] = '" + queryString + "')");
            queryData.push({name: 'query', value: form.find('[name="query"]').val()});
        }
        var searchCategory = selectCategory.val();
        if (searchCategory && searchCategory.length > 0){
            var searchCategoryList = [];
            $.each(searchCategory, function () {
                searchCategoryList.push(this);
                selectCategory.find('[data-parent="'+this+'"]').each(function () {
                    searchCategoryList.push($(this).attr('value'));
                })
            })
            if (searchCategoryList.length > 0) {
                query.push("category.id in [" + jQuery.unique(searchCategoryList).join(',') + "]");
                queryData.push({name: 'category', value: jQuery.unique(searchCategoryList)});
            }
        }
        var searchArea = selectArea.val();
        if (searchArea){
            var searchAreaList = [searchArea];
            selectArea.find('[data-parent="'+searchArea+'"]').each(function () {
                searchAreaList.push($(this).attr('value'));
            })
            query.push("area.id in [" + searchAreaList.join(',') + "]");
            queryData.push({name: 'area', value: jQuery.unique(searchAreaList)});
        }
        var searchType = selectType.val();
        if (searchType){
            query.push("type in [" + searchType + "]");
            queryData.push({name: 'type', value: searchType});
        }
        var searchPublished = form.find('[name="published"]');
        if (searchPublished.val().length > 0){
            query.push("published range [" + searchPublished.data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm') + "," + searchPublished.data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm') + "]");
        }

        if (selectOwner.length > 0) {
            var searchOwner = selectOwner.find(':selected').val();
            if (searchOwner) {
                query.push("last_owner_user_id in [" + searchOwner + "]");
            }
        }
        if (selectGroup.length > 0) {
            var searchGroup = selectGroup.find(':selected').val();
            if (searchGroup) {
                query.push("last_owner_group_id in [" + searchGroup + "]");
            }
        }
        if (selectObserver.length > 0) {
            var searchObserver = selectObserver.find(':selected').val();
            if (searchObserver) {
                query.push("observer_id_list in [" + searchObserver + "]");
            }
        }
        if (form.find('input[name="author"]').length > 0) {
            var searchAuthor = form.find('input[name="author"]').val().replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");
            if (searchAuthor.length > 0) {
                query.push("author_name = '" + searchAuthor + "'");
            }
        }
        if (selectUserGroup.length > 0) {
            var searchUserGroup = selectUserGroup.find(':selected').val();
            if (searchUserGroup) {
                if(searchUserGroup === '0'){
                    searchUserGroup = "'0'";
                }
                query.push("raw[sensor_author_group_list_lk] in [" + searchUserGroup + "]");
            }
        }

        if (selectStatus.length > 0) {
            var searchStatus = selectStatus.find(':selected').val();
            if (searchStatus) {
                query.push("status in [" + searchStatus + "]");
            }else if (onlyActive) {
                query.push("status in [open,pending]");
            }
        }else if (onlyActive) {
            query.push("status in [open,pending]");
        }

        return query.length > 0 ? query.join(' and ') + ' and ' : '';
    };
    var resetMarkers = function() {
        if (currentGeoRequest){
            currentGeoRequest.abort();
        }
        mapSpinner.stop(true,false).css({'width': '0'}).show();
        markers.clearLayers();
    };
    var buildMarkers = function() {
        currentQueryId++;
        var ajaxTime = new Date().getTime();
        var currentPercentage;
        map.loadingControl.addLoader('buildMarkers');
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
                if (!response.nextPageQuery){
                    currentPercentage = 100;
                }else {
                    currentPercentage = Math.ceil(100 / response.totalCount * currentCount);
                }
            }else{
                currentPercentage = 100;
            }

            mapSpinner.animate({'width': currentPercentage+'%'}, totalTime);
            if (currentPercentage >= 100){
                mapSpinner.fadeOut(1000);
                map.loadingControl.removeLoader('buildMarkers');
            }
        });
    };
    var buildView = function(callback, context) {
        var baseQuery = buildQueryFilters();
        var paginatedQuery = baseQuery + ' limit ' + limitPagination + ' offset ' + currentPage*limitPagination + ' sort [published=>desc]';
        viewContainer.html(spinner);
        find(paginatedQuery, function (response) {
            queryPerPage[currentPage] = paginatedQuery;
            response.query = baseQuery + ' sort [published=>desc]';
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
                post.canReadUsers = $.opendataTools.settings('canReadUsers');
            });
            var renderData = $(template.render(response));
            renderData.find('[data-preview]').on('click', function (e){
                var postId = $(this).data('preview');
                sensorPostViewer.openPost(postId, onOpenPost);
                e.preventDefault();
            });
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
            if ($.isFunction(callback)){
                callback.call(context);
            }
        });
    };
    var reset = function(){
        form[0].reset();
        form.find('.select, .remote-select').val(null).trigger('change');
        form.find('[type="reset"]').addClass('hide');
        currentPage = 0;
        queryPerPage = [];
        buildView(function (){
            resetMarkers();
            buildMarkers();
        });
    };
    form.find('[type="submit"]').on('click', function(e){
        form.find('[type="reset"]').removeClass('hide');
        currentPage = 0;
        queryPerPage = [];
        buildView(function (){
            resetMarkers();
            buildMarkers();
        });
        e.preventDefault();
    });
    form.find('[type="reset"]').on('click', function(e){
        reset();
        e.preventDefault();
    });
    var map = L.map('map', {loadingControl: true});
    var osmLayer = L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    var baseLayers = [];
    baseLayers[$.sensorTranslate.translate('Map')] = osmLayer;
    baseLayers[$.sensorTranslate.translate('Satellite')] =  L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    });
    var mapLayers = [];
    if (additionalWMSLayers.length > 0) {
        $.each(additionalWMSLayers, function(){
            mapLayers[$.sensorTranslate.translate(this.attribution)] = L.tileLayer.wms(this.baseUrl, {
                layers: this.layers,
                version: this.version,
                format: this.format,
                transparent: this.transparent,
                attribution: this.attribution
            });
        });
    }
    L.control.layers(baseLayers, mapLayers, {'position': 'topleft'}).addTo(map);
    map.scrollWheelZoom.disable();
    var markers = new L.markerClusterGroup();
    if (typeof centerMap === 'string') {
        try{
            var centerMapLayer = L.geoJson(JSON.parse(centerMap));
            map.fitBounds(centerMapLayer.getBounds());
        }catch(err) {
            console.log(err.message);
        }
    }else if (typeof centerMap === 'object') {
        map.setView(centerMap, 13);
    }
    map.addLayer(markers);
    markers.on('clusterclick', function(){hasClickOnMarker = true});
    reset();

    sensorPostViewer.initNavigation(currentPageLink, onOpenPost, onClosePost, reset);

{/literal}
{rdelim});
</script>
