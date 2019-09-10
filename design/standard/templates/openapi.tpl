<html>
<head>
    <title>OpenSegnalazioni API Docs</title>
    <style type="text/css">
        @import url({"stylesheets/swagger-ui.css"|ezdesign});
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src={'javascript/swagger-ui-bundle.js'|ezdesign}></script>
    <script>
        window.onload = function () {ldelim}
            window.ui = SwaggerUIBundle({ldelim}
                url: {"/sensor/openapi.json"|ezurl(yes,full)},
                dom_id: '#swagger-ui',
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIBundle.SwaggerUIStandalonePreset
                ]
                {rdelim});
            {rdelim}
    </script>
</body>
</html>
