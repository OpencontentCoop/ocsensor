;(function ($, window, document, undefined) {

    'use strict';

    var pluginName = 'sensorAddPost',
        defaults = {
            'geocoder': 'Nominatim',
            'geocoder_params': null,
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
            'default_marker': [],
            'map_params': {
                scrollWheelZoom: true,
                loadingControl: true
            }
        };

    function Plugin(element, options) {
        this.element = $(element);
        this.settings = $.extend({}, defaults, options);

        this.loading = false;
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

        if (this.settings.geocoder === "Nominatim" || this.settings.geocoder === '') {
            this.geocoder = L.Control.Geocoder.nominatim();
        } else if (window.XDomainRequest) {
            this.geocoder = L.Control.Geocoder.bing(this.settings.geocoder_params);
        } else if (this.settings.geocoder === "Google") {
            this.geocoder = L.Control.Geocoder.google(this.settings.geocoder_params);
        } else if (this.settings.geocoder === "NominatimDetailed") {
            this.geocoder = L.Control.Geocoder.nominatimDetailed(this.settings.geocoder_params);
        }

        this.selectArea = $('.select-sensor-area');
        this.suggestionContainer = $('#input-results');
        this.inputLat = $('input#latitude');
        this.inputLng = $('input#longitude');
        this.inputAddress = $('input#input-address');
        this.searchButton = $('#input-address-button');
        this.inputMeta = $('textarea.ezcca-sensor_post_meta');

        this.initSearch();
        this.initPerimeters();

        if (this.settings.default_marker) {
            var latLng = new L.LatLng(this.settings.default_marker.coords[0], this.settings.default_marker.coords[1]);
            if (this.settings.default_marker.id === 'default') {
                this.map.setView(latLng, 15);
            } else {
                this.setUserMarker(latLng, this.settings.default_marker.address || null, function () {
                });
            }
        }

        this.debugNearest = this.settings.nearest_service.debug ? L.featureGroup().addTo(this.map) : false;
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
                self.map.loadingControl.addLoader('lc');
                self.map.locate({setView: false, watch: false})
                    .on('locationfound', function (e) {
                        self.map.loadingControl.removeLoader('lc');
                        icon.removeClass('fa-spin');
                        self.setUserMarker(new L.LatLng(e.latitude, e.longitude));
                    })
                    .on('locationerror', function (e) {
                        icon.removeClass('fa-spin');
                        self.map.loadingControl.removeLoader('lc');
                        alert(e.message);
                    });
            });
        },

        initSearch: function () {
            var self = this;

            if (self.settings.geocoder === "NominatimDetailed") {
                self.inputAddress.hide();
                self.inputNumber = $('<input class="form-control" size="20" type="text" placeholder="civico" id="input-number" value="" style="width: 20%;border-left:0">').prependTo(InputAddress.parent());
                self.inputStreet = $('<input class="form-control" size="20" type="text" placeholder="Via, viale, piazza..." id="input-street" value="" style="width: 80%;border-right:0">').prependTo(InputAddress.parent());

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
                }).on('focusout', function (e) {
                    if (!self.loading) {
                        self.searchButton.trigger('click');
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
            }).on('focusout', function (e) {
                if (!self.loading) {
                    self.searchButton.trigger('click');
                }
            });

            self.searchButton.on('click', function (e) {
                self.loading = true;
                self.map.loadingControl.addLoader('gc');
                var query = self.inputAddress.val();
                if (self.settings.geocoder === "NominatimDetailed") {
                    query = {street: self.inputNumber.val() + ' ' + self.inputStreet.val()};
                }

                self.geocoder.geocode(query, function (result) {
                    if (result.length > 0) {
                        self.clearSuggestions();
                        if (result.length > 1) {
                            $.each(result, function (i, o) {
                                self.appendSuggestion(o);
                            });
                        } else {
                            self.setUserMarker(new L.LatLng(result[0].center.lat, result[0].center.lng), result[0].name);
                            self.appendGeocoderMeta(result[0]);
                        }
                        self.map.loadingControl.removeLoader('gc');
                    }
                }, this);
                self.map.loadingControl.removeLoader('gc');
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
                    if (self.getUserMarker() && current !== self.getPerimeterIdByPosition(self.getUserMarker().getLatLng())) {
                        var layer = self.getPerimeterLayerById(current);
                        if (layer) {
                            self.map.fitBounds(layer.getBounds());
                        }
                        self.markers.clearLayers();
                        self.clearGeo();
                    }
                });
            }else{
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

            var areaId = self.getPerimeterIdByPosition(latLng);
            if (self.settings.strict_in_area && !areaId) {
                alert(self.settings.strict_in_area_alert);
                if (self.positionBeforeDrag) {
                    self.setUserMarker(self.positionBeforeDrag, null, function () {
                    });
                }
                return false;
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
            self.map.fitBounds(self.markers.getBounds());
            self.map.setView(latLng, 17);


            if ($.isFunction(cb)) {
                cb.call(context, self, userMarker);
            } else {
                self.clearGeo();
                self.setGeo(latLng, address);
                self.setArea(areaId);
                self.appendNearestMeta(latLng, areaId);
                self.clearSuggestions();
                self.loading = false;
            }
        },

        appendSuggestion: function (suggestion) {
            var self = this;

            var item = $('<li style="cursor:pointer">' + suggestion.name + '</li>')
                .data('geocoder_result', suggestion)
                .appendTo(this.suggestionContainer)
                .on('click', function (e) {
                    var suggestion = $(e.target).data('geocoder_result');
                    self.setUserMarker(new L.LatLng(suggestion.center.lat, suggestion.center.lng), suggestion.name);
                    self.appendGeocoderMeta(suggestion);
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
        },

        setGeo: function (latLng, address) {
            var self = this;

            this.inputLat.val(latLng.lat);
            this.inputLng.val(latLng.lng);

            if (!address) {
                address = latLng.toString();
                self.map.loadingControl.addLoader('sc');
                self.geocoder.reverse(latLng, 1, function (result) {
                    if (result.length > 0) {
                        address = result[0].name;
                        self.appendGeocoderMeta(result[0]);
                    }
                    self._setAddress(address);
                    self.map.loadingControl.removeLoader('sc');
                }, this);
            } else {
                self._setAddress(address);
            }
        },

        _setAddress: function (address) {
            var self = this;

            if (address.length > 150) {
                address = address.substring(0, 140) + '...';
            }
            this.inputAddress.val(address);
            self.getUserMarker().bindPopup(address).openPopup();
        },

        appendNearestMeta: function (latLng, areaId) {
            if (!areaId){
                return null;
            }
            if (this.settings.nearest_service.url) {
                this.map.loadingControl.addLoader('fn');
                this._findNearest(latLng, 100);
            }
        },

        _findNearest: function (latLng, distance) {
            var self = this;

            if (distance > 10000) {
                self.map.loadingControl.removeLoader('fn');
                return false;
            }

            if (self.debugNearest) {
                self.debugNearest.clearLayers();
            }
            var circle = L.circle(latLng, distance);
            var circleBounds = circle.getBounds();
            var rectangle = L.rectangle(circleBounds, {
                color: 'red',
                fillColor: '#f03',
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
                    'cql_filter': '(BBOX('+self.settings.nearest_service.geometryName+',' + circleBounds.getWest() + ',' + circleBounds.getSouth() + ',' + circleBounds.getEast() + ',' + circleBounds.getNorth() + ',\'EPSG:4326\'))'
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
                                    return new L.Marker(latLng, {
                                        icon: L.MakiMarkers.icon({
                                            icon: "circle",
                                            color: '#f00'
                                        })
                                    });
                                }
                            });

                            self.debugNearest.addLayer(nearestLayer);
                        }
                        self.appendMeta(nearest.properties);
                        self.map.loadingControl.removeLoader('fn');
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