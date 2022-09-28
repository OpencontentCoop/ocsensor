<div id="post" class="post-gui" style="position: relative;min-height: 400px;"></div>

{ezcss_require(array(
    'plugins/blueimp/blueimp-gallery.css',
    'jquery.fileupload.css',
    'select2.min.css',
    'leaflet.0.7.2.css'
))}
{ezscript_require(array(
    'ezjsc::jquery', 'ezjsc::jqueryio', 'ezjsc::jqueryUI',
    'moment-with-locales.min.js',
    'js.cookie.js',
    'plugins/blueimp/jquery.blueimp-gallery.min.js',
    'select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js'),
    'jquery.fileupload.js',
    'leaflet.0.7.2.js',
    'Leaflet.MakiMarkers.js',
    'jquery.opendataTools.js',
    'jsrender.js', 'jsrender.helpers.js',
    'jquery.maskedinput.js',
    'jquery.sensorpost.js'
))}

{include uri='design:sensor_api_gui/posts/v2/parts/tpl-post.tpl'}
{include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-title.tpl'}
{include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-detail.tpl'}
{include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-messages.tpl'}
{include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-sidebar.tpl'}
{include uri='design:sensor_api_gui/posts/tpl-alerts.tpl'}
{include uri='design:sensor_api_gui/posts/tpl-spinner.tpl'}
{include uri='design:sensor_api_gui/posts/tpl-post-gallery.tpl'}

<script>
var additionalWMSLayers = [];
{foreach sensor_additional_map_layers() as $layer}
additionalWMSLayers.push({ldelim}
    baseUrl: '{$layer.baseUrl}',
    version: '{$layer.version}',
    layers: '{$layer.layers}',
    format: '{$layer.format}',
    transparent: {cond($layer.transparent, 'true', 'false')},
    attribution: '{$layer.attribution}'
{rdelim});
{/foreach}
$(document).ready(function () {ldelim}
    var sensorPostViewer =$('#post').sensorPost({ldelim}
        'apiEndPoint': '/api/sensor_gui',
        'sensorPostDefinition': '{$sensor_post|wash(javascript)}',
        'currentUserId': {fetch(user,current_user).contentobject_id|int()},
        'areas': '{$areas|wash(javascript)}',
        'categories': '{$categories|wash(javascript)}',
        'operators': '{$operators|wash(javascript)}',
        'groups': '{$groups|wash(javascript)}',
        'settings': '{$settings|wash(javascript)}',
        'spinnerTpl': '#tpl-spinner',
        'postTpl': '#tpl-post',
        'alertsEndPoint': '{'social_user/alert'|ezurl(no)}',
        'additionalWMSLayers': additionalWMSLayers
    {rdelim}, {$post_id}).data('plugin_sensorPost');

    var currentPageLink = $('[data-location="sensor-posts"]');
    window.onpopstate = function(event) {ldelim}
        if (event.state !== null && event.state.post_id !== null){ldelim}
            var postId = event.state.post_id;
            //window.history.pushState({ldelim}'post_id': postId{rdelim}, document.title, currentPageLink.attr('href')+'/'+postId);
            $(window).scrollTop(0);
            sensorPostViewer.removeAlert().startLoading().load(postId);
        {rdelim}
    };
    window.history.replaceState({ldelim}'post_id': {$post_id}{rdelim}, document.title, currentPageLink.attr('href')+'/'+{$post_id});

{rdelim});
</script>
