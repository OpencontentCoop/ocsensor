{def $can_behalf = fetch('user','has_access_to',hash('module','sensor','function','behalf'))}
{def $areas = sensor_areas()}
{if is_set( $areas['children'] )}
    {set $areas = $areas['children']}
{/if}
{def $images_length = sensor_settings().UploadMaxNumberOfImages}
{def $file_length = sensor_settings().UploadMaxNumberOfFiles}

<div id="add-post-gui">
    <div id="sensor_full_map"></div>
    <a id="sensor_hide_map_button" class="visible-xs-block btn btn-default btn-lg" href="#">{sensor_translate('Hide map')}</a>
    <a class="btn btn-default btn-lg visible-xs-block" id="mylocation-mobile-button"
       title="{sensor_translate('Detect my location')}">
        <i class="fa fa-compass"></i>
    </a>
    <form id="edit" class="post-edit col-md-6 col-xs-12">
        <div id="sensor_add_gui">
            <div class="panel panel-default">
                <div class="panel-body">

                    <div id="add-post-errors" class="alert alert-warning" style="display: none">
                        <a href="#" onclick="(function(){ldelim}var elem = document.querySelector('#add-post-errors');elem.parentNode.removeChild(elem);return false;{rdelim})();return false;" style="display:block; margin: 10px 0;float: right">
                            <i class="fa fa-times"></i>
                        </a>
                        <p></p>
                    </div>

                    <ul class="list-inline text-left step-nav" role="tablist">
                        <li role="presentation" class="nav-item active">
                            <a href="#step-text" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-text" role="tab" title="{sensor_translate('Issue info')}" style="position: relative">
                                <i class="add-icon fa fa-plus-circle text-primary"></i><i class="fa fa-pencil fa-2x text-muted"></i>
                            </a>
                        </li>
                        {if $can_behalf}
                            <li role="presentation" class="nav-item">
                                <a tabindex="4" href="#step-behalf" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-behalf" role="tab" title="{sensor_translate('Reporter info')}" style="position: relative">
                                    <i class="add-icon fa fa-plus-circle text-primary"></i><i class="fa fa-user fa-2x text-muted"></i>
                                </a>
                            </li>
                        {/if}
                        <li role="presentation" class="nav-item">
                            <a tabindex="5" href="#step-geo" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-geo" role="tab" title="{sensor_translate('Location info')}" style="position: relative">
                                <i class="add-icon fa fa-plus-circle text-primary"></i><i class="fa fa-map-marker fa-2x text-muted"></i>
                            </a>
                        </li>
                        {if $images_length|gt(0)}
                        <li role="presentation" class="nav-item">
                            <a tabindex="8" href="#step-image" class="btn btn-lg btn-default{if $file_length|eq(0)} is-last-tab last-tab{/if}" data-toggle="tab" aria-controls="step-image" role="tab" title="{sensor_translate('Images')}" style="position: relative">
                                <i class="add-icon fa fa-plus-circle text-primary"></i><i class="fa fa-image fa-2x text-muted"></i>
                            </a>
                        </li>
                        {/if}
                        {if $file_length|gt(0)}
                            <li role="presentation" class="nav-item">
                                <a tabindex="8" href="#step-file" class="btn btn-lg btn-default is-last-tab last-tab" data-toggle="tab" aria-controls="step-file" role="tab" title="{sensor_translate('Files')}" style="position: relative">
                                    <i class="add-icon fa fa-plus-circle text-primary"></i><i class="fa fa-file-o fa-2x text-muted"></i>
                                </a>
                            </li>
                        {/if}
                        <li id="nav-faqs" role="presentation" class="nav-item" style="display: none">
                            <a tabindex="8" href="#step-faq" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-faq" role="tab" title="{sensor_translate('Faq')}" style="position: relative">
                                <i class="fa fa-question-circle fa-2x text-muted"></i>
                            </a>
                        </li>
                    </ul>

                    <div class="post-content tab-content">
                        <div class="tab-pane active" role="tabpanel" id="step-text">
                            {include uri='design:sensor_api_gui/add/type.tpl'}
                            <div class="post-subject">
                                {include uri='design:sensor_api_gui/add/subject.tpl'}
                            </div>
                            <div class="step-content">
                                <div class="step-part">
                                    {include uri='design:sensor_api_gui/add/description.tpl'}
                                </div>
                            </div>
                        </div>
                        {if $can_behalf}
                            <div class="tab-pane" role="tabpanel" id="step-behalf">
                                {include uri='design:sensor_api_gui/add/behalf.tpl'}
                            </div>
                        {/if}
                        <div class="tab-pane" role="tabpanel" id="step-geo">
                            <div class="step-content">
                                <div class="step-part">
                                    <p class="lead hidden-xs">
                                        {sensor_translate('Choose a point on the map or enter an address and click on the lens.')}<br />
                                        {sensor_translate('To enter your current position click on the compass.')}<br />
                                    </p>
                                    <p class="lead visible-xs">{sensor_translate('Type an address and click on the lens or click on the map to view the map')}</p>
                                    {include uri='design:sensor_api_gui/add/geoLocation.tpl'}
                                {if ezini( 'SensorConfig', 'ReadOnlySelectArea', 'ocsensor.ini' )|ne('enabled')}
                                </div>
                                <div class="step-part">
                                    <p class="lead">{sensor_translate('Select the reference area')}</p>
                                {/if}
                                {include uri='design:sensor_api_gui/add/areas.tpl'}
                                </div>
                                <p class="drag-marker-help lead hidden-xs" style="display: none">
                                    {sensor_translate('You can drag the marker on the map to select the location more precisely')}
                                </p>
                            </div>
                        </div>
                        {if $images_length|gt(0)}
                        <div class="tab-pane" role="tabpanel" id="step-image">
                            <div class="step-content">
                                <div class="step-part">
                                    <p class="lead hidden-xs">
                                        {sensor_translate('You can add up to three images in png or jpg format')}<br />
                                        {sensor_translate('To remove an attached image click on the trash can')}
                                    </p>
                                    <div class="row">
                                        <div class="col-xs-3">
                                            {include uri='design:sensor_api_gui/add/images.tpl'}
                                        </div>
                                        {for 0 to sub($images_length, 1) as $counter}
                                        <div class="col-xs-3" style="margin-bottom: 15px;">
                                            <div class="image-placeholder image-empty" data-index="{$counter}">
                                                <input type="hidden" name="images[{$counter}][filename]" value="">
                                                <input type="hidden" name="images[{$counter}][file]" value="">
                                                <i style="display: none" class="fa fa-trash fa-2x"></i>
                                            </div>
                                        </div>
                                        {/for}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/if}
                        {if $file_length|gt(0)}
                            <div class="tab-pane" role="tabpanel" id="step-file">
                                <div class="step-content">
                                    <div class="step-part">
                                        <p class="lead hidden-xs">
                                            {sensor_translate('You can add up to three files in pdf format')}<br />
                                            {sensor_translate('To remove an attached file click on the trash can')}
                                        </p>
                                        <div class="row">
                                            <div class="col-xs-3">
                                                {include uri='design:sensor_api_gui/add/files.tpl'}
                                            </div>
                                            {for 0 to sub($file_length, 1) as $counter}
                                                <div class="col-xs-3" style="margin-bottom: 15px;">
                                                    <div class="image-placeholder file-empty" data-index="{$counter}">
                                                        <input type="hidden" name="files[{$counter}][filename]" value="">
                                                        <input type="hidden" name="files[{$counter}][file]" value="">
                                                        <i style="display: none" class="fa fa-trash fa-2x"></i>
                                                    </div>
                                                </div>
                                            {/for}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                        <div class="tab-pane" role="tabpanel" id="step-faq"></div>

                    </div>

                    {include uri='design:sensor_api_gui/add/categories.tpl'}

                    <div class="post-privacy">
                        {if sensor_settings().HidePrivacyChoice}
                            <input type="hidden" name="is_private" value="1" />
                        {else}
                            <div class="row">
                                <div class="col-xs-8 col-md-6">
                                    <p class="lead">{sensor_translate('Allow publishing:')}</p>
                                </div>
                                <div class="col-xs-2 col-md-2">
                                    <label class="radio" style="font-size: 1.3em;margin-top: 20px">
                                        <input required type="radio" name="is_private" value="" />{sensor_translate('Yes')}
                                    </label>
                                </div>
                                <div class="col-xs-2 col-md-1">
                                    <label class="radio" style="font-size: 1.3em;margin-top: 20px">
                                        <input required type="radio" name="is_private" value="1" />{sensor_translate('No')}
                                    </label>
                                </div>
                            </div>

                            <p class="is_private" style="display: none"><i class="fa fa-lock"></i> {sensor_translate('Only the %site team will be able to read this report', '', hash('%site', social_pagedata().logo_title))}</p>
                            {if sensor_is_moderation_enabled()}
                                <p class="is_public" style="display: none"><i class="fa fa-globe"></i> {sensor_translate('Everyone will be able to read this report when the %site team approves it', '', hash('%site', social_pagedata().logo_title))}</p>
                            {else}
                                <p class="is_public" style="display: none"><i class="fa fa-globe"></i> {sensor_translate('Everyone will be able to read this report')}</p>
                            {/if}

                        {/if}
                    </div>

                    <div class="row post-send">
                        <div class="col-xs-6">
                            <a href="#" class="close-add-post btn btn-default btn-lg">{sensor_translate('Cancel')}</a>
                        </div>
                        <div class="col-xs-6 text-right">
                            <a href="#" class="btn btn-default btn-lg next-step" tabindex="11">{sensor_translate('Next')}</a>
                            <button type="submit" class="btn btn-primary btn-lg" style="display: none" tabindex="11">{sensor_translate('Send')}</button>
                        </div>
                    </div>

                    <div>
                        {if sensor_settings().HidePrivacyChoice}
                            <p><i class="fa fa-lock"></i> {sensor_translate('Only the %site team will be able to read this report', '', hash('%site', social_pagedata().logo_title))}</p>
                        {/if}
                        <p class="text-muted post-help">
                            {sensor_translate('The texts and images inserted must comply with the policies established for %open_privacy_url%privacy%close_privacy_url% and %open_terms_url% the terms of use %close_terms_url%', '', hash(
                            '%open_privacy_url%', concat('<a href="','/sensor/redirect/info,privacy'|ezurl(no,full), '">'),
                            '%close_privacy_url%', '</a>',
                            '%open_terms_url%', concat('<a href="','/sensor/redirect/info,terms'|ezurl(no,full), '">'),
                            '%close_terms_url%', '</a>'
                            ))}
                        </p>
                    </div>

                    <div id="post-spinner" class="text-center" style="display: none">
                        <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
                        <span class="sr-only">{sensor_translate('Loading...')}</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
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
    var PointsOfInterest = null;
    var CenterMap = {if is_set($areas[0].geo.coords[0])}new L.latLng({$areas[0].geo.coords[0]}, {$areas[0].geo.coords[1]}){else}false{/if};
    var BoundingArea = {if is_set($areas[0].bounding_box)}'{$areas[0].bounding_box.geo_json}'{else}false{/if};
    $.opendataTools.settings('language', "{ezini('RegionalSettings', 'Locale')}");
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
        $('#sensor_full_map').sensorAddPost({ldelim}
            'area_cache_prefix': '{openpa_instance_identifier()}',
            'use_smart_gui': true,
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
            {if $can_behalf}
                'default_user_placement': {ezini('UserSettings', 'DefaultUserPlacement')},
            {/if}
            'default_marker': PointsOfInterest,
            'center_map': CenterMap,
            'bounding_area': BoundingArea,
            'additionalWMSLayers': additionalWMSLayers,
            'persistentMetaKeys': ['{ezini('GeoCoderSettings', 'PersistentMetaKeys', 'ocsensor.ini')|implode("','")}']
        {rdelim});
    {rdelim});
</script>
{literal}
<script id="tpl-faq-on-create" type="text/x-jsrender">
{{if totalCount > 0}}
    {{for searchHits}}
        <div class="panel-group" id="accordion-{{:metadata.id}}" role="tablist" aria-multiselectable="true">
          <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading-{{:metadata.id}}">
              <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion-{{:metadata.id}}" href="#collapse-{{:metadata.id}}" aria-expanded="false" aria-controls="collapse-{{:metadata.id}}">
                  {{if ~i18n(data, 'question')}}{{:~i18n(data, 'question')}}{{/if}}
                </a>
              </h4>
            </div>
            <div id="collapse-{{:metadata.id}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{{:metadata.id}}">
              <div class="panel-body">
                {{if ~i18n(data, 'answer')}}{{:~i18n(data, 'answer')}}{{/if}}
                {{if ~i18n(data, 'category')}}<div class="text-right"><small><i class="fa fa-tag"></i> {{for ~i18n(data, 'category')}}{{:~i18n(name)}}{{/for}}</small></div>{{/if}}
              </div>
            </div>
          </div>
        </div>
    {{/for}}
{{/if}}
</script>
{/literal}
{undef $can_behalf}
