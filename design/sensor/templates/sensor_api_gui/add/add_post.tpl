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
                    <a href="#" class="close-add-post close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></a>
                    <div class="post-subject">
                        {include uri='design:sensor_api_gui/add/subject.tpl'}
                    </div>

                    <div class="post-content tab-content">
                        <div class="tab-pane active" role="tabpanel" id="step-text">
                            <div class="step-content">
                                <div class="step-part">
                                    {include uri='design:sensor_api_gui/add/type.tpl'}
                                </div>
                                <div class="step-part">
                                    {include uri='design:sensor_api_gui/add/description.tpl'}
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" role="tabpanel" id="step-geo">
                            <div class="step-content">
                                <div class="step-part">
                                    <p class="lead hidden-xs">
                                        Digita un indirizzo e clicca sulla lente<br />
                                        Oppure clicca sulla mappa e trascina il marker
                                    </p>
                                    <p class="lead visible-xs">Digita un indirizzo e clicca sulla lente o clicca sulla cartina per visualizzare la mappa</p>
                                    {include uri='design:sensor_api_gui/add/geoLocation.tpl'}
                                </div>
                                <div class="step-part">
                                    {if ezini( 'SensorConfig', 'ReadOnlySelectArea', 'ocsensor.ini' )|ne('enabled')}
                                    <p class="lead">Seleziona la zona di riferimento</p>
                                    {/if}
                                    {include uri='design:sensor_api_gui/add/areas.tpl'}
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted post-help">
                        Puoi aggiungere anche una geolocalizzazione e fino a tre immagini
                    </p>
                    <ul class="list-inline text-left step-nav" role="tablist">
                        <li role="presentation" class="nav-item active">
                            <a href="#step-text" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-text" role="tab" title="Aggiungi testo" style="position: relative">
                                <i class="add-icon fa fa-plus-circle text-danger"></i><i class="fa fa-align-left fa-2x text-muted"></i>
                            </a>
                        </li>
                        <li role="presentation" class="nav-item">
                            <a tabindex="3" href="#step-geo" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-geo" role="tab" title="Aggiungi geolocalizzazione" style="position: relative">
                                <i class="add-icon fa fa-plus-circle text-danger"></i><i class="fa fa-map-marker fa-2x text-muted"></i>
                            </a>
                        </li>
                        <li role="presentation" class="nav-item">
                            {include uri='design:sensor_api_gui/add/images.tpl'}
                        </li>
                        <li role="presentation" class="nav-item">
                            <div class="image-placeholder image-empty" data-index="0">
                                <input type="hidden" name="images[0][filename]" value="">
                                <input type="hidden" name="images[0][file]" value="">
                            </div>
                        </li>
                        <li role="presentation" class="nav-item">
                            <div class="image-placeholder image-empty" data-index="1">
                                <input type="hidden" name="images[1][filename]" value="">
                                <input type="hidden" name="images[1][file]" value="">
                            </div>
                        </li>
                        <li role="presentation" class="nav-item">
                            <div class="image-placeholder image-empty" data-index="2">
                                <input type="hidden" name="images[2][filename]" value="">
                                <input type="hidden" name="images[2][file]" value="">
                            </div>
                    </ul>

                    <div class="post-privacy post-privacy-select text-muted">
                        {if sensor_settings().HidePrivacyChoice}
                            <i class="fa fa-lock"></i> Solo il team di {social_pagedata().logo_title} potrà leggere questa segnalazione
                        {else}
                        <a href="#" class="text-danger" data-privacy="1"><i class="fa fa-lock text-danger"></i> Solo il team di {social_pagedata().logo_title} potrà leggere questa segnalazione</a>
                        <a href="#" class="text-danger" style="display: none" data-privacy=""><i class="fa fa-globe text-danger"></i> Tutti potranno leggere questa segnalazione</a>
                        {/if}
                        <input type="hidden" name="is_private" value="1" />
                    </div>

                    <div class="post-send">
                        <button type="submit" class="btn btn-primary btn-lg" tabindex="10"><i class="fa fa-send-o"></i></button>
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
{/if}
{def $nearestService = cond(ezini('GeoCoderSettings', 'NearestService', 'ocsensor.ini')|eq('enabled'), true(), false())}
{def $show_map_debug = cond(and(ezhttp_hasvariable('debug', 'get'), fetch('user','has_access_to',hash('module','sensor','function','config'))), 'true', 'false')}
<script>
    var PointsOfInterest = null;
    var CenterMap = {if is_set($areas.children[0].geo.coords[0])}new L.latLng({$areas.children[0].geo.coords[0]}, {$areas.children[0].geo.coords[1]}){else}false{/if};
    var BoundingArea = {if is_set($areas.children[0].bounding_box)}'{$areas.children[0].bounding_box.geo_json}'{else}false{/if};
    {literal}
    $.fn.serializeObject = function () {
        var self = this,
            json = {},
            push_counters = {},
            patterns = {
                "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                "key": /[a-zA-Z0-9_]+|(?=\[\])/g,
                "push": /^$/,
                "fixed": /^\d+$/,
                "named": /^[a-zA-Z0-9_]+$/
            };
        this.build = function (base, key, value) {
            base[key] = value;
            return base;
        };
        this.push_counter = function (key) {
            if (push_counters[key] === undefined) {
                push_counters[key] = 0;
            }
            return push_counters[key]++;
        };
        $.each($(this).serializeArray(), function () {
            // Skip invalid keys
            if (!patterns.validate.test(this.name)) {
                return;
            }
            var k,
                keys = this.name.match(patterns.key),
                merge = this.value,
                reverse_key = this.name;
            while ((k = keys.pop()) !== undefined) {
                // Adjust reverse_key
                reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');
                // Push
                if (k.match(patterns.push)) {
                    merge = self.build([], self.push_counter(reverse_key), merge);
                }
                // Fixed
                else if (k.match(patterns.fixed)) {
                    merge = self.build([], k, merge);
                }
                // Named
                else if (k.match(patterns.named)) {
                    merge = self.build({}, k, merge);
                }
            }
            json = $.extend(true, json, merge);
        });
        return json;
    };
    $(document).ready(function () {
        var addPostGui = $('#add-post-gui');
        addPostGui.find('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            var $target = $(e.target);
        });
        var subject = addPostGui.find('[name="subject"]').on('input change', function () {
            checkTextFields()
        });
        var description = addPostGui.find('[name="description"]').on('input change', function () {
            checkTextFields()
        });
        var address = addPostGui.find('[name="address[address]"]').on('input change', function () {
            checkMapFields()
        });
        var latitude = addPostGui.find('[name="address[latitude]"]').on('input change', function () {
            checkMapFields()
        });
        var longitude = addPostGui.find('[name="address[longitude]"]').on('input change', function () {
            checkMapFields()
        });
        var uploadImage = addPostGui.find('#add_image')
        uploadImage.find('.upload').fileupload({
            dropZone: null,
            formData: function (form) {
                return form.serializeArray();
            },
            url: '/api/sensor_gui/upload-temp',
            autoUpload: true,
            dataType: 'json',
            limitMultiFileUploads: 1,
            change : function (e, data) {
                if(addPostGui.find('.image-empty').length === 0){
                    return false;
                }
            },
            submit: function () {
                uploadImage.find('.upload-button-container').hide();
                uploadImage.find('.upload-button-spinner').show();
            },
            error: function () {
                uploadImage.find('.upload-button-container').show();
                uploadImage.find('.upload-button-spinner').hide();
            },
            done: function (e, data) {
                $.each(data.result, function () {
                    var placeholder = addPostGui.find('.image-empty').first();
                    var index = placeholder.data('index');
                    placeholder
                        .css('background-image', "url('data:" + this.mime + ";base64," + this.file + "')")
                        .removeClass('image-empty');
                    placeholder.find('input[name="images['+index+'][file]"]').val(this.file);
                    placeholder.find('input[name="images['+index+'][filename]"]').val(this.filename);
                });
                uploadImage.find('.upload-button-container').show();
                uploadImage.find('.upload-button-spinner').hide();
                if(addPostGui.find('.image-empty').length === 0){
                    uploadImage.find('.add-icon')
                        .removeClass('fa-plus-circle text-danger')
                        .addClass('fa-check-circle text-success');
                    uploadImage.find('.upload').attr('disabled', 'disabled').fileupload('destroy');
                }
            }
        });
        addPostGui.find('.post-privacy-select a').click(function (e) {
            var privacy = $(this).hide().parent().find('a').not($(this)).show().data('privacy');
            addPostGui.find('[name="is_private"]').val(privacy);
            e.preventDefault();
        })
        addPostGui.find('form').on('submit', function (e) {
            var self = $(this);
            if (self.data('disabled') === true){
                e.preventDefault();
                return false;
            }
            addPostGui.find('#post-spinner').show();
            self.data('disabled', true);
            var payload = addPostGui.find('form').serializeObject();
            if (addPostGui.find('.image-empty').length === 3){
                delete payload.images;
            }else {
                var images = payload.images;
                $(addPostGui.find('.image-empty').get().reverse()).each(function () {
                    var index = parseInt($(this).data('index'));
                    payload.images.splice(index, 1);
                })
            }
            if (address.val().length === 0 && latitude.val().length === 0 && longitude.val().length === 0){
                delete payload.address;
            }
            $.ajax({
                type: "POST",
                url: '/api/sensor_gui/posts',
                data: JSON.stringify(payload),
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                success: function (data,textStatus,jqXHR) {
                    window.location = '/sensor/posts/' + data.id
                },
                error: function (jqXHR) {
                    var error = jqXHR.responseJSON;
                    console.log(error.error_message);
                    self.removeData('disabled');
                    addPostGui.find('#post-spinner').hide();
                }
            });
            e.preventDefault();
        });
        function checkTextFields() {
            var stepItemIcon = $('a[href="#step-text"] .add-icon');
            if (subject.val().length > 0 && description.val().length > 0){
                stepItemIcon
                    .removeClass('fa-plus-circle text-danger')
                    .addClass('fa-check-circle text-success')
            }else{
                stepItemIcon
                    .removeClass('fa-check-circle text-success')
                    .addClass('fa-plus-circle text-danger')
            }
        }
        function checkMapFields() {
            var stepItemIcon = $('a[href="#step-geo"] .add-icon');
            if (address.val().length > 0 && latitude.val().length > 0 && longitude.val().length > 0){
                stepItemIcon
                    .removeClass('fa-plus-circle text-danger')
                    .addClass('fa-check-circle text-success')
            }else{
                stepItemIcon
                    .removeClass('fa-check-circle text-success')
                    .addClass('fa-plus-circle text-danger')
            }
        }
        $(document).on('click', '#sensor_show_map_button', function () {
            $(window).scrollTop(0);
            $('#sensor_hide_map_button, #sensor_full_map, #mylocation-mobile-button').addClass('zindexize');
            $('body').addClass('noscroll');
        });
        $(document).on('click', '#sensor_hide_map_button', function () {
            $('#sensor_hide_map_button, #sensor_full_map, #mylocation-mobile-button').removeClass('zindexize');
            $('body').removeClass('noscroll');
        });
        var showAddPostGui = function() {
            addPostGui.find('form').trigger("reset");
            checkTextFields();
            checkMapFields();
            addPostGui.show().find('.post-subject input').focus();
            $('body').css('overflow', 'hidden');
            {/literal}
            $('#sensor_full_map').sensorAddPost({ldelim}
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
                'default_marker': PointsOfInterest,
                'center_map': CenterMap,
                'bounding_area': BoundingArea
            {rdelim});
            {literal}
        }

        $('a[href="/sensor/add"]').on('click', function (e) {
            showAddPostGui();
            e.preventDefault();
        });

        $('.close-add-post').on('click', function (e) {
            addPostGui.hide();
            $('body').css('overflow', 'auto');
            e.preventDefault();
        });

    });
</script>