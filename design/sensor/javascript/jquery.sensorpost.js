;(function ($, window, document, undefined) {

    'use strict';

    var pluginName = 'sensorPost',
        defaults = {
            'apiEndPoint': '/',
            'sensorPostDefinition': '',
            'currentUserId': 0,
            'areas': '',
            'categories': '',
            'settings': '',
            'alertContainerSelector': 'header',
            'spinnerTpl': '#tpl-spinner',
            'postTpl': '#tpl-post',
            'alertTpl': '#tpl-alerts',
            'onRemove': null,
            'alertsEndPoint': false
        };

    function Plugin(element, options, postId) {
        this.settings = $.extend({}, defaults, options);
        this.resultContainer = $(element);
        this.alertContainer = $(this.settings.alertContainerSelector);
        this.actionStarted = false;
        this.spinner = $($.templates(this.settings.spinnerTpl).render({}));
        $.views.helpers($.opendataTools.helpers);

        $(document).on('show.bs.collapse', '#collapseConversation', function (e) {
            Cookies.set('collapseConversation', 1);
        });
        $(document).on('hide.bs.collapse', '#collapseConversation', function (e) {
            Cookies.set('collapseConversation', 0);
        });

        if (parseInt(postId) > 0) {
            this.removeAlert().startLoading().load(postId);
        }
    }

    $.extend(Plugin.prototype, {
        load: function (postId) {
            var plugin = this;
            $.getJSON(plugin.settings.apiEndPoint + '/users/current/capabilities/' + postId, function (userCapabilities) {

                var capabilities = [];
                $.each(userCapabilities, function () {
                    capabilities[this.identifier] = this.grant;
                });

                if (capabilities.can_read) {
                    $.getJSON(plugin.settings.apiEndPoint + '/posts/' + postId, function (post) {
                        post.accessPath = $.opendataTools.settings('accessPath');
                        post.sensorPost = JSON.parse(plugin.settings.sensorPostDefinition);
                        post.currentUserId = plugin.settings.currentUserId;
                        post.capabilities = capabilities;
                        post.areasTree = JSON.parse(plugin.settings.areas);
                        post.categoriesTree = JSON.parse(plugin.settings.categories);
                        post.settings = JSON.parse(plugin.settings.settings);

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

                        post.privateMessageWrapperClass = parseInt(Cookies.get('collapseConversation')) === 1 ? 'in' : '';

                        var renderData = $($.templates(plugin.settings.postTpl).render(post));

                        $.each(post.areas, function () {
                            renderData.find('[data-value="area_id"] option[value="' + this.id + '"]').attr('selected', 'selected');
                        });
                        $.each(post.categories, function () {
                            renderData.find('[data-value="category_id"] option[value="' + this.id + '"]').attr('selected', 'selected');
                        });
                        renderData.find(".select").select2({
                            width: '100%',
                            templateResult: function (item) {
                                var style = item.element ? $(item.element).attr('style') : '';
                                return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
                            }
                        });
                        renderData.find(".remote-select").each(function () {
                            var that = $(this);
                            that.select2({
                                width: '100%',
                                ajax: {
                                    url: plugin.settings.apiEndPoint + '/' + that.data('remote'),
                                    dataType: 'json',
                                    delay: 250,
                                    data: function (params) {
                                        return {
                                            query: params.term,
                                            limit: 50
                                        };
                                    },
                                    processResults: function (data, params) {

                                        var results = [];
                                        $.each(data.items, function () {
                                            var text = this.name;
                                            if (this.description) {
                                                text += ' (' + this.description + ')';
                                            }
                                            results.push({
                                                id: this.id,
                                                text: text
                                            });
                                        });
                                        return {
                                            results: results
                                        };
                                    },
                                    cache: true
                                },
                                minimumInputLength: 4
                            });
                        });

                        plugin.stopLoading();
                        plugin.resultContainer.html(renderData);

                        var postMap = renderData.find('.post-map');
                        if (postMap.length > 0) {
                            var latLng = [postMap.data('lat'), postMap.data('lng')];
                            var map = new L.Map(postMap[0]);
                            map.scrollWheelZoom.disable();
                            var customIcon = L.MakiMarkers.icon({icon: "star", color: "#f00", size: "l"});
                            var postMarker = new L.marker(latLng, {icon: customIcon});
                            postMarker.addTo(map);
                            map.setView(latLng, 18);
                            L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(map);
                        }

                        renderData.find("a.edit-message").on('click', function (e) {
                            var id = $(this).data('message-id');
                            renderData.find('#edit-message-' + id).toggle();
                            renderData.find('#view-message-' + id).toggle();
                            e.preventDefault();
                        });

                        renderData.find('[data-upload]').each(function () {
                            var $element = $(this);
                            var $buttonContainer = $element.find('.upload-button-container');
                            var $spinner = $element.find('.upload-button-spinner');
                            $element.find('.upload').fileupload({
                                dropZone: $element,
                                formData: function (form) {
                                    return form.serializeArray();
                                },
                                url: plugin.settings.apiEndPoint + '/upload/' + post.id + '/' + $element.data('upload'),
                                autoUpload: true,
                                dataType: 'json',
                                submit: function (e, data) {
                                    $buttonContainer.hide();
                                    $spinner.show();
                                },
                                error: function (data) {
                                    plugin.error(data);
                                    plugin.load(post.id);
                                },
                                done: function (e, data) {
                                    plugin.load(post.id);
                                }
                            });
                        });

                        renderData.find('[data-action]').on('click', function (e) {
                            var action = $(this).data('action');
                            var parameters = $(this).data('parameters');
                            var wrapper = $(this).parents('[data-action-wrapper]');
                            var payload = {};
                            if (typeof parameters !== 'undefined') {
                                $.each(parameters.split(','), function () {
                                    var valueContainer = wrapper.find('[data-value="' + this + '"]');
                                    if (valueContainer.is('select')) {
                                        payload[this] = valueContainer.find('option:selected').val();
                                    } else if (valueContainer.attr('type') === 'checkbox') {
                                        if (valueContainer.length > 1) {
                                            var values = [];
                                            valueContainer.filter(':checked').each(function () {
                                                values.push($(this).val());
                                            });
                                            payload[this] = values;
                                        } else {
                                            payload[this] = valueContainer.is(':checked') ? valueContainer.val() : 0;
                                        }
                                    } else {
                                        payload[this] = valueContainer.val();
                                    }
                                });
                            }
                            var confirmation = true;
                            if ($(this).data('confirmation')) {
                                confirmation = confirm($(this).data('confirmation'));
                            }
                            if (plugin.actionStarted === false && confirmation) {
                                plugin.actionStarted = true;
                                plugin.startLoading();
                                $.ajax({
                                    type: 'POST',
                                    url: plugin.settings.apiEndPoint + '/actions/' + post.id + '/' + action,
                                    data: JSON.stringify(payload),
                                    success: function (data) {
                                        plugin.load(post.id);
                                        plugin.actionStarted = false;
                                        if (plugin.settings.alertsEndPoint) {
                                            $('#social_user_alerts').remove();
                                            $.get(plugin.settings.alertsEndPoint, function (data) {
                                                $('header').prepend(data);
                                            });
                                        }
                                    },
                                    error: function (data) {
                                        plugin.actionStarted = false;
                                        plugin.error(data);
                                        plugin.load(post.id);
                                    },
                                    contentType: "application/json",
                                    dataType: 'json'
                                });
                            }
                            e.preventDefault();
                        });

                        renderData.find('[data-remove]').on('click', function (e) {
                            var confirmation = true;
                            if ($(this).data('confirmation')) {
                                confirmation = confirm($(this).data('confirmation'));
                            }
                            if (plugin.actionStarted === false && confirmation) {
                                plugin.actionStarted = true;
                                plugin.startLoading();
                                $.ajax({
                                    type: 'DELETE',
                                    url: plugin.settings.apiEndPoint + '/posts/' + $(this).data('post'),
                                    success: function () {
                                        if ($.isFunction(plugin.settings.onRemove)) {
                                            plugin.settings.onRemove.call(plugin.resultContainer.context, plugin);
                                        }else {
                                            window.location = $.opendataTools.settings('accessPath') + '/sensor/posts';
                                        }
                                    },
                                    error: function (data) {
                                        plugin.actionStarted = false;
                                        plugin.error(data);
                                        plugin.load(post.id);
                                    },
                                    contentType: "application/json",
                                    dataType: 'json'
                                });
                            }
                        });

                        //temp
                        renderData.find('#current-post-id').on('click', function () {
                            plugin.removeAlert().startLoading().load($(this).text());
                        });
                    });
                }
            });

            return plugin;
        },

        error: function (data) {
            var plugin = this;
            if (data.responseJSON) {
                plugin.showAlert(data.responseJSON);
            }else{
                plugin.showAlert({error_message: data.responseText});
            }

            return plugin;
        },

        showAlert: function(data){
            var plugin = this;
            var content = $($.templates(this.settings.alertTpl).render(data));
            var alerts = plugin.alertContainer.find('#social_user_alerts');
            if (alerts.length === 0) {
                plugin.alertContainer.prepend(content);
            }else{
                alerts.find('.errorList').append(content.find('.errorList').html());
            }
            return plugin;
        },

        removeAlert: function(){
            var plugin = this;
            plugin.alertContainer.find('#social_user_alerts').remove();
            return plugin;
        },

        startLoading: function () {
            var plugin = this;
            plugin.resultContainer.css({'opacity': '0.3'}).append(plugin.spinner.css({'height': plugin.resultContainer.height()}));

            return plugin;
        },

        stopLoading: function () {
            var plugin = this;
            plugin.resultContainer.css({'opacity': '1'});

            return plugin;
        }
    });

    $.fn[pluginName] = function (options, postId) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' +
                    pluginName, new Plugin(this, options, postId));
            }
        });
    };

})(jQuery, window, document);