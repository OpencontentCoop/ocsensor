<!doctype html>
<html class="sensor-add-post no-js" lang="en">
{def $social_pagedata = social_pagedata()}
{include uri='design:page_head.tpl'}
{include uri='design:page_head_google_tag_manager.tpl'}
{ezcss_require(array(
    'leaflet.0.7.2.css',
    'Control.Loading.css',
    'sensor_add_post.css'
))}
{ezscript_require(array(
    'leaflet.0.7.2.js',
    'ezjsc::jquery',
    'leaflet.activearea.js',
    'Leaflet.MakiMarkers.js',
    'Control.Geocoder.js',
    'Control.Loading.js',
    'jquery.ocdrawmap.js',
    'wise-leaflet-pip.js',
    'turf.min.js',
    'jquery.sensor_add_post.js'
))}
<body class="sensor-add-post">
{include uri='design:page_body_google_tag_manager.tpl'}
{$module_result.content}

{def $geocoder = ezini('GeoCoderSettings', 'GeocoderHandler', 'ocsensor.ini')}
{def $geocoder_params = false()}
{if $geocoder|eq('Google')}
    {set $geocoder_params = ezini('GeoCoderSettings', 'GoogleApiKey', 'ocsensor.ini')}
{elseif $geocoder|eq('Google')}
    {set $geocoder_params = ezini('GeoCoderSettings', 'BingApiKey', 'ocsensor.ini')}
{elseif and($geocoder|eq('NominatimDetailed'), ezini_hasvariable('GeoCoderSettings', 'NominatimDetailedDefaults', 'ocsensor.ini'))}
    {set $geocoder_params = hash('geocodingQueryParams', ezini('GeoCoderSettings', 'NominatimDetailedDefaults', 'ocsensor.ini'))}
{elseif and($geocoder|eq('Geoserver'), ezini_hasvariable('GeoCoderSettings', 'GeoserverParams', 'ocsensor.ini'))}
    {set $geocoder_params = ezini('GeoCoderSettings', 'GeoserverParams', 'ocsensor.ini')}
{/if}
{def $nearestService = cond(ezini('GeoCoderSettings', 'NearestService', 'ocsensor.ini')|eq('enabled'), true(), false())}
{def $show_map_debug = cond(and(ezhttp_hasvariable('debug', 'get'), fetch('user','has_access_to',hash('module','sensor','function','config'))), 'true', 'false')}
<script>
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
    $(document).ready(function () {ldelim}
        $(document).on('click', '#sensor_show_map_button', function () {ldelim}
            $(window).scrollTop(0);
            $('#sensor_hide_map_button, #sensor_full_map, #mylocation-mobile-button').addClass('zindexize');
            $('body').addClass('noscroll');
        {rdelim});
        $(document).on('click', '#sensor_hide_map_button', function () {ldelim}
            $('#sensor_hide_map_button, #sensor_full_map, #mylocation-mobile-button').removeClass('zindexize');
            $('body').removeClass('noscroll');
        {rdelim});
        $('#sensor_full_map').sensorAddPost({ldelim}
            'geocoder': '{$geocoder}',
            'geocoder_params': {$geocoder_params|json_encode()},
            'strict_in_area': {cond(ezini('GeoCoderSettings', 'MarkerMustBeInArea', 'ocsensor.ini')|eq('enabled'), true, false)},
            'debug_bounding_area': {$show_map_debug},
            'debug_meta_info': {$show_map_debug},
            'debug_geocoder': {$show_map_debug},
            'strict_in_area_alert': "{sensor_translate(ezini('GeoCoderSettings', 'MarkerOutOfBoundsAlert', 'ocsensor.ini'))}",
            'no_suggestion_message': "{ezini('GeoCoderSettings', 'NoSuggestionMessage', 'ocsensor.ini')}",
            {if $nearestService}
            'nearest_service': {ldelim}
                'strict_in_area_alert': "{sensor_translate('The selected location is not covered by the service ')}",
                'no_suggestion_message': "{sensor_translate('No results')}",
                'debug': {cond(or($show_map_debug|eq('true'), ezini('GeoCoderSettings', 'NearestServiceDebug', 'ocsensor.ini')|eq('enabled')), 'true', 'false')},
                'url': '{ezini('GeoCoderSettings', 'NearestServiceUrl', 'ocsensor.ini')}',
                'typeName': '{ezini('GeoCoderSettings', 'NearestServiceTypeName', 'ocsensor.ini')}',
                'maxFeatures': '{ezini('GeoCoderSettings', 'NearestServiceMaxFeatures', 'ocsensor.ini')}',
                'srsName': '{ezini('GeoCoderSettings', 'NearestServiceSrsName', 'ocsensor.ini')}',
                'geometryName': '{ezini('GeoCoderSettings', 'NearestServiceGeometryName', 'ocsensor.ini')}'
            {rdelim},
            {/if}
            'default_marker': PointsOfInterest,
            'center_map': CenterMap,
            'bounding_area': BoundingArea,
            'additionalWMSLayers': additionalWMSLayers,
            'persistentMetaKeys': ['{ezini('GeoCoderSettings', 'PersistentMetaKeys', 'ocsensor.ini')|implode("','")}']
        {rdelim});
    {rdelim});
</script>


<!--DEBUG_REPORT-->
</body>
</html>
