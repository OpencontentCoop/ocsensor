self.addEventListener('notificationclick', (event) => {
    var pageURL = $.opendataTools.settings('accessPath')+'/sensor/posts/' + event.notification.data.id;
    const urlToOpen = new URL(pageURL, self.location.origin).href;
    const promiseChain = clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    })
        .then((windowClients) => {
            let matchingClient = null;
            for (let i = 0; i < windowClients.length; i++) {
                const windowClient = windowClients[i];
                if (windowClient.url === urlToOpen) {
                    matchingClient = windowClient;
                    break;
                }
            }
            if (matchingClient) {
                return matchingClient.focus();
            } else {
                return clients.openWindow(urlToOpen);
            }
        });
    event.waitUntil(promiseChain);
    event.notification.close();
}, false);
