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
            'alertsEndPoint': false,
            'additionalWMSLayers': []
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
                        post.operatorsTree = JSON.parse(plugin.settings.operators);
                        post.groupsTree = JSON.parse(plugin.settings.groups);
                        post.settings = JSON.parse(plugin.settings.settings);
                        post.canReadUsers = capabilities.can_read_user;

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

                        var currentOwnerGroupId = null;
                        var currentOwnerUserId = null;
                        $.each(post.owners, function () {
                            if (this.type === 'group') {
                                currentOwnerGroupId = this.id;
                            }
                            if (this.type === 'user') {
                                currentOwnerUserId = this.id;
                            }
                        })
                        post.currentOwnerGroupId = currentOwnerGroupId;
                        post.currentOwnerUserId = currentOwnerUserId;

                        var renderData = $($.templates(plugin.settings.postTpl).render(post));

                        $.each(post.areas, function () {
                            renderData.find('[data-value="area_id"] option[value="' + this.id + '"]').attr('selected', 'selected');
                        });
                        $.each(post.categories, function () {
                            renderData.find('[data-value="category_id"] option[value="' + this.id + '"]').attr('selected', 'selected');
                        });

                        renderData.find(".select").select2({
                            //width: '100%',
                            //allowClear: true,
                            templateResult: function (item) {
                                var style = item.element ? $(item.element).attr('style') : '';
                                return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
                            }
                        }).on('select2:select', function (e) {
                            if (post.groupsTree.children.length > 0) {
                                if ($(e.currentTarget).attr('id') === groupAssignSelect.attr('id')) {
                                    onChangeGroupAssignSelect($(e.currentTarget).val());
                                }
                                if ($(e.currentTarget).attr('id') === userAssignSelect.attr('id')) {
                                    onChangeUserAssignSelect($(e.currentTarget).val())
                                }
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

                        var userAssignSelect = renderData.find('#user-assign');
                        var groupAssignSelect = renderData.find('#group-assign');
                        var onChangeUserAssignSelect = function (currentUser) {
                            if (parseInt(currentUser) > 0) {
                                var currentGroupSelected = groupAssignSelect.val();
                                $.get(plugin.settings.apiEndPoint + '/operators/' + currentUser, function (data) {
                                    if ($.inArray(parseInt(currentGroupSelected), data.groups) === -1) {
                                        if (data.groups.length > 0) {
                                            groupAssignSelect.val(data.groups[0]).trigger('change');
                                        } else {
                                            groupAssignSelect.val('').trigger('change');
                                        }
                                    }
                                });
                            }
                        };
                        var onChangeGroupAssignSelect = function (currentGroup) {
                            if (parseInt(currentGroup) > 0) {
                                var currentUserSelected = userAssignSelect.val();
                                userAssignSelect.html('').append(new Option('', '', false, false)).trigger('change');
                                $.get(plugin.settings.apiEndPoint + '/groups/' + currentGroup, function (data) {
                                    $.each(data, function () {
                                        var selected = parseInt(currentUserSelected) === parseInt(this.id);
                                        var newOption = new Option(this.name, this.id, selected, selected);
                                        if (userAssignSelect.find('option[value="' + this.id + '"]').length === 0) {
                                            userAssignSelect.append(newOption).trigger('change');
                                        }
                                    });
                                });
                            }
                        };
                        var resetUserAndGroupSelect = function () {
                            userAssignSelect.html('').append(new Option('', '', false, false)).trigger('change');
                            $.each(post.operatorsTree.children, function () {
                                var selected = parseInt(post.currentOwnerUserId) === parseInt(this.id);
                                var newOption = new Option(this.name, this.id, selected, selected);
                                if (userAssignSelect.find('option[value="' + this.id + '"]').length === 0) {
                                    userAssignSelect.append(newOption);
                                }
                            });
                            userAssignSelect.trigger('change');
                            userAssignSelect.trigger('select2:select');
                            if (post.groupsTree.children.length > 0) {
                                groupAssignSelect.html('').append(new Option('', '', false, false)).trigger('change');
                                $.each(post.groupsTree.children, function () {
                                    var selected = parseInt(post.currentOwnerGroupId) === parseInt(this.id);
                                    var newOption = new Option(this.name, this.id, selected, selected);
                                    if (groupAssignSelect.find('option[value="' + this.id + '"]').length === 0) {
                                        groupAssignSelect.append(newOption);
                                    }
                                });
                                groupAssignSelect.trigger('change');
                                groupAssignSelect.trigger('select2:select');
                            }
                        }
                        resetUserAndGroupSelect();

                        plugin.stopLoading();
                        plugin.resultContainer.html(renderData);

                        var messageReceivers = renderData.find('.private_message_receivers');
                        messageReceivers.find('.group_receivers').each(function () {
                            var groupSelector = $(this).parent().find('.group_select');
                            if ($(this).find('li').length === 0) {
                                groupSelector.attr('disabled', 'disabled');
                                groupSelector.next().addClass('text-muted');
                                $('a[data-toggle_group="' + groupSelector.data('toggle_group') + '"]').addClass('hide');
                            } else {
                                groupSelector.on('change', function () {
                                    var group = $('[data-group="' + $(this).data('toggle_group') + '"]');
                                    if ($(this).is(':checked')) {
                                        group.find('input').prop('checked', 'checked');
                                        group.removeClass('hide');
                                    } else {
                                        group.find('input').prop('checked', false);
                                        group.addClass('hide');
                                    }
                                });
                                messageReceivers.find('[data-group="' + groupSelector.data('toggle_group') + '"] input').on('change', function () {
                                    var group = $(this).parents('ul.group_receivers');
                                    var groupSelector = messageReceivers.find('[data-toggle_group="' + group.data('group') + '"]');
                                    var total = group.find('input').length;
                                    var actives = group.find('input:checked').length;
                                    if (actives === 0) {
                                        groupSelector.prop("indeterminate", false);
                                        groupSelector.prop('checked', false).trigger('change');
                                    } else if (actives < total) {
                                        groupSelector.prop("indeterminate", true);
                                    } else {
                                        groupSelector.prop("indeterminate", false);
                                    }

                                });
                            }
                        });
                        if (capabilities.is_approver) {
                            messageReceivers.find('input[data-toggle_group="owners"]').trigger('click');
                        }
                        renderData.find('input[data-value="is_response_proposal"]').on('click', function () {
                            if ($(this).is(':checked')) {
                                messageReceivers.find('input[data-toggle_group="approvers"]').trigger('click');
                            }
                        });
                        if (capabilities.can_respond) {
                            renderData.find('a.create-response-draft').on('click', function (e) {
                                var messageId = $(this).data('message');
                                $.each(post.privateMessages, function () {
                                    if (this.id === messageId) {
                                        renderData.find('.new_response textarea').val(this.text);
                                        renderData.find('[data-target="new_response"]').trigger('click');
                                    }
                                })
                                e.preventDefault();
                            });
                        }

                        var postMap = renderData.find('.post-map');
                        if (postMap.length > 0) {
                            var latLng = [postMap.data('lat'), postMap.data('lng')];
                            var map = new L.Map(postMap[0]);
                            map.scrollWheelZoom.disable();
                            var customIcon = L.MakiMarkers.icon({icon: "star", color: "#f00", size: "l"});
                            var postMarker = new L.Marker(latLng, {icon: customIcon});
                            postMarker.addTo(map);
                            map.setView(latLng, 17);
                            var osmLayer = L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(map);
                            var baseLayers = {
                                'Mappa': osmLayer,
                                'Satellite': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                                    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
                                })
                            };
                            var mapLayers = [];
                            if (plugin.settings.additionalWMSLayers.length > 0) {
                                $.each(plugin.settings.additionalWMSLayers, function(){
                                    mapLayers[this.attribution] = L.tileLayer.wms(this.baseUrl, {
                                        layers: this.layers,
                                        version: this.version,
                                        format: this.format,
                                        transparent: this.transparent,
                                        attribution: this.attribution
                                    });
                                });
                            }
                            L.control.layers(baseLayers, mapLayers, {'position': 'topleft'}).addTo(map);
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

                        var timeIsGreater = function (date, seconds) {
                            if (seconds < 0) return true;
                            if (date < 0) return false;
                            return date > (Math.round(+new Date() / 1000) - seconds);
                        }

                        var runAction = function (action, payload, confirmMessage, onSuccess, onError) {
                            var confirmation = true;
                            if (confirmMessage) {
                                confirmation = confirm(confirmMessage);
                            }
                            if (plugin.actionStarted === false && confirmation) {
                                //console.log(action, payload, confirmMessage);
                                plugin.actionStarted = true;
                                plugin.startLoading();

                                var csrfToken;
                                var tokenNode = document.getElementById('ezxform_token_js');
                                if ( tokenNode ){
                                    csrfToken = tokenNode.getAttribute('title');
                                }

                                $.ajax({
                                    type: 'POST',
                                    async: false, // per poter fermare le multi actions in caso di errore
                                    url: plugin.settings.apiEndPoint + '/actions/' + post.id + '/' + action,
                                    data: JSON.stringify(payload),
                                    headers: {'X-CSRF-TOKEN': csrfToken},
                                    success: function (data) {
                                        plugin.actionStarted = false;
                                        if ($.isFunction(onSuccess)) {
                                            onSuccess(data);
                                        }
                                    },
                                    error: function (data) {
                                        plugin.actionStarted = false;
                                        if ($.isFunction(onError)) {
                                            onError(data);
                                        }
                                    },
                                    contentType: "application/json",
                                    dataType: 'json'
                                });
                            }
                        };

                        var getActionPayload = function (wrapper, parameters) {
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
                                        } else if (valueContainer.is(':checked')) {
                                            payload[this] = valueContainer.val();
                                        }
                                    } else {
                                        payload[this] = valueContainer.val();
                                    }
                                });
                            }

                            return payload;
                        };

                        renderData.find('[data-action]').on('click', function (e) {
                            renderData.find('.modal').modal('hide');
                            var action = $(this).data('action');
                            if (action === 'checkNoteAndFix') {
                                if (timeIsGreater(capabilities.last_private_message_timestamp, post.settings.MinimumIntervalFromLastPrivateMessageToFix)) {
                                    action = 'fix';
                                } else {
                                    renderData.find('#addNoteThenFix').modal('show');
                                    return false;
                                }
                            }
                            var parameters = $(this).data('parameters');
                            var wrapper = $(this).parents('[data-action-wrapper]');
                            var payload = getActionPayload(wrapper, parameters);
                            runAction(action, payload, $(this).data('confirmation'),
                                function (data){
                                    plugin.load(post.id);
                                    if (plugin.settings.alertsEndPoint) {
                                        $('#social_user_alerts').remove();
                                        $.get(plugin.settings.alertsEndPoint, function (data) {
                                            $('header').prepend(data);
                                        });
                                    }
                                },
                                function (data){
                                    plugin.error(data);
                                    plugin.load(post.id);
                                }
                            );
                            e.preventDefault();
                        });

                        renderData.find('[data-actions]').on('click', function (e) {
                            renderData.find('.modal').modal('hide');
                            var actions = $(this).data('actions').split(',');
                            var parameters = $(this).data('parameters');
                            var wrapper = $(this).parents('[data-action-wrapper]');
                            var confirmMessage = $(this).data('confirmation');
                            var hasError = false;
                            $.each(actions, function () {
                                if (!hasError) {
                                    var action = this;
                                    var payload = getActionPayload(wrapper, parameters);
                                    runAction(action, payload, confirmMessage,
                                        null,
                                        function (data) {
                                            hasError = true;
                                            plugin.error(data);
                                            plugin.load(post.id);
                                        }
                                    );
                                }
                            });
                            if (!hasError) {
                                plugin.load(post.id);
                                if (plugin.settings.alertsEndPoint) {
                                    $('#social_user_alerts').remove();
                                    $.get(plugin.settings.alertsEndPoint, function (data) {
                                        $('header').prepend(data);
                                    });
                                }
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
                                        } else {
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

                        renderData.find('.message-triggers a').on('click', function (e) {
                            var target = $(this).data('target');
                            renderData.find('.message-form .' + target).removeClass('hide');
                            renderData.find('.message-triggers').addClass('hide');
                            e.preventDefault();
                        });
                        renderData.find('a.reset-message-form').on('click', function (e) {
                            renderData.find('.message-form .action-form').addClass('hide');
                            renderData.find('.message-triggers').removeClass('hide');
                            e.preventDefault();
                        });

                        renderData.find('.sidebar a.action-trigger').on('click', function (e) {
                            var reverse = $(this).data('reverse');
                            var direct = $(this).text();
                            $(this).text(reverse);
                            $(this).data('reverse', direct);
                            var widget = $(this).parent();
                            widget.find('.widget-content').toggleClass('hide');
                            widget.find('.form-group').toggleClass('hide');
                            if (widget.find('.form-group').hasClass('hide')) {
                                if (widget.find('.form-group').find('#user-assign').length > 0) {
                                    resetUserAndGroupSelect();
                                }
                            }
                            e.preventDefault();
                        });

                        renderData.find('a.message-visibility').on('click', function (e) {
                            var that = $(this);
                            var type = that.data('type');
                            if (that.hasClass('label-default')) {
                                that.removeClass('label-default');
                                that.addClass('label-simple');
                                $('.message-' + type).hide();
                            } else {
                                that.removeClass('label-simple');
                                that.addClass('label-default');
                                $('.message-' + type).show();
                            }
                            e.preventDefault();
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
            } else {
                plugin.showAlert({error_message: data.responseText});
            }

            return plugin;
        },

        showAlert: function (data) {
            var plugin = this;
            var content = $($.templates(this.settings.alertTpl).render(data));
            plugin.removeAlert();
            plugin.alertContainer.prepend(content);

            return plugin;
        },

        removeAlert: function () {
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