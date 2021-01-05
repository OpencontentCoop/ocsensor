{def $can_behalf = fetch('user','has_access_to',hash('module','sensor','function','behalf'))}
{def $areas = sensor_areas()}
{if is_set( $areas['children'] )}
    {set $areas = $areas['children']}
{/if}

<div id="add-post-gui">
    <div id="sensor_full_map"></div>
    <a id="sensor_hide_map_button" class="visible-xs-block btn btn-default btn-lg" href="#">Nascondi mappa</a>
    <a class="btn btn-default btn-lg visible-xs-block" id="mylocation-mobile-button"
       title="{'Gets your current position if your browser support GeoLocation and you grant this website access to it! Most accurate if you have a built in gps in your Internet device! Also note that you might still have to type in address manually!'|i18n('extension/ezgmaplocation/datatype')}">
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
                            <a href="#step-text" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-text" role="tab" title="Aggiungi testo" style="position: relative">
                                <i class="add-icon fa fa-plus-circle text-primary"></i><i class="fa fa-pencil fa-2x text-muted"></i>
                            </a>
                        </li>
                        {if $can_behalf}
                            <li role="presentation" class="nav-item">
                                <a tabindex="4" href="#step-behalf" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-behalf" role="tab" title="Informazioni sul segnalatore" style="position: relative">
                                    <i class="add-icon fa fa-plus-circle text-primary"></i><i class="fa fa-user fa-2x text-muted"></i>
                                </a>
                            </li>
                        {/if}
                        <li role="presentation" class="nav-item">
                            <a tabindex="5" href="#step-geo" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-geo" role="tab" title="Aggiungi geolocalizzazione" style="position: relative">
                                <i class="add-icon fa fa-plus-circle text-primary"></i><i class="fa fa-map-marker fa-2x text-muted"></i>
                            </a>
                        </li>
                        <li role="presentation" class="nav-item">
                            <a tabindex="8" href="#step-image" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-image" role="tab" title="Aggiungi immagini" style="position: relative">
                                <i class="add-icon fa fa-plus-circle text-primary"></i><i class="fa fa-image fa-2x text-muted"></i>
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
                                        Scegli un punto sulla mappa o digita un indirizzo e clicca sulla lente.<br />
                                        Per inserire la tua posizione corrente clicca sulla bussola.<br />
                                    </p>
                                    <p class="lead visible-xs">Digita un indirizzo e clicca sulla lente o clicca sulla cartina per visualizzare la mappa</p>
                                    {include uri='design:sensor_api_gui/add/geoLocation.tpl'}
                                {if ezini( 'SensorConfig', 'ReadOnlySelectArea', 'ocsensor.ini' )|ne('enabled')}
                                </div>
                                <div class="step-part">
                                    <p class="lead">Seleziona la zona di riferimento</p>
                                {/if}
                                {include uri='design:sensor_api_gui/add/areas.tpl'}
                                </div>
                                <p class="drag-marker-help lead hidden-xs" style="display: none">
                                    Puoi trascinare il marker sulla mappa per selezionare la posizione in maniera più precisa
                                </p>
                            </div>
                        </div>
                        <div class="tab-pane" role="tabpanel" id="step-image">
                            <div class="step-content">
                                <div class="step-part">
                                    <p class="lead hidden-xs">
                                        Puoi aggiungere fino a tre immagini in formato png o jpg<br />
                                        Per rimuovere un'immagine allegata clicca sul cestino
                                    </p>
                                    <div class="row">
                                        <div class="col-xs-3">
                                            {include uri='design:sensor_api_gui/add/images.tpl'}
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="image-placeholder image-empty" data-index="0">
                                                <input type="hidden" name="images[0][filename]" value="">
                                                <input type="hidden" name="images[0][file]" value="">
                                                <i style="display: none" class="fa fa-trash fa-2x"></i>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="image-placeholder image-empty" data-index="1">
                                                <input type="hidden" name="images[1][filename]" value="">
                                                <input type="hidden" name="images[1][file]" value="">
                                                <i style="display: none" class="fa fa-trash fa-2x"></i>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="image-placeholder image-empty" data-index="2">
                                                <input type="hidden" name="images[2][filename]" value="">
                                                <input type="hidden" name="images[2][file]" value="">
                                                <i style="display: none" class="fa fa-trash fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="post-privacy">
                        {if sensor_settings().HidePrivacyChoice}
                            <input type="hidden" name="is_private" value="1" />
                        {else}
                            <div class="row">
                                <div class="col-xs-8 col-md-6">
                                    <p class="lead">Consenti la pubblicazione:</p>
                                </div>
                                <div class="col-xs-2 col-md-2">
                                    <label class="radio" style="font-size: 1.3em;margin-top: 20px">
                                        <input required type="radio" name="is_private" value="" />Sì
                                    </label>
                                </div>
                                <div class="col-xs-2 col-md-1">
                                    <label class="radio" style="font-size: 1.3em;margin-top: 20px">
                                        <input required type="radio" name="is_private" value="1" />No
                                    </label>
                                </div>
                            </div>

                            <p class="is_private" style="display: none"><i class="fa fa-lock"></i> Solo il team di {social_pagedata().logo_title} potrà leggere questa segnalazione</p>
                            {if sensor_is_moderation_enabled()}
                                <p class="is_public" style="display: none"><i class="fa fa-globe"></i> Tutti potranno leggere questa segnalazione quando il team di {social_pagedata().logo_title} la approverà</p>
                            {else}
                                <p class="is_public" style="display: none"><i class="fa fa-globe"></i> Tutti potranno leggere questa segnalazione</p>
                            {/if}

                        {/if}
                    </div>

                    <div class="row post-send">
                        <div class="col-xs-6">
                            <a href="#" class="close-add-post btn btn-default btn-lg">Annulla</a>
                        </div>
                        <div class="col-xs-6 text-right">
                            <a href="#" class="btn btn-default btn-lg next-step" tabindex="11">Avanti</a>
                            <button type="submit" class="btn btn-primary btn-lg" style="display: none" tabindex="11">Invia</button>
                        </div>
                    </div>

                    <div>
                        {if sensor_settings().HidePrivacyChoice}
                            <p><i class="fa fa-lock"></i> Solo il team di {social_pagedata().logo_title} potrà leggere questa segnalazione</p>
                        {/if}
                        <p class="text-muted post-help">
                            {'I testi e le immagini inserite dovranno rispettare le policy stabilite per %open_privacy_url%la privacy%close_privacy_url% e %open_terms_url%i termini di utilizzo%close_terms_url%'|i18n('sensor/add', '', hash(
                            '%open_privacy_url%', concat('<a href="','/sensor/redirect/info,privacy'|ezurl(no,full), '">'),
                            '%close_privacy_url%', '</a>',
                            '%open_terms_url%', concat('<a href="','/sensor/redirect/info,terms'|ezurl(no,full), '">'),
                            '%close_terms_url%', '</a>'
                            ))}
                        </p>
                    </div>

                    <div id="post-spinner" class="text-center" style="display: none">
                        <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
                        <span class="sr-only">Loading...</span>
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
    $(document).ready(function () {ldelim}
        $('#sensor_full_map').sensorAddPost({ldelim}
            'use_smart_gui': true,
            'geocoder': '{$geocoder}',
            'geocoder_params': {$geocoder_params|json_encode()},
            'strict_in_area': {cond(ezini('GeoCoderSettings', 'MarkerMustBeInArea', 'ocsensor.ini')|eq('enabled'), true, false)},
            'debug_bounding_area': {$show_map_debug},
            'debug_meta_info': {$show_map_debug},
            'debug_geocoder': {$show_map_debug},
            'strict_in_area_alert': "{ezini('GeoCoderSettings', 'MarkerOutOfBoundsAlert', 'ocsensor.ini')}",
            'no_suggestion_message': "{ezini('GeoCoderSettings', 'NoSuggestionMessage', 'ocsensor.ini')}",
            {if $nearestService}
            'nearest_service': {ldelim}
                'strict_in_area_alert': "{'La località selezionata non è coperta dal servizio'|i18n('sensor/config')}",
                'no_suggestion_message': "{'Nessun risultato'|i18n('sensor/config')}",
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
            'bounding_area': BoundingArea
        {rdelim});
    {rdelim});
</script>
{undef $can_behalf}