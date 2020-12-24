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

                    <ul class="list-inline text-left step-nav" role="tablist">
                        <li role="presentation" class="nav-item active">
                            <a href="#step-text" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-text" role="tab" title="Aggiungi testo" style="position: relative">
                                <i class="add-icon fa fa-plus-circle text-primary"></i><i class="fa fa-pencil fa-2x text-muted"></i>
                            </a>
                        </li>
                        <li role="presentation" class="nav-item">
                            <a tabindex="3" href="#step-geo" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-geo" role="tab" title="Aggiungi geolocalizzazione" style="position: relative">
                                <i class="add-icon fa fa-plus-circle text-primary"></i><i class="fa fa-map-marker fa-2x text-muted"></i>
                            </a>
                        </li>
                        <li role="presentation" class="nav-item">
                            <a tabindex="3" href="#step-image" class="btn btn-lg btn-default" data-toggle="tab" aria-controls="step-image" role="tab" title="Aggiungi immagini" style="position: relative">
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
                        {/if}
                    </div>

                    <div class="row post-send">
                        <div class="col-xs-6">
                            <a href="#" class="close-add-post btn btn-default btn-lg">Annulla</a>
                        </div>
                        <div class="col-xs-6 text-right">
                            <a href="#" class="btn btn-default btn-lg next-step" tabindex="10">Avanti</a>
                            <button type="submit" class="btn btn-primary btn-lg" style="display: none" tabindex="10">Invia</button>
                        </div>
                    </div>

                    <div>
                        {if sensor_settings().HidePrivacyChoice}
                            <p><i class="fa fa-lock"></i> Solo il team di {social_pagedata().logo_title} potrà leggere questa segnalazione</p>
                        {else}
                            <p class="is_private" style="display: none"><i class="fa fa-lock"></i> Solo il team di {social_pagedata().logo_title} potrà leggere questa segnalazione</p>
                            {if sensor_is_moderation_enabled()}
                                <p class="is_public" style="display: none"><i class="fa fa-globe"></i> Tutti potranno leggere questa segnalazione quando il team di {social_pagedata().logo_title} la approverà</p>
                            {else}
                                <p class="is_public" style="display: none"><i class="fa fa-globe"></i> Tutti potranno leggere questa segnalazione</p>
                            {/if}
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
        addPostGui.find('a[data-toggle="tab"]').on('click', function (e) {
            if (!checkTextFields()) {
                showTextValidation();
                e.preventDefault();
                return false;
            }
            if ($(this).attr('href') === '#step-image'){
                addPostGui.find('.next-step').hide().next().show();
            }
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
        var uploadImage = addPostGui.find('#add_image');
        function checkTextFields() {
            var stepItemIcon = $('a[href="#step-text"] .add-icon');
            if (subject.val().length > 0 && description.val().length > 0){
                if (subject.val().length > 0){
                    subject.parent().removeClass('has-warning')
                }
                if (description.val().length > 0){
                    description.parent().removeClass('has-warning')
                }
                stepItemIcon
                    .removeClass('fa-plus-circle text-primary')
                    .addClass('fa-check-circle text-success');

                return true;
            }else{
                stepItemIcon
                    .removeClass('fa-check-circle text-success')
                    .addClass('fa-plus-circle text-primary')

                return false;
            }
        }
        function checkMapFields() {
            var stepItemIcon = $('a[href="#step-geo"] .add-icon');
            if (address.val().length > 0 && latitude.val().length > 0 && longitude.val().length > 0){
                stepItemIcon
                    .removeClass('fa-plus-circle text-primary')
                    .addClass('fa-check-circle text-success');
                addPostGui.find('.drag-marker-help').show();
            }else{
                stepItemIcon
                    .removeClass('fa-check-circle text-success')
                    .addClass('fa-plus-circle text-primary');
                addPostGui.find('.drag-marker-help').hide();
            }
        }
        function checkUploadImages(){
            if(addPostGui.find('.image-empty').length === 0){
                addPostGui.find('a[href="#step-image"]').find('.add-icon')
                    .removeClass('fa-plus-circle text-primary')
                    .addClass('fa-check-circle text-success');
                uploadImage.find('.upload').attr('disabled', 'disabled');
                uploadImage.find('.fileinput-button').hide();
            }else{
                addPostGui.find('a[href="#step-image"]').find('.add-icon')
                    .addClass('fa-plus-circle text-primary')
                    .removeClass('fa-check-circle text-success');
                uploadImage.find('.upload').removeAttr('disabled');
                uploadImage.find('.fileinput-button').show();
            }
        }
        function showTextValidation(){
            if (subject.val().length === 0){
                subject.parent().addClass('has-warning')
            }
            if (description.val().length === 0){
                description.parent().addClass('has-warning')
            }
        }
        function hideTextValidation(){
            if (subject.val().length === 0){
                subject.parent().removeClass('has-warning')
            }
            if (description.val().length === 0){
                description.parent().removeClass('has-warning')
            }
        }
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
                        .removeClass('image-empty')
                        .on('click', function () {
                            $(this)
                                .css('background-image', "")
                                .addClass('image-empty')
                                .find('i').hide();
                            $(this).find('input[name="images['+$(this).data('index')+'][file]"]').val('');
                            $(this).find('input[name="images['+$(this).data('index')+'][filename]"]').val('');
                            checkUploadImages();
                        }).find('i').show();
                    placeholder.find('input[name="images['+index+'][file]"]').val(this.file);
                    placeholder.find('input[name="images['+index+'][filename]"]').val(this.filename);
                });
                uploadImage.find('.upload-button-container').show();
                uploadImage.find('.upload-button-spinner').hide();
                checkUploadImages();
            }
        });
        addPostGui.find('[name="is_private"]').on('change', function (e) {
            var isPrivate = $(this).val().length > 0;
            if (isPrivate){
                addPostGui.find('p.is_private').show();
                addPostGui.find('p.is_public').hide();
            }else{
                addPostGui.find('p.is_private').hide();
                addPostGui.find('p.is_public').show();
            }
            e.preventDefault();
        })
        addPostGui.find('.next-step').on('click', function (e) {
            if (checkTextFields()) {
                var navActive = addPostGui.find('.step-nav li.active');
                var next = navActive.next().find('a');
                if (next.length > 0){
                    next.trigger('click');
                }
            }else{
                showTextValidation();
            }
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
            if (payload.hasOwnProperty('areas')) {
                if (payload.areas.length === 1 && payload.areas[0].length === 0){
                    payload.areas = [];
                }
            }
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
            $('#social_user_alerts').remove();
            $('html').addClass('sensor-add-post');
            $('body').addClass('sensor-add-post').css('overflow', 'hidden');
            addPostGui.find('form').trigger("reset");
            subject.val('');
            description.val('').trigger('keyup');
            address.val('');
            latitude.val('');
            longitude.val('');
            checkTextFields();
            checkMapFields();
            checkUploadImages();
            addPostGui.show().find('.post-subject input').focus();
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
            addPostGui.find('a[href="#step-text"]').trigger('click');
            addPostGui.find('.next-step').show().next().hide();
            hideTextValidation();
            $('html').removeClass('sensor-add-post');
            $('body').removeClass('sensor-add-post').css('overflow', 'auto');
            e.preventDefault();
        });

    });
</script>