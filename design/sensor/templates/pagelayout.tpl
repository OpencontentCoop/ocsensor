{def $user_hash  = concat( $current_user.role_id_list|implode( ',' ), ',', $current_user.limited_assignment_value_list|implode( ',' ) )}
{def $has_container = cond(is_set($module_result.content_info.persistent_variable.has_container), true(), false())}
<!doctype html>
<html class="no-js" lang="en">

<head>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">

    <meta charset="utf-8">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {ezcss_load( array(
        'animate.css',
        'style.css',
        'custom.css',
        'font-awesome.min.css',
        'font-awesome-animation.min.css',
        'leaflet.0.7.2.css',
        'Control.Loading.css',
        'jquery.fileupload.css',
        'alpaca.min.css',
        'alpaca-custom.css',
        'sensor_add_post.css',
        'summernote/summernote-lite.css',
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
        'moment-with-locales.min.js',
        'alpaca.js',
        concat('https://www.google.com/recaptcha/api.js?hl=', fetch( 'content', 'locale' ).country_code|downcase),
        'fields/Recaptcha.js',
        'jquery.opendatabrowse.js',
        'jquery.opendataform.js',
        'jquery.fileupload.js',
        'summernote/summernote-lite.js',
        'jquery.sensor_add_post.js',
        'jquery.sensor_notifications.js'
    ))}

    {if and(fetch('user', 'has_access_to', hash('module','sensor','function','manage')), sensor_settings('SocketIsEnabled'))}
        <script src="{sensor_settings('SocketUri')}/socket.io/socket.io.js"></script>
        <script>var Socket = typeof io === 'undefined' ? null : io({if sensor_settings('SocketPath')}{ldelim}path:'{sensor_settings('SocketPath')}/socket.io'{rdelim}{else}'{sensor_settings('SocketUri')}'{/if});</script>
    {else}
        <script>var Socket = null;</script>
    {/if}

{cache-block expiry=86400 ignore_content_expiry keys=array( $current_user.contentobject_id, $access_type.name )}
{def $social_pagedata = social_pagedata()}
    <!--[if lt IE 9]>
    <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <script src="{'javascript/respond.min.js'|ezdesign(no)}"></script>
    <![endif]-->

    <title>{$social_pagedata.site_title}</title>

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
    <script>
        {def $current_language = ezini('RegionalSettings', 'Locale')}
        {def $current_locale = fetch( 'content', 'locale' , hash( 'locale_code', $current_language ))}
        {def $moment_language = $current_locale.http_locale_code|explode('-')[0]|downcase()|extract_left( 2 )}
        $(document).ready(function (){ldelim}
            $.opendataTools.settings('accessPath', "{''|ezurl(no,full)}");
            $.opendataTools.settings('language', "{$current_language}");
            $.opendataTools.settings('languages', ['{ezini('RegionalSettings','SiteLanguageList')|implode("','")}']);
            $.opendataTools.settings('locale', "{$moment_language}");
            moment.locale('{$moment_language}');
            $.ajaxSetup({ldelim}
                headers : {ldelim}
                    'X-SiteAccess' : '{$access_type.name}'
                {rdelim}
            {rdelim});
        {rdelim})
    </script>
</head>


{include uri='design:page_head_google_tag_manager.tpl'}
{include uri='design:page_head_google-site-verification.tpl'}

<body>
    {include uri='design:page_header.tpl'}

{/cache-block}

{def $cache_keys = array(
    cond(is_set($module_result.content_info.persistent_variable.sensor_home), 'is_home', 'not_is_home'),
    cond(is_set($module_result.content_info.persistent_variable.sensor_post_container), 'is_post_container', 'not_is_post_container'),
    cond(fetch('user', 'has_access_to', hash('module','sensor','function','manage')), 'can_manage', 'not_can_manage'),
    cond(sensor_settings().ShowSmartGui, 'has_smart_gui', 'not_has_smart_gui'),
    sensor_edit_category_access_cache_key(),
    cond(fetch('user', 'has_access_to', hash('module','sensor','function','behalf')), 'can_behalf', 'not_can_behalf')
)}
{cache-block expiry=86400 ignore_content_expiry keys=$cache_keys}
    {include uri='design:page_banner.tpl'}
{/cache-block}

<div class="main">
    {if $has_container|not()}<div class="container">{/if}
        {$module_result.content}

        {if and( $current_user.is_logged_in|not(), $social_pagedata.need_login|not )}
            {include uri='design:page_login.tpl'}
        {/if}

        {if $has_container|not()}</div>{/if}

{cache-block expiry=86400 ignore_content_expiry keys=array( $access_type.name, $user_hash )}

    {if is_set( $social_pagedata )|not()}{def $social_pagedata = social_pagedata()}{/if}
    <footer>
        <section id="footer_teasers_wrapper">
            <div class="container">
                <div class="row">
                    <div class="footer_teaser col-sm-6 col-md-6">
                        <h3>{'Contacts'|i18n('ocsocialdesign')}</h3>
                        <p>{attribute_view_gui attribute=$social_pagedata.attribute_contacts}</p>
                    </div>
                    <div class="footer_teaser col-sm-6 col-md-6">
                        <p>{attribute_view_gui attribute=$social_pagedata.attribute_footer}</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="copyright">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        &copy; {currentdate()|datetime('custom', '%Y')} {$social_pagedata.text_credits}
                    </div>
                </div>
            </div>
        </section>
    </footer>

</div>
{/cache-block}

{cache-block expiry=86400 ignore_content_expiry keys=array( $access_type.name )}
{if is_set($social_pagedata)|not()}{def $social_pagedata = social_pagedata()}{/if}
{if $social_pagedata.google_analytics_id}
    <script type="text/javascript">
        (function(i,s,o,g,r,a,m){ldelim}i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ldelim}
                (i[r].q=i[r].q||[]).push(arguments){rdelim},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        {rdelim})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
        ga('create', '{$social_pagedata.google_analytics_id}', 'auto');
        ga('set', 'anonymizeIp', true);
        ga('set', 'forceSSL', true);
        ga('send', 'pageview');
    </script>
{/if}

<script>
    {literal}
    $(document).ready(function(){
        $.get({/literal}{'sensor/alert'|ezurl()}{literal}, function(data){
            $('header').prepend(data)
        });
    });
    {/literal}
</script>
{/cache-block}

<!--DEBUG_REPORT-->
</body>
</html>
