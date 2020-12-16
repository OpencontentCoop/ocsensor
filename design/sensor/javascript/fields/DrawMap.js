(function ($) {

    var Alpaca = $.alpaca;

    Alpaca.Fields.DrawMap = Alpaca.Fields.ObjectField.extend(
        /**
         * @lends Alpaca.Fields.DrawMap.prototype
         */
        {
            /**
             * @see Alpaca.Fields.ObjectField#getFieldType
             */
            getFieldType: function () {
                return 'drawmap';
            },

            /**
             * @private
             * @see Alpaca.Fields.ObjectField#setup
             */
            setup: function () {
                this.base();

                if (!this.isDisplayOnly()) {
                    this.schema = {
                        'type': 'object',
                        'properties': {
                            'type': {
                                'title': this.options.i18n.type,
                                'enum': ['geojson', 'osm']
                            },
                            'source': {
                                'title': this.options.i18n.source,
                                'type': 'string'
                            },
                            'color': {
                                'title': this.options.i18n.color,
                                'type': 'string'
                            },
                            'geo_json': {
                                'title': this.options.i18n.geo_json,
                                'type': 'string'
                            },
                        }
                    };
                }

                Alpaca.merge(this.options, {
                    'fields': {
                        'geo_json': {
                            'type': this.isDisplayOnly() ? 'hidden' : 'textarea'
                        },
                        'color': {
                            'type': this.isDisplayOnly() ? 'hidden' : 'colorpicker'
                        },
                        'type': {
                            'type': this.isDisplayOnly() ? 'hidden' : 'select',
                            'hideNone': true,
                            'optionLabels': [this.options.i18n.types['geojson'], this.options.i18n.types['osm']],
                        }
                    },
                    'i18n': {
                        'type': 'Type',
                        'color': 'Color',
                        'source': 'Source',
                        'geo_json': 'Data',
                        'types': {
                            'geojson': 'GeoJSON',
                            'osm': 'Openstreet map full xml',
                        }
                    }
                });
            },

            /**
             * @see Alpaca.Field#afterRenderContainer
             */
            afterRenderContainer: function (model, callback) {

                var self = this;

                this.base(model, function () {

                    var container = self.getContainerEl();
                    var fieldName = self.name;
                    var mapContainer = $('<div id="osm-' + self.getId() + '" style="width: 100%; min-width:200px; max-width:100%; height: 400px; margin-top: 2px;"></div>').prependTo(container);
                    var map = new L.Map(mapContainer[0], {
                        loadingControl: true,
                        scrollWheelZoom: !self.isDisplayOnly(),
                        center: new L.LatLng(0, 0),
                        zoom: 13
                    });
                    L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);


                    L.Icon.Default.imagePath = '/extension/ocmap/design/standard/images';

                    L.Circle.include({
                        toGeoJSON: function () {
                            var feature = {
                                feature: {
                                    type: 'Feature',
                                    properties: {
                                        radius: this.getRadius(),
                                        type: 'Circle'
                                    },
                                    geometry: {}
                                }
                            };
                            return L.GeoJSON.getFeature(feature, {
                                type: 'Point',
                                coordinates: L.GeoJSON.latLngToCoords(this.getLatLng()),
                                properties: {radius: this.getRadius()}
                            });
                        }
                    });

                    L.CircleMarker.include({
                        toGeoJSON: function () {
                            var feature = {
                                feature: {
                                    type: 'Feature',
                                    properties: {
                                        radius: this.getRadius(),
                                        type: 'CircleMarker'
                                    },
                                    geometry: {}
                                }
                            };
                            return L.GeoJSON.getFeature(feature, {
                                type: 'Point',
                                coordinates: L.GeoJSON.latLngToCoords(this.getLatLng()),
                                properties: {radius: this.getRadius()}
                            });
                        }
                    });

                    var drawnItems = L.featureGroup().addTo(map);

                    window.setTimeout(function () {
                        map.invalidateSize(false);
                        if (self.data && self.data.geo_json) {
                            var json = JSON.parse(self.data.geo_json);
                            self.addGeoJSONLayer(json, map, drawnItems, self.data.type, {color: self.data.color});
                            if (drawnItems.getLayers().length > 0) {
                                map.fitBounds(drawnItems.getBounds());
                            }
                        }
                    }, self.isDisplayOnly() ? 500 : 1500);

                    if (!self.isDisplayOnly()) {
                        var sourceInput = container.find('[name="'+fieldName+'_source"]');
                        var dataInput = container.find('[name="'+fieldName+'_geo_json"]');
                        var typeInput = container.find('[name="'+fieldName+'_type"]');
                        var colorInput = container.find('[name="'+fieldName+'_color"]');

                        var storeData = function () {
                            var json = drawnItems.toGeoJSON();
                            dataInput.val(JSON.stringify(json));
                        };
                        map.addControl(new L.Control.Draw({
                            edit: {
                                featureGroup: drawnItems,
                                poly: {
                                    allowIntersection: false
                                }
                            },
                            draw: {
                                polygon: {
                                    allowIntersection: false,
                                    showArea: true
                                }
                            }
                        }));
                        map.on(L.Draw.Event.CREATED, function (event) {
                            var layer = event.layer;
                            layer.options.color = colorInput.val();
                            drawnItems.addLayer(layer);
                            storeData();
                        });
                        map.on(L.Draw.Event.DELETED, function (event) {
                            var data = drawnItems.toGeoJSON();
                            storeData();
                        });

                        var inputGroup = $('<div class="input-group"></div>');
                        var inputGroupButtonContainer = $('<div class="input-group-btn"></div>');
                        sourceInput.wrap(inputGroup);
                        var loadSourceButton = $('<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span></button>').appendTo(inputGroupButtonContainer);
                        var resetButton = $('<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-remove"></span></button>').appendTo(inputGroupButtonContainer);
                        inputGroupButtonContainer.appendTo(sourceInput.parent());

                        resetButton.on('click', function (e) {
                            drawnItems.clearLayers();
                            sourceInput.val('');
                            typeInput.prop("selectedIndex", 0);
                            dataInput.val('');
                            storeData();
                            e.preventDefault();
                        });

                        loadSourceButton.on('click', function (e) {
                            self.loadSource(sourceInput.val(), typeInput.val(), function (geoJSON) {
                                drawnItems.clearLayers();
                                self.addGeoJSONLayer(geoJSON, map, drawnItems, typeInput.val(), {color: colorInput.val()});
                                storeData();
                            });
                            e.preventDefault();
                        });
                    }else{
                        $('[data-alpaca-container-item-name="'+fieldName+'_geo_json"]').hide();
                        $('[data-alpaca-container-item-name="'+fieldName+'_type"]').hide();
                        $('[data-alpaca-container-item-name="'+fieldName+'_color"]').hide();
                    }
                    callback();
                });
            },

            addGeoJSONLayer: function (json, map, featureGroup, type, options, pointToLayer, onEachFeature) {
                var geoJSONLayer = L.geoJson(json, {
                    pointToLayer: $.isFunction(pointToLayer) ? function (feature, latlng) {
                        return pointToLayer(feature, latlng)
                    } : function (feature, latlng) {
                        var geometry = feature.type === 'Feature' ? feature.geometry : feature;
                        if (geometry.type === 'Point') {
                            if (feature.properties.radius) {
                                if (feature.properties.type === 'CircleMarker')
                                    return new L.CircleMarker(latlng, feature.properties.radius);
                                if (feature.properties.type === 'Circle')
                                    return new L.Circle(latlng, feature.properties.radius);
                            } else {
                                var customIconProperties = {icon: "circle"};
                                if (options.color) {
                                    customIconProperties.color = options.color;
                                }
                                var customIcon = L.MakiMarkers.icon(customIconProperties);
                                return new L.Marker(latlng, {icon: customIcon});
                            }
                        }
                    },
                    onEachFeature: $.isFunction(onEachFeature) ? function (feature, layer) {
                        return onEachFeature(feature, layer)
                    } : function (feature, layer) {
                        if (feature.properties.name) {
                            layer.bindPopup(feature.properties.name);
                        }
                    }
                });
                geoJSONLayer.eachLayer(function (l) {
                    if (l.options) {
                        l.options = $.extend({}, l.options, options);
                    } else if (typeof l.getLayers === 'function') {
                        $.each(l.getLayers(), function () {
                            this.options = $.extend({}, this.options, options);
                            if ($.isFunction(onEachFeature)) {
                                this.options.onEachFeature = function (feature, layer) {
                                    return onEachFeature(feature, layer)
                                };
                            }
                        });
                    }
                    featureGroup.addLayer(l);
                });
                if (featureGroup.getLayers().length > 0) {
                    map.fitBounds(featureGroup.getBounds());
                }
                return geoJSONLayer;
            },

            loadSource: function (url, type, cb, context) {
                switch (type) {
                    case 'osm':
                        $.ajax({
                            url: url,
                            dataType: "xml",
                            success: function (xml) {
                                var layer = new L.OSMData.DataLayer(xml);
                                var geoJSON = layer.toGeoJSON();
                                var lines = [];
                                $.each(geoJSON.features, function () {
                                    if (this.type === 'Feature'
                                        && (this.geometry.type === 'LineString' || this.geometry.type === 'MultiLineString')){
                                        lines.push(this);
                                    }
                                });
                                var polygon = turf.polygonize(turf.featureCollection(lines));
                                if ($.isFunction(cb)) {
                                    cb.call(context, polygon);
                                    return true;
                                }
                            }
                        });
                        break;

                    case 'geojson':
                        $.ajax({
                            url: url,
                            dataType: "json",
                            success: function (geoJSON) {
                                if ($.isFunction(cb)) {
                                    cb.call(context, geoJSON);
                                    return true;
                                }
                            }
                        });
                        break;
                }
            }

        });

    Alpaca.registerFieldClass('drawmap', Alpaca.Fields.DrawMap);

})(jQuery);
