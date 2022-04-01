$(document).ready(function () {
    if (Socket && window.Notification) {

        var availableEvents = ['on_create'];

        // control /notification/settings
        var settingsRow = $('#desktop-notification-settings').removeClass('hide');
        var settingsTable = $('table.desktopNotifications');

        settingsRow.find('.notificationPermissionStatus.' + Notification.permission).removeClass('hide');
        if (Notification.permission === 'granted') {
            $.each(availableEvents, function () {
                settingsTable.find('.' + this).removeClass('hide');
            });
        }
        settingsRow.find('a.enableNotificationButton').on('click', function (e) {
            Notification.requestPermission().then(function (permission) {
                settingsRow.find('.notificationPermissionStatus').addClass('hide');
                settingsTable.find('.desktopNotificationType').addClass('hide');
                settingsRow.find('.notificationPermissionStatus.' + permission).removeClass('hide');
                if (permission === 'granted') {
                    $.each(availableEvents, function () {
                        settingsTable.find('.' + this).removeClass('hide');
                    });
                }
            });
            e.preventDefault();
        });
        if ('permissions' in navigator) {
            navigator.permissions.query({name: 'notifications'}).then(function (notificationPerm) {
                notificationPerm.onchange = function () {
                    settingsRow.find('.notificationPermissionStatus').addClass('hide');
                    settingsTable.find('.desktopNotificationType').addClass('hide');
                    settingsRow.find('.notificationPermissionStatus.' + Notification.permission).removeClass('hide');
                    if (Notification.permission === 'granted') {
                        $.each(availableEvents, function () {
                            settingsTable.find('.' + this).removeClass('hide');
                        });
                    }
                };
            });
        }
        var sw = '/extension/ocsensor/design/sensor/javascript/sw.js';
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register(sw);
        }

        if (Notification.permission === 'granted') {
            $.getJSON('/api/sensor_gui/users/current', function (user) {
                $.each(availableEvents, function (){
                    Socket.on(this, function (data) {
                        if (!(data.creator === user.id) &&
                            (
                                $.inArray(user.id, data.users) > -1
                                || data.groups.filter(function (n) {
                                    return user.groups.indexOf(n) !== -1;
                                }).length > 0
                            )
                        ) {
                            var title = $('title').text();
                            var icon = $('a.navbar-brand img').attr('src');
                            var options = {
                                body: '[' + data.id + '] ' + $.sensorTranslate.translate('New issue'),
                                icon: icon,
                                badge: icon,
                                data: data,
                                silent: false,
                                tag: 'post-' + data.id,
                                requireInteraction: false
                            };

                            if ('serviceWorker' in navigator) {
                                navigator.serviceWorker.getRegistration(sw).then((reg) => {
                                    options.requireInteraction = true;
                                    reg.showNotification(title, options);
                                });
                            } else {
                                var notification = new Notification(title, options);
                                notification.onclick = function (event) {
                                    var pageURL = $.opendataTools.settings('accessPath')+'/sensor/posts/' + this.data.id;
                                    event.preventDefault();
                                    location.href = pageURL;
                                }
                            }
                        }
                    })
                })
            });
        }
    }
});
