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
            }
        };

    function Plugin(element, options) {
        this.element = $(element);
        this.settings = $.extend({}, defaults, options);
        this.settings.geoinput_splitted = false;

        this.positionBeforeDrag = false;

        this.map = new L.Map(
            this.element.attr('id'),
            this.settings.map_params
        ).setActiveArea('viewport');
        L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);
        this.initMapEvents();

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
        this._debugMeta();

        if (this.settings.geoinput_splitted) {
            this.inputAddress.hide();
            this.inputNumber = $('<input class="form-control" size="20" type="text" placeholder="civico" id="input-number" value="" style="width: 20%;border-left:0">').prependTo(this.inputAddress.parent());
            this.inputStreet = $('<input class="form-control" size="20" type="text" placeholder="Via, viale, piazza..." id="input-street" value="" style="width: 80%;border-right:0">').prependTo(this.inputAddress.parent());
        }
        this.initSearch();

        this.initPerimeters();

        if (this.settings.default_marker) {
            var latLng = new L.LatLng(this.settings.default_marker.coords[0], this.settings.default_marker.coords[1]);
            var self = this;
            this.setUserMarker(latLng, this.settings.default_marker.address || null, function () {
            });
        } else if (typeof this.settings.center_map === 'object') {
            this.map.setView(this.settings.center_map, 15);
        } else if (this.perimeters.getLayers().length === 0 && this.globalBoundingBox) {
            this.map.setView(new L.LatLng(0, 0), 10);
            this.map.fitBounds(this.globalBoundingBox);
        }

        this.debugNearest = this.settings.nearest_service.debug ? L.featureGroup().addTo(this.map) : false;
        this.debugGeocoder = this.settings.debug_geocoder ? L.featureGroup().addTo(this.map) : false;
    }

    $.extend(Plugin.prototype, {

        initMapEvents: function () {
            var self = this;

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
                var icon = $(e.currentTarget).find('i');
                icon.addClass('fa-spin');
                self.map.loadingControl.addLoader('mylocation');
                self.map.locate({setView: false, watch: false})
                    .on('locationfound', function (e) {
                        self.map.loadingControl.removeLoader('mylocation');
                        icon.removeClass('fa-spin');
                        self.setUserMarker(new L.LatLng(e.latitude, e.longitude));
                    })
                    .on('locationerror', function (e) {
                        icon.removeClass('fa-spin');
                        self.map.loadingControl.removeLoader('mylocation');
                        alert(e.message);
                    });
            });
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

            self.inputAddress.on('click', function (e) {
                //$(this).select();
            }).on('keypress', function (e) {
                if (e.which === 13) {
                    self.searchButton.trigger('click');
                    e.preventDefault();
                }
            });

            self.searchButton.on('click', function (e) {
                self.map.loadingControl.addLoader('inputsearch');
                var query = self.inputAddress.val();
                if (self.settings.geoinput_splitted) {
                    query = {street: self.inputNumber.val() + ' ' + self.inputStreet.val()};
                }

                self.clearSuggestions();
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
                    if (self.settings.strict_in_area) {
                        results = [];
                        $.each(response, function (i, o) {
                            if (self.getPerimeterIdByPosition(new L.LatLng(o.center.lat, o.center.lng))) {
                                results.push(o);
                            }
                        });
                    }
                    self.map.loadingControl.removeLoader('inputsearch');
                    if (results.length > 0) {

                        // deduplicate suggestions
                        var suggestions = [];
                        $.each(results, function (i, o) {
                            var name = o.name;
                            var alreadySuggested = $.grep(suggestions, function(e){ return e.name === name; });
                            if (alreadySuggested.length === 0) {
                                suggestions.push(o);
                            }
                        });

                        if (suggestions.length > 1) {
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
            });
        },

        initPerimeters: function () {
            var self = this;

            $('[data-geojson]').each(function () {
                var item = $(this);
                $.addGeoJSONLayer(
                    item.data('geojson'),
                    self.map,
                    self.perimeters, null, {
                        color: item.data('color'),
                        weight: 2,
                        opacity: 0.4,
                        fillOpacity: 0.2
                    },
                    null,
                    function (feature, layer) {
                        feature.properties._id = item.data('id');
                        layer.on('click', function (e) {
                            self.setUserMarker(e.latlng);
                        });
                    }
                );
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
                        self.setUserMarker(self.positionBeforeDrag, null, function () {
                        });
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
            this.selectArea.val(areaId);
        },

        clearGeo: function () {
            this.inputLat.val('');
            this.inputLng.val('');
            this.inputAddress.val('');
            this.inputMeta.val('');
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
            this.inputAddress.val(name);
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
            }
        },

        appendMeta: function (data) {
            var meta = JSON.parse(this.inputMeta.val() || '{}');
            meta = $.extend({}, meta, data);
            this.inputMeta.val(JSON.stringify(meta));
            this._debugMeta();
        },

        _debugMeta: function () {
            if (this.settings.debug_meta_info) {
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
                    debugContainer.append($('<dt>' + i + '</dt>'));
                    debugContainer.append($('<dd>' + v + '</dd>'));
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