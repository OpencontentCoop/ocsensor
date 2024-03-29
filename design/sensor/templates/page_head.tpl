<head>

    <meta charset="utf-8">

    <title>{$social_pagedata.site_title}</title>

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {ezcss_load( array(
        'opensans.css',
        'animate.css',
        'style.css',
        'custom.css',
        'font-awesome.min.css',
        'font-awesome-animation.min.css',
        'leaflet.0.7.2.css',
        'Control.Loading.css',
        'jquery.fileupload.css',
        'summernote/summernote-lite.css',
        'alpaca.min.css',
        'alpaca-custom.css',
        'sensor_add_post.css',
        'debug.css',
        'websitetoolbar.css'
    ) )}

    {ezscript_load(array(
        'modernizr.custom.48287.js',
        'sensor::translations',
        'ezjsc::jquery',
        'bootstrap.min.js',
        'isotope/jquery.isotope.min.js',
        'jquery.ui.totop.js',
        'easing.js',
        'wow.min.js',        
        'restart_theme.js',
        'collapser.js',
        'placeholders.min.js',
        'leaflet.0.7.2.js',
        'leaflet.activearea.js',
        'Leaflet.MakiMarkers.js',
        'Control.Geocoder.js',
        'Control.Loading.js',
        'jquery.ocdrawmap.js',
        'wise-leaflet-pip.js',
        'turf.min.js',
        'jquery.opendataTools.js',
        'handlebars.min.js',
        'typeahead.bundle.js',
        'ezjsc::jqueryUI',
        'ezjsc::jqueryio',
        'jquery.opendataTools.js',
        'jsrender.js', 'jsrender.helpers.js',
        'alpaca.js',
        'moment-with-locales.min.js',
        'fields/Recaptcha.js',
        'jquery.opendatabrowse.js',
        'jquery.opendataform.js',
        'jquery.fileupload.js',
        'summernote/summernote-lite.js',
        'jsrender.js',
        'jsrender.helpers.js',
        'jquery.sensor_add_post.js'
    ))}
    
    <!--[if lt IE 9]>
    <script src="{'javascript/respond.min.js'|ezdesign(no)}"></script>
    <![endif]-->

    {if $social_pagedata.head_images["apple-touch-icon-114x114-precomposed"]}
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{$social_pagedata.head_images["apple-touch-icon-114x114-precomposed"]}" />
    {/if}
    {if $social_pagedata.head_images["apple-touch-icon-72x72-precomposed"]}
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{$social_pagedata.head_images["apple-touch-icon-72x72-precomposed"]}" />
    {/if}
    {if $social_pagedata.head_images["apple-touch-icon-57x57-precomposed"]}
    <link rel="apple-touch-icon-precomposed" href="{$social_pagedata.head_images["apple-touch-icon-57x57-precomposed"]}" />
    {/if}
    {if $social_pagedata.head_images["favicon"]}
        <link rel="icon" href="{$social_pagedata.head_images["favicon"]}">
    {else}
        {def $favicon = openpaini('GeneralSettings','favicon', 'favicon.ico')}
        {def $favicon_src = openpaini('GeneralSettings','favicon_src', 'ezimage')}
        {if $favicon_src|eq('ezimage')}
            <link rel="icon" href="{$favicon|ezimage(no)}" type="image/x-icon" />
        {else}
            <link rel="icon" href="{$favicon}" type="image/x-icon" />
        {/if}
    {/if}
</head>
