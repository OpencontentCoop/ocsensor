;$.fn.serializeObject = function () {
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
}

;(function ($, window, document, undefined) {

    'use strict';

    var pluginName = 'sensorAddPost',
        defaults = {
            'geocoder': 'Nominatim',
            'geocoder_params': {},
            'nearest_service': {
                'debug': true,
                'url': false,
                'typeName': false,
                'maxFeatures': 0,
                'srsName': false,
                'geometryName': false
            },
            'strict_in_area': false,
            'strict_in_area_alert': 'The selected location is not covered by the service',
            'no_suggestion_message': 'No result found',
            'default_marker': [],
            'center_map': false,
            'bounding_area': false,
            'debug_bounding_area': false,
            'debug_meta_info': false,
            'debug_geocoder': false,
            'map_params': {
                scrollWheelZoom: true,
                loadingControl: true
            },
            'use_smart_gui': false,
            'default_user_placement': 0,
            'additionalWMSLayers': [],
            'area_cache_prefix': 'area-',
            'persistentMetaKeys': ['pingback_url', 'approver_id'],
            'faq_predictor': false
        };

    function Plugin(element, options) {
        this.element = $(element);
        this.selectedArea = 0;
        this.settings = $.extend({}, defaults, options);

        this.settings.geoinput_splitted = false;
        this.settings.geoinput_autocomplete = false;

        this.positionBeforeDrag = false;

        this.map = new L.Map(
            this.element.attr('id'),
            this.settings.map_params
        ).setActiveArea('viewport');
        var osmLayer = L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);
        var baseLayers = [];
        baseLayers[$.sensorTranslate.translate('Map')] = osmLayer;
        baseLayers[$.sensorTranslate.translate('Satellite')] =  L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
        });
        var mapLayers = [];
        if (this.settings.additionalWMSLayers.length > 0) {
            $.each(this.settings.additionalWMSLayers, function(){
                mapLayers[$.sensorTranslate.translate(this.attribution)] = L.tileLayer.wms(this.baseUrl, {
                    layers: this.layers,
                    version: this.version,
                    format: this.format,
                    transparent: this.transparent,
                    attribution: this.attribution
                });
            });
        }
        var layers = L.control.layers(baseLayers, mapLayers, {'position': 'topleft'}).addTo(this.map);
        if (!L.Browser.touch) {
            L.DomEvent
                .disableClickPropagation(layers._container)
                .disableScrollPropagation(layers._container);
        } else {
            L.DomEvent.disableClickPropagation(layers._container);
        }
        this.initMapEvents();

        this.viewport = this.element.find('.viewport');

        this.markers = L.featureGroup().addTo(this.map);
        this.perimeters = L.featureGroup().addTo(this.map);
        this.globalBoundingPerimeter = false;
        this.globalBoundingBox = false;

        var nominatimGeocoderParams = {};
        if (typeof this.settings.bounding_area === 'string') {
            try {
                this.globalBoundingBox = L.geoJson(JSON.parse(this.settings.bounding_area)).getBounds();
                this.globalBoundingPerimeter = L.rectangle(this.globalBoundingBox, {
                    color: 'blue',
                    weight: 2,
                    fillOpacity: 0
                });
                if (this.settings.debug_bounding_area) {
                    this.map.addLayer(this.globalBoundingPerimeter);
                }
                var viewBox = this.globalBoundingBox.getWest() + ',' + this.globalBoundingBox.getSouth() + ',' + this.globalBoundingBox.getEast() + ',' + this.globalBoundingBox.getNorth();
                if (this.settings.geocoder === "Nominatim") {
                    nominatimGeocoderParams = {
                        geocodingQueryParams: {
                            viewbox: viewBox,
                            bounded: 1
                        }
                    };
                } else if (this.settings.geocoder === "NominatimDetailed") {
                    if (this.settings.geocoder_params === false) {
                        this.settings.geocoder_params = {geocodingQueryParams: {}};
                    }
                    this.settings.geocoder_params.geocodingQueryParams.viewbox = viewBox;
                    this.settings.geocoder_params.geocodingQueryParams.bounded = 1;
                }
            } catch (err) {
                console.log(err.message);
            }
        }

        if (this.settings.geocoder === "Nominatim" || this.settings.geocoder === '') {
            this.geocoder = L.Control.Geocoder.nominatim(nominatimGeocoderParams);
        } else if (this.settings.geocoder === "Geoserver") {
            this.geocoder = L.Control.Geocoder.geoserver(this.settings.geocoder_params);
            this.settings.geoinput_autocomplete = true;
        } else if (window.XDomainRequest) {
            this.geocoder = L.Control.Geocoder.bing(this.settings.geocoder_params);
        } else if (this.settings.geocoder === "Google") {
            this.geocoder = L.Control.Geocoder.google(this.settings.geocoder_params);
        } else if (this.settings.geocoder === "NominatimDetailed") {
            this.geocoder = L.Control.Geocoder.nominatimDetailed(this.settings.geocoder_params);
            this.settings.geoinput_splitted = true;
        }

        this.selectArea = $('.select-sensor-area');
        this.suggestionContainer = $('#input-results');
        this.inputLat = $('input#latitude');
        this.inputLng = $('input#longitude');
        this.inputAddress = $('input#input-address');
        this.searchButton = $('#input-address-button');
        this.inputMeta = $('textarea.ezcca-sensor_post_meta');
        var currentMeta = JSON.parse(this.inputMeta.val() || '{}');
        var persistentMeta = {};
        $.each(this.settings.persistentMetaKeys, function (index, value){
            if (currentMeta.hasOwnProperty(value)){
                persistentMeta[value] = currentMeta[value];
            }
        });
        this.persistentMeta = persistentMeta;

        this._debugMeta();

        if (this.settings.geoinput_splitted) {
            this.inputAddress.hide();
            this.inputNumber = $('<input class="form-control" size="20" type="text" placeholder="civico" id="input-number" value="" style="width: 20%;border-left:0">').prependTo(this.inputAddress.parent());
            this.inputStreet = $('<input class="form-control" size="20" type="text" placeholder="Via, viale, piazza..." id="input-street" value="" style="width: 80%;border-right:0">').prependTo(this.inputAddress.parent());
        }
        this.initSearch();

        this.initPerimeters();

        this.debugNearest = this.settings.nearest_service.debug ? L.featureGroup().addTo(this.map) : false;
        this.debugGeocoder = this.settings.debug_geocoder ? L.featureGroup().addTo(this.map) : false;

        this.refreshViewPort();
        this.refreshMap();

        if (this.settings.use_smart_gui) {
            this.initSmartGui();
        }
    }

    $.extend(Plugin.prototype, {

        initSmartGui: function () {
            var plugin = this;

            var hasValidTexts = false;
            var hasFaqRequest = false;
            var addPostGui = $('#add-post-gui');

            var subject = addPostGui.find('[name="subject"]')
                .on('input change', function () {
                    checkTextFields()
                });

            var description = addPostGui.find('[name="description"]')
                .on('input change', function () {
                    checkTextFields()
                });

            var category = addPostGui.find('[name="category"]');

            var address = addPostGui.find('[name="address[address]"]')
                .on('input change', function () {
                    checkMapFields()
                });

            var latitude = addPostGui.find('[name="address[latitude]"]')
                .on('input change', function () {
                    checkMapFields()
                });

            var longitude = addPostGui.find('[name="address[longitude]"]')
                .on('input change', function () {
                    checkMapFields()
                });

            var uploadImage = addPostGui.find('#add_image');
            uploadImage.find('.upload').fileupload({
                pasteZone: null,
                dropZone: null,
                formData: function (form) {
                    return form.serializeArray();
                },
                url: '/api/sensor_gui/upload-temp/images',
                autoUpload: true,
                dataType: 'json',
                limitMultiFileUploads: 1,
                change: function (e, data) {
                    if (addPostGui.find('.image-empty').length === 0) {
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
                                $(this).find('input[name="images[' + $(this).data('index') + '][file]"]').val('');
                                $(this).find('input[name="images[' + $(this).data('index') + '][filename]"]').val('');
                                checkUploadImages();
                            }).find('i').show();
                        placeholder.find('input[name="images[' + index + '][file]"]').val(this.filepath);
                        placeholder.find('input[name="images[' + index + '][filename]"]').val(this.filename);
                    });
                    uploadImage.find('.upload-button-container').show();
                    uploadImage.find('.upload-button-spinner').hide();
                    checkUploadImages();
                }
            });

            var uploadFile = addPostGui.find('#add_file');
            if (uploadFile.length > 0) {
                uploadFile.find('.upload').fileupload({
                    pasteZone: null,
                    dropZone: null,
                    formData: function (form) {
                        return form.serializeArray();
                    },
                    url: '/api/sensor_gui/upload-temp/files',
                    autoUpload: true,
                    dataType: 'json',
                    limitMultiFileUploads: 1,
                    change: function (e, data) {
                        if (addPostGui.find('.file-empty').length === 0) {
                            return false;
                        }
                    },
                    submit: function () {
                        uploadFile.find('.upload-button-container').hide();
                        uploadFile.find('.upload-button-spinner').show();
                    },
                    error: function (data) {
                        alert(data.error_message);
                        uploadFile.find('.upload-button-container').show();
                        uploadFile.find('.upload-button-spinner').hide();
                    },
                    done: function (e, data) {
                        $.each(data.result, function () {
                            var placeholder = addPostGui.find('.file-empty').first();
                            var index = placeholder.data('index');
                            placeholder
                                .css('background-image', "url('data:" + this.mime + ";base64," + this.file + "')")
                                .css('background-position', 'center center')
                                .css('background-repeat', 'no-repeat')
                                .css('background-size', 'auto')
                                .attr('title', this.filename)
                                .removeClass('file-empty')
                                .on('click', function () {
                                    $(this)
                                        .css('background-image', "")
                                        .attr('title', "")
                                        .addClass('file-empty')
                                        .find('i').hide();
                                    $(this).find('input[name="files[' + $(this).data('index') + '][file]"]').val('');
                                    $(this).find('input[name="files[' + $(this).data('index') + '][filename]"]').val('');
                                    checkUploadFiles();
                                }).find('i').show();
                            placeholder.find('input[name="files[' + index + '][file]"]').val(this.filepath);
                            placeholder.find('input[name="files[' + index + '][filename]"]').val(this.filename);
                        });
                        uploadFile.find('.upload-button-container').show();
                        uploadFile.find('.upload-button-spinner').hide();
                        checkUploadFiles();
                    }
                });
            }

            var behalfOf = addPostGui.find('#behalf-of');
            var behalfOfChannel = addPostGui.find('#behalf-of-channel');
            var behalfOfSearch = addPostGui.find('#behalf-of-search');
            var behalfOfCreate = addPostGui.find('#behalf-of-create');
            var behalfOfView = addPostGui.find('#behalf-of-view');
            var behalfOfSearchInput = addPostGui.find('#behalf-of-search-input')
                .val('')
                .typeahead({
                    minLength: 3,
                    hint: false
                }, {
                    limit: 15,
                    name: 'behalf-of-search-input',
                    source: new Bloodhound({
                        queryTokenizer: Bloodhound.tokenizers.whitespace,
                        datumTokenizer: Bloodhound.tokenizers.whitespace,
                        remote: {
                            url: '/api/sensor_gui/users?q=%QUERY',
                            wildcard: '%QUERY',
                            transform: function (response) {
                                //console.log(response);
                                var data = [];
                                $.each(response.items, function () {
                                    data.push(this);
                                });
                                return data;
                            }
                        }
                    }),
                    templates: {
                        suggestion: Handlebars.compile('<div><strong>{{name}}</strong> – {{email}} {{fiscal_code}}</div>')
                    }
                })
                .on('typeahead:select', function (e, suggestion) {
                    behalfOfSearch.addClass('hide');
                    behalfOfCreate.addClass('hide');
                    behalfOfView.removeClass('hide').find('span').text(suggestion.name);
                    behalfOf.val(suggestion.id);
                    checkBehalfFields();
                    e.preventDefault();
                })
                .on('keydown', function (e) {
                    if (e.keyCode === 13) {
                        e.preventDefault();
                    }
                });
            var behalfOfAnonymous = addPostGui.find('#behalf-of-anonymous')
                .attr('checked', false)
                .on('change', function () {
                    var userName = $.sensorTranslate.translate('Anonymous user');
                    var userId = $(this).data('userid');
                    if ($(this).is(':checked')) {
                        behalfOfSearch.addClass('hide');
                        behalfOfCreate.addClass('hide');
                        behalfOfView.removeClass('hide').find('span').text(userName);
                        behalfOf.val(userId);
                        checkBehalfFields();
                    } else {
                        behalfOfSearch.removeClass('hide');
                        behalfOfCreate.addClass('hide');
                        behalfOfView.addClass('hide').find('span').text('');
                        behalfOf.val('');
                        behalfOfSearchInput.val('');
                        checkBehalfFields();
                    }
                });
            behalfOfView.find('i').css('cursor', 'pointer').on('click', function (e) {
                behalfOfSearch.removeClass('hide');
                behalfOfCreate.addClass('hide');
                behalfOfView.addClass('hide').find('span').text('');
                behalfOf.val('');
                //behalfOfChannel.val('');
                behalfOfSearchInput.val('');
                behalfOfAnonymous.attr('checked', false);
                checkBehalfFields();
                e.preventDefault();
            });
            $('#behalf-of-create-button').on('click', function (e) {
                behalfOfSearch.addClass('hide');
                behalfOfView.addClass('hide');
                behalfOfCreate.html('').removeClass('hide').opendataFormCreate({
                    class: 'user',
                    parent: plugin.settings.default_user_placement
                }, {
                    connector: 'create-user',
                    onSuccess: function (response) {
                        behalfOfSearch.addClass('hide');
                        behalfOfCreate.addClass('hide');
                        behalfOfView.removeClass('hide').find('span').text(response.content.metadata.name[$.opendataTools.settings('language')]);
                        behalfOf.val(response.content.metadata.id);
                        checkBehalfFields();
                    },
                    alpaca: {
                        'options': {
                            'form': {
                                'buttons': {
                                    'submit': {
                                        'value': 'Crea',
                                        'styles': 'btn btn-sm btn-success pull-right'
                                    },
                                    'reset': {
                                        'click': function () {
                                            behalfOfSearch.removeClass('hide');
                                            behalfOfCreate.addClass('hide');
                                            behalfOfView.addClass('hide').find('span').text('');
                                            behalfOf.val('');
                                            behalfOfSearchInput.val('');
                                            checkBehalfFields();
                                        },
                                        'value': 'Annulla',
                                        'styles': 'btn btn-sm btn-danger pull-left'
                                    }
                                }
                            }
                        }
                    }
                });
                e.preventDefault();
            });

            function checkBehalfFields() {
                var stepItemIcon = addPostGui.find('a[href="#step-behalf"] .add-icon');
                if (behalfOf.val() !== '' && behalfOfChannel.val() !== ''){
                    stepItemIcon
                        .removeClass('fa-plus-circle text-primary')
                        .addClass('fa-check-circle text-success');
                }else{
                    stepItemIcon
                        .removeClass('fa-check-circle text-success')
                        .addClass('fa-plus-circle text-primary')
                }
            }

            function checkTextFields() {
                var stepItemIcon = addPostGui.find('a[href="#step-text"] .add-icon');
                if (subject.val().length > 0 && description.val().length > 0) {
                    if (subject.val().length > 0) {
                        subject.parent().removeClass('has-warning')
                    }
                    if (description.val().length > 0) {
                        description.parent().removeClass('has-warning')
                    }
                    stepItemIcon
                        .removeClass('fa-plus-circle text-primary')
                        .addClass('fa-check-circle text-success');

                    hasValidTexts = true;
                    return true;
                } else {
                    stepItemIcon
                        .removeClass('fa-check-circle text-success')
                        .addClass('fa-plus-circle text-primary')

                    return false;
                }
            }

            function checkMapFields() {
                var stepItemIcon = addPostGui.find('a[href="#step-geo"] .add-icon');
                if (address.val().length > 0 && latitude.val().length > 0 && longitude.val().length > 0) {
                    stepItemIcon
                        .removeClass('fa-plus-circle text-primary')
                        .addClass('fa-check-circle text-success');
                    addPostGui.find('.drag-marker-help').show();
                } else {
                    stepItemIcon
                        .removeClass('fa-check-circle text-success')
                        .addClass('fa-plus-circle text-primary');
                    addPostGui.find('.drag-marker-help').hide();
                }
            }

            function checkUploadImages() {
                if (addPostGui.find('.image-empty').length === 0) {
                    addPostGui.find('a[href="#step-image"]').find('.add-icon')
                        .removeClass('fa-plus-circle text-primary')
                        .addClass('fa-check-circle text-success');
                    uploadImage.find('.upload').attr('disabled', 'disabled');
                    uploadImage.find('.fileinput-button').hide();
                } else {
                    addPostGui.find('a[href="#step-image"]').find('.add-icon')
                        .addClass('fa-plus-circle text-primary')
                        .removeClass('fa-check-circle text-success');
                    uploadImage.find('.upload').removeAttr('disabled');
                    uploadImage.find('.fileinput-button').show();
                }
            }

            function checkUploadFiles() {
                if (addPostGui.find('.file-empty').length === 0) {
                    addPostGui.find('a[href="#step-file"]').find('.add-icon')
                        .removeClass('fa-plus-circle text-primary')
                        .addClass('fa-check-circle text-success');
                    uploadFile.find('.upload').attr('disabled', 'disabled');
                    uploadFile.find('.fileinput-button').hide();
                } else {
                    addPostGui.find('a[href="#step-file"]').find('.add-icon')
                        .addClass('fa-plus-circle text-primary')
                        .removeClass('fa-check-circle text-success');
                    uploadFile.find('.upload').removeAttr('disabled');
                    uploadFile.find('.fileinput-button').show();
                }
            }

            function showTextValidation() {
                if (subject.val().length === 0) {
                    subject.parent().addClass('has-warning')
                }
                if (description.val().length === 0) {
                    description.parent().addClass('has-warning')
                }
            }

            function hideTextValidation() {
                if (subject.val().length === 0) {
                    subject.parent().removeClass('has-warning')
                }
                if (description.val().length === 0) {
                    description.parent().removeClass('has-warning')
                }
            }

            addPostGui.find('a[data-toggle="tab"]').on('click', function (e) {
                if (!checkTextFields()) {
                    showTextValidation();
                    e.preventDefault();
                    return false;
                }
                if (plugin.settings.faq_predictor && hasValidTexts && hasFaqRequest === false){
                    console.log('load faq by predict')
                    hasFaqRequest = true;
                    $.ajax({
                        type: 'POST',
                        url: '/api/sensor_gui/predict/faqs',
                        contentType: 'application/json',
                        dataType: 'json',
                        data: JSON.stringify({
                            subject: addPostGui.find('[name="subject"]').val(),
                            description: addPostGui.find('[name="description"]').val()
                        }),
                        success: function (response) {
                            if (response.faqs.totalCount > 0){
                                //addPostGui.find('.is-last-tab').removeClass('last-tab');
                                $('#nav-faqs').show();//.find('[data-toggle="tab"]').addClass('last-tab');
                                var faqs = $.templates('#tpl-faq-on-create').render(response.faqs);
                                $('#step-faq').html(faqs);
                            }
                        },
                        error: function () {}
                    });
                }
                if ($(this).hasClass('last-tab')) {
                    addPostGui.find('.next-step').hide().next().show();
                }
            });

            addPostGui.find('[name="is_private"]').on('change', function (e) {
                var isPrivate = $(this).val().length > 0;
                if (isPrivate) {
                    addPostGui.find('p.is_private').show();
                    addPostGui.find('p.is_public').hide();
                } else {
                    addPostGui.find('p.is_private').hide();
                    addPostGui.find('p.is_public').show();
                }
                e.preventDefault();
            })

            addPostGui.find('.next-step').on('click', function (e) {
                if (checkTextFields()) {
                    var navActive = addPostGui.find('.step-nav li.active');
                    var next = navActive.next().find('a');
                    if (next.length > 0) {
                        next.trigger('click');
                    }
                } else {
                    showTextValidation();
                }
                e.preventDefault();
            })

            plugin.selectArea.on('change sensor-set-area', function (){
                var current = plugin.selectArea.val();
                category.find('[data-avoid_areas]').each(function (){
                    if ($(this).data('avoid_areas').indexOf(parseInt(current)) >= 0){
                        $(this).attr('disabled', 'disabled').hide();
                        if ($(this).is(':selected')){
                            category.val('');
                        }
                    }else{
                        $(this).attr('disabled', false).show();
                    }
                })
            });

            addPostGui.find('form').on('submit', function (e) {
                var self = $(this);
                if (self.data('disabled') === true) {
                    e.preventDefault();
                    return false;
                }
                addPostGui.find('#post-spinner').show();
                self.data('disabled', true);
                var payload = addPostGui.find('form').serializeObject();
                if (payload.hasOwnProperty('areas')) {
                    if (payload.areas.length === 1 && payload.areas[0].length === 0) {
                        payload.areas = [];
                    }
                }
                if (addPostGui.find('.image-empty').length === 3) {
                    delete payload.images;
                } else {
                    var images = payload.images;
                    $(addPostGui.find('.image-empty').get().reverse()).each(function () {
                        var index = parseInt($(this).data('index'));
                        payload.images.splice(index, 1);
                    })
                }
                if (uploadFile.length > 0) {
                    if (addPostGui.find('.file-empty').length === 3) {
                        delete payload.files;
                    } else {
                        var files = payload.files;
                        $(addPostGui.find('.file-empty').get().reverse()).each(function () {
                            var index = parseInt($(this).data('index'));
                            payload.files.splice(index, 1);
                        })
                    }
                }
                if (address.val().length === 0 && latitude.val().length === 0 && longitude.val().length === 0) {
                    delete payload.address;
                }

                var csrfToken;
                var tokenNode = document.getElementById('ezxform_token_js');
                if ( tokenNode ){
                    csrfToken = tokenNode.getAttribute('title');
                }

                $.ajax({
                    type: "POST",
                    url: '/api/sensor_gui/posts',
                    data: JSON.stringify(payload),
                    contentType: "application/json; charset=utf-8",
                    headers: {'X-CSRF-TOKEN': csrfToken},
                    dataType: "json",
                    success: function (data, textStatus, jqXHR) {
                        window.location = $.opendataTools.settings('accessPath')+'/sensor/posts/' + data.id
                    },
                    error: function (jqXHR) {
                        var error = jqXHR.responseJSON;
                        alert(error.error_message);
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
                $('.leaflet-control-layers').removeClass("leaflet-control-layers-expanded");
                $('#sensor_hide_map_button, #sensor_full_map, #mylocation-mobile-button').removeClass('zindexize');
                $('body').removeClass('noscroll');
            });

            var showAddPostGui = function () {
                $.get('/api/sensor_gui/default_area', function (response){

                    hasValidTexts = false;
                    hasFaqRequest = false;
                    //addPostGui.find('.is-last-tab').addClass('last-tab');
                    $('#nav-faqs').hide();//.find('[data-toggle="tab"]').removeClass('last-tab');
                    $('#step-faq').html('');

                    $('body > .main, body > .full_page_photo, #posts-search').hide();
                    plugin.selectedArea = response.id;
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
                    checkBehalfFields();
                    addPostGui.show().find('.post-subject input').focus();
                    behalfOfView.find('i').trigger('click');
                    behalfOfAnonymous.trigger('click');
                    plugin.refreshViewPort();
                    plugin.refreshMap();
                    if (plugin.selectedArea > 0){
                        plugin.selectArea.val(plugin.selectedArea).trigger('change');
                    }
                });
            }

            var addButton = $('a[href$="/sensor/add"]');
            addButton.on('click', function (e) {
                showAddPostGui();
                e.preventDefault();
            });
            var hash = window.location.hash;
            if (hash === '#add'){
                addButton.trigger('click');
            }

            $('.close-add-post').on('click', function (e) {
                $('body > .main, body > .full_page_photo, #posts-search').show();
                addPostGui.hide();
                addPostGui.find('a[href="#step-text"]').trigger('click');
                addPostGui.find('.next-step').show().next().hide();
                addPostGui.find('p.is_public').hide();
                addPostGui.find('p.is_private').hide();
                hideTextValidation();
                $('html').removeClass('sensor-add-post');
                $('body').removeClass('sensor-add-post').css('overflow', 'auto');
                e.preventDefault();
            });
        },

        refreshMap: function () {
            this.markers.clearLayers();
            this.map.invalidateSize();
            if (this.settings.default_marker) {
                this.setUserMarker(
                    new L.LatLng(this.settings.default_marker.coords[0], this.settings.default_marker.coords[1]),
                    this.settings.default_marker.address || null,
                    function () {
                    }
                );
            } else if (typeof this.settings.center_map === 'object') {
                this.map.setView(this.settings.center_map, 15);
            } else if (this.globalBoundingBox) {
                this.map.fitBounds(this.globalBoundingBox);
            } else if(this.perimeters.getLayers().length > 0) {
                this.map.fitBounds(this.perimeters.getBounds());
            }
        },

        initMapEvents: function () {
            var self = this;

            if (L.Browser.android || L.Browser.mobile || L.Browser.touch || L.Browser.retina) {
                $('.leaflet-control-layers-selector').on('change', function () {
                    setTimeout(function () {
                        $('.leaflet-control-layers').removeClass("leaflet-control-layers-expanded")
                    }, 500);
                })
            }

            $('.zoomIn').on('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
                self.map.setZoom(self.map.getZoom() < self.map.getMaxZoom() ? self.map.getZoom() + 1 : self.map.getMaxZoom());
            });

            $('.zoomOut').on('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
                self.map.setZoom(self.map.getZoom() > self.map.getMinZoom() ? self.map.getZoom() - 1 : self.map.getMinZoom());
            });

            $('.fitbounds').on('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
                self.map.fitBounds(markers.getBounds(), {padding: [10, 10]});
            });

            self.map.on('click', function (e) {
                self.setUserMarker(e.latlng);
            });

            $('#mylocation-button, #mylocation-mobile-button').on('click', function (e) {
                var button = $(e.currentTarget);
                button.html('<i class="fa fa-circle-o-notch fa-spin"></i>');
                self.map.loadingControl.addLoader('mylocation');
                self.map.locate({setView: false, watch: false})
                    .on('locationfound', function (e) {
                        self.map.loadingControl.removeLoader('mylocation');
                        button.html('<i class="fa fa-location-arrow"></i>');
                        self.setUserMarker(new L.LatLng(e.latitude, e.longitude));
                        self.map.off('locationfound');
                    })
                    .on('locationerror', function (e) {
                        button.html('<i class="fa fa-location-arrow"></i>');
                        self.map.loadingControl.removeLoader('mylocation');
                        alert(e.message);
                        self.map.off('locationerror');
                    });
            });

            $( window ).resize(function() {
                self.refreshViewPort();
            });
        },

        refreshViewPort: function(){
            var windowWidth = $(window).width();
            var editBoxWidth = $('#edit').width();
            var viewportWidth = windowWidth - editBoxWidth;
            //this.viewport.addClass('debug-viewport');
            if (viewportWidth >= 600){
                this.viewport.width(viewportWidth);
            }else{
                this.viewport.width('100%');
            }
        },

        initSearch: function () {
            var self = this;

            if (self.settings.geoinput_splitted) {
                self.inputStreet.on('keypress', function (e) {
                    if (e.which === 13) {
                        self.inputNumber.focus();
                        e.preventDefault();
                    }
                });

                self.inputNumber.on('keypress', function (e) {
                    if (e.which === 13) {
                        self.searchButton.trigger('click');
                        e.preventDefault();
                    }
                });
            }

            var suggestionSelected;
            var suggestionSelectedClass = 'list-group-item-warning';

            self.inputAddress.on('click', function (e) {
                //$(this).select();
            }).on('keypress', function (e) {
                if (e.which === 13) {
                    if (suggestionSelected && suggestionSelected.hasClass(suggestionSelectedClass)){
                        suggestionSelected.trigger('click');
                    }else {
                        self.searchButton.trigger('click');
                    }
                    e.preventDefault();
                }
            });

            var isResettingInput = function (which, val){
                return (
                    (which === 8 ||which === 46 )
                    && val.length === 0
                );
            }

            var clearGeoInputs = function (){
                self.clearGeo();
                self.selectArea.val('');
                self.markers.clearLayers();
            }

            if (self.settings.geoinput_autocomplete){
                self.inputAddress.on('keyup', function (e) {

                    if (isResettingInput(e.which, $(this).val())) {
                        clearGeoInputs();

                    } else if (
                        e.which === 8 || //del
                        e.which === 46 || //canc
                        e.which === 222 || //quote
                        (e.which >= 48 && e.which <= 57) || //numbers
                        (e.which >= 65 && e.which <= 90) || //chars
                        (e.which >= 96 && e.which <= 105) //numbers
                    ){
                        self.searchButton.trigger('click');

                    } else if (e.which === 40) { //arrow down
                        if (suggestionSelected) {
                            suggestionSelected.removeClass(suggestionSelectedClass);
                            var nextSuggestion = suggestionSelected.next();
                            if (nextSuggestion.length) {
                                suggestionSelected = nextSuggestion.addClass(suggestionSelectedClass);
                            } else {
                                suggestionSelected = self.suggestionContainer.find('a.list-group-item').first().addClass(suggestionSelectedClass);
                            }
                        } else {
                            suggestionSelected = self.suggestionContainer.find('a.list-group-item').first().addClass(suggestionSelectedClass);
                        }
                        if (suggestionSelected && suggestionSelected.hasClass(suggestionSelectedClass)){
                            self.inputAddress.val(suggestionSelected.text());
                        }
                    } else if (e.which === 38) { //arrow up
                        if (suggestionSelected) {
                            suggestionSelected.removeClass(suggestionSelectedClass);
                            var prevSuggestion = suggestionSelected.prev();
                            if (prevSuggestion.length) {
                                suggestionSelected = prevSuggestion.addClass(suggestionSelectedClass);
                            } else {
                                suggestionSelected = self.suggestionContainer.find('a.list-group-item').last().addClass(suggestionSelectedClass);
                            }
                        } else {
                            suggestionSelected = self.suggestionContainer.find('a.list-group-item').last().addClass(suggestionSelectedClass);
                        }
                        if (suggestionSelected && suggestionSelected.hasClass(suggestionSelectedClass)){
                            self.inputAddress.val(suggestionSelected.text());
                        }
                    }
                });
            }else{
                self.inputAddress.on('keyup', function (e) {
                    if (isResettingInput(e.which, $(this).val())) {
                        clearGeoInputs();
                    }
                });
            }

            self.searchButton.on('click', function (e) {
                var query = self.inputAddress.val();
                var queryString = query;
                if (self.settings.geoinput_splitted) {
                    query = {street: self.inputNumber.val() + ' ' + self.inputStreet.val()};
                    queryString = query.street;
                }
                self.clearSuggestions();
                if (queryString.trim().length > 0) {
                    self.map.loadingControl.addLoader('inputsearch');
                    self.searchButton.html('<i class="fa fa-circle-o-notch fa-spin"></i>');
                    self.geocoder.geocode(query, function (response) {
                        var results = response;
                        if (self.debugGeocoder) {
                            self.debugGeocoder.clearLayers();
                            $.each(response, function (i, o) {
                                self.debugGeocoder.addLayer(
                                    L.circleMarker(new L.LatLng(o.center.lat, o.center.lng), {color: 'blue'})
                                        .bindPopup(o.name)
                                );
                            });
                            if (self.debugGeocoder.getLayers().length > 0) {
                                self.map.fitBounds(self.debugGeocoder.getBounds());
                            }
                        }
                        var skipCheck = false;
                        if (self.settings.geocoder_params.hasOwnProperty('skipAreaCheck')){
                            skipCheck = parseInt(self.settings.geocoder_params.skipAreaCheck) === 1;
                        }
                        if (self.settings.strict_in_area && !skipCheck) {
                            results = [];
                            $.each(response, function (i, o) {
                                if (self.getPerimeterIdByPosition(new L.LatLng(o.center.lat, o.center.lng))) {
                                    results.push(o);
                                }
                            });
                        }
                        self.map.loadingControl.removeLoader('inputsearch');
                        self.searchButton.html('<i class="fa fa-search"></i>');

                        self.clearSuggestions();
                        if (results.length > 0) {
                            // deduplicate suggestions
                            var suggestions = [];
                            $.each(results, function (i, o) {
                                var name = o.name;
                                var alreadySuggested = $.grep(suggestions, function (e) {
                                    return e.name === name;
                                });
                                if (alreadySuggested.length === 0) {
                                    suggestions.push(o);
                                }
                            });

                            if (suggestions.length > 1 || self.settings.geoinput_autocomplete) {
                                $.each(suggestions, function (i, o) {
                                    self.appendSuggestion(o);
                                });
                            } else {
                                self.setUserMarker(new L.LatLng(suggestions[0].center.lat, suggestions[0].center.lng), suggestions[0]);
                                self.appendGeocoderMeta(suggestions[0]);
                            }
                        } else {
                            self.noSuggestion();
                        }
                    }, this);
                }
            });
        },

        initPerimeters: function () {
            var self = this;

            var addPerimeter = function (id, data){
                $.addGeoJSONLayer(
                    data.geoBounding.geoJson,
                    self.map,
                    self.perimeters, null, {
                        color: data.geoBounding.color,
                        weight: 2,
                        opacity: 0.4,
                        fillOpacity: 0.2
                    },
                    null,
                    function (feature, layer) {
                        feature.properties._id = id;
                        layer.on('click', function (e) {
                            self.setUserMarker(e.latlng);
                        });
                    }
                );
            };

            $('[data-geojson]').each(function () {
                var item = $(this);
                var perimeterUrl = '/api/sensor_gui/areas/' + item.data('id');
                if (window.sessionStorage !== undefined && sessionStorage.getItem(self.settings.area_cache_prefix+perimeterUrl)) {
                    addPerimeter(item.data('id'), JSON.parse(sessionStorage.getItem(self.settings.area_cache_prefix+perimeterUrl)));
                }else {
                    $.getJSON(perimeterUrl, function (data) {
                        addPerimeter(item.data('id'), data);
                        if (window.sessionStorage !== undefined){
                            sessionStorage.setItem(self.settings.area_cache_prefix+perimeterUrl, JSON.stringify(data));
                        }
                    });
                }
            });

            if (self.perimeters.getLayers().length > 0) {
                if (!self.settings.default_marker) {
                    self.map.fitBounds(self.perimeters.getBounds());
                }
                self.selectArea.on('change', function () {
                    var current = self.selectArea.val();
                    var layer = self.getPerimeterLayerById(current);
                    if (layer) {
                        self.map.fitBounds(layer.getBounds());
                    }
                    if (self.getUserMarker() && current !== self.getPerimeterIdByPosition(self.getUserMarker().getLatLng())) {
                        self.markers.clearLayers();
                        self.clearGeo();
                    }
                });
            } else {
                self.settings.strict_in_area = false;
            }
        },

        getUserMarker: function () {
            var self = this;

            if (self.markers.getLayers().length > 0) {
                return self.markers.getLayers()[0];
            }

            return false;
        },

        setUserMarker: function (latLng, address, cb, context) {
            var self = this;
            if (!$.isFunction(cb)) {
                var areaId = self.getPerimeterIdByPosition(latLng);
                if (self.settings.strict_in_area && !areaId) {
                    alert(self.settings.strict_in_area_alert);
                    if (self.positionBeforeDrag) {
                        self.setUserMarker(self.positionBeforeDrag, null, function () {});
                    }
                    return false;
                }
            }
            self.positionBeforeDrag = false;
            self.markers.clearLayers();
            var userMarker = new L.Marker(latLng, {
                icon: L.MakiMarkers.icon({icon: "star", color: "#f00", size: "l"}),
                draggable: true
            }).on('dragstart', function (event) {
                self.positionBeforeDrag = event.target.getLatLng();
            }).on('dragend', function (event) {
                self.setUserMarker(event.target.getLatLng());
            });
            self.markers.addLayer(userMarker);
            var zoom = self.map.getZoom();
            self.map.setView(latLng, zoom > 17 ? zoom : 17);

            if (self.debugGeocoder) {
                self.debugGeocoder.clearLayers();
            }
            if ($.isFunction(cb)) {
                cb.call(context, self, userMarker);
            } else {
                self.clearGeo();
                self.setGeo(latLng, address);
                self.setArea(areaId);
                self.appendNearestMeta(latLng, areaId);
                self.clearSuggestions();
            }
        },

        noSuggestion: function (suggestion) {
            var self = this;

            var item = $('<a class="list-group-item" href="#">' + self.settings.no_suggestion_message + '</a>')
                .appendTo(this.suggestionContainer)
                .on('click', function (e) {
                    self.suggestionContainer.empty()
                });
        },

        appendSuggestion: function (suggestion) {
            var self = this;

            $('<a class="list-group-item" href="#">' + suggestion.name + '</a>')
                .data('geocoder_result', suggestion)
                .appendTo(this.suggestionContainer)
                .on('click', function (e) {
                    var selectedSuggestion = $(this).data('geocoder_result');
                    self.setUserMarker(new L.LatLng(selectedSuggestion.center.lat, selectedSuggestion.center.lng), selectedSuggestion);
                    self.appendGeocoderMeta(selectedSuggestion);
                    e.preventDefault();
                });
        },

        clearSuggestions: function () {
            this.suggestionContainer.empty();
        },

        setArea: function (areaId) {
            this.selectArea.val(areaId).trigger('sensor-set-area');
        },

        clearGeo: function () {
            this.inputLat.val('').trigger('change');
            this.inputLng.val('').trigger('change');
            this.inputAddress.val('').trigger('change');
            this.inputMeta.val(JSON.stringify(this.persistentMeta));
            this._debugMeta();
        },

        setGeo: function (latLng, address) {
            var self = this;

            this.inputLat.val(latLng.lat);
            this.inputLng.val(latLng.lng);

            if (!address) {
                address = {'name': latLng.toString()};
                self.map.loadingControl.addLoader('reversegeo');
                self.geocoder.reverse(latLng, 1, function (result) {
                    if (result.length > 0) {
                        address = result[0];
                        self.appendGeocoderMeta(result[0]);
                    }
                    self._setAddress(address);
                    self.map.loadingControl.removeLoader('reversegeo');
                }, this);
            } else {
                self._setAddress(address);
            }
        },

        _setAddress: function (data) {
            var name = data.name;
            if (name.length > 150) {
                name = name.substring(0, 140) + '...';
            }
            this.inputAddress.val(name).trigger('change');
            this.getUserMarker().bindPopup(name).openPopup();

            if (this.settings.geoinput_splitted) {
                if (data.properties.address.hasOwnProperty('house_number')) {
                    this.inputNumber.val(data.properties.address.house_number);
                } else {
                    this.inputNumber.val('');
                }

                if (data.properties.address.hasOwnProperty('road')) {
                    this.inputStreet.val(data.properties.address.road);
                } else if (data.properties.address.hasOwnProperty('pedestrian')) {
                    this.inputStreet.val(data.properties.address.pedestrian);
                } else {
                    this.inputStreet.val('');
                }
            }
        },

        appendNearestMeta: function (latLng, areaId) {
            if (!areaId) {
                return null;
            }
            if (this.settings.nearest_service.url) {
                this.map.loadingControl.addLoader('findnearest');
                this._findNearest(latLng, 100);
            }
        },

        _findNearest: function (latLng, distance) {
            var self = this;

            if (distance > 10000) {
                self.map.loadingControl.removeLoader('findnearest');
                return false;
            }

            if (self.debugNearest) {
                self.debugNearest.clearLayers();
            }
            var circle = L.circle(latLng, distance);
            var circleBounds = circle.getBounds();
            var rectangle = L.rectangle(circleBounds, {
                color: 'red',
                weight: 2,
                fillOpacity: 0
            });
            if (self.debugNearest) {
                self.debugNearest.addLayer(rectangle);
                self.map.fitBounds(rectangle.getBounds());
            }

            $.getJSON(self.settings.nearest_service.url,
                {
                    'service': 'WFS',
                    'version': '1.0.0',
                    'request': 'GetFeature',
                    'typeName': self.settings.nearest_service.typeName,
                    'maxFeatures': self.settings.nearest_service.maxFeatures,
                    'srsName': self.settings.nearest_service.srsName,
                    'outputFormat': 'JSON',
                    'cql_filter': '(BBOX(' + self.settings.nearest_service.geometryName + ',' + circleBounds.getWest() + ',' + circleBounds.getSouth() + ',' + circleBounds.getEast() + ',' + circleBounds.getNorth() + ',\'EPSG:4326\'))'
                },
                function (response) {
                    var searchLayer = L.geoJson(response, {
                        pointToLayer: function (feature, latLng) {
                            return L.circleMarker(latLng, {
                                color: 'green'
                            });
                        }
                    });
                    if (self.debugNearest) {
                        self.debugNearest.addLayer(searchLayer);
                    }
                    if (searchLayer.getLayers().length > 0) {
                        var nearest = turf.nearestPoint([latLng.lng, latLng.lat], response);
                        if (self.debugNearest) {
                            var nearestLayer = L.geoJson(nearest, {
                                pointToLayer: function (feature, latLng) {
                                    return L.circleMarker(latLng, {
                                        color: 'yellow'
                                    });
                                }
                            });

                            self.debugNearest.addLayer(nearestLayer);
                        }
                        self.appendMeta(nearest.properties);
                        self.map.loadingControl.removeLoader('findnearest');
                    } else {
                        distance = distance + 100;
                        self._findNearest(latLng, distance);
                    }
                }
            )
        },

        appendGeocoderMeta: function (data) {
            if (this.settings.geocoder === 'Nominatim' || this.settings.geocoder === 'NominatimDetailed') {
                var meta = data.properties.address;
                meta.osm_id = data.osm_id;
                meta.place_id = data.place_id;
                meta.osm_type = data.osm_type;
                this.appendMeta(meta);
            }else if (this.settings.geocoder === 'Geoserver') {
                this.appendMeta(data.properties);
            }
        },

        appendMeta: function (data) {
            var meta = JSON.parse(this.inputMeta.val() || '{}');
            meta = $.extend({}, this.persistentMeta, meta, data);
            this.inputMeta.val(JSON.stringify(meta));
            this._debugMeta();
        },

        _debugMeta: function () {
            if (this.settings.debug_meta_info) {
                var self = this;
                var debugContainer = $('#debug-meta-info');
                if (debugContainer.length === 0) {
                    debugContainer = $('<dl class="dl-horizontal hidden-xs" id="debug-meta-info"></dl>').css({
                        'position': 'fixed',
                        'top': '0',
                        'right': '0',
                        'width': '300px',
                        'background': '#fff',
                        'padding': '10px'
                    }).appendTo($('form#edit'));
                }
                debugContainer.empty();
                var meta = JSON.parse(this.inputMeta.val() || '{}');
                $.each(meta, function (i, v) {
                    var style = '';
                    if ($.inArray(i, self.settings.persistentMetaKeys) > -1){
                        style = ' style="background-color:#ccc"';
                    }
                    debugContainer.append($('<dt'+style+'>' + i + '</dt>'));
                    debugContainer.append($('<dd'+style+'>' + v + '</dd>'));
                });
            }
        },

        getPerimeterIdByPosition: function (latLng) {
            var self = this;

            var id;
            self.perimeters.eachLayer(function (layer) {
                var layerHasPoint = self._layerContains(layer, latLng);
                if (layerHasPoint) {
                    id = layer.feature.properties._id;
                }
            });

            return id;
        },

        getPerimeterLayerById: function (id) {
            var self = this;

            var foundLayer;
            self.perimeters.eachLayer(function (layer) {
                if (parseInt(id) === layer.feature.properties._id) {
                    foundLayer = layer;
                }
            });

            return foundLayer;
        },

        _layerContains: function (layer, latLng) {
            var self = this;

            var layerHasPoint;
            if ($.isFunction(layer.contains) && layer.contains(latLng)) {
                layerHasPoint = layer;
            } else if ($.isFunction(layer.eachLayer)) {
                layer.eachLayer(function (subLayer) {
                    var subLayerHasPoint = self._layerContains(subLayer, latLng);
                    if (subLayerHasPoint) {
                        layerHasPoint = subLayerHasPoint;
                    }
                });
            }

            return layerHasPoint;
        }
    });

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' +
                    pluginName, new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);
