<div id="post" style="position: relative;min-height: 400px;"></div>

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

{include uri='design:sensor_api_gui/posts/v1/parts/tpl-post.tpl'}
{include uri='design:sensor_api_gui/posts/v1/parts/tpl-post-title.tpl'}
{include uri='design:sensor_api_gui/posts/v1/parts/tpl-post-detail.tpl'}
{include uri='design:sensor_api_gui/posts/v1/parts/tpl-post-messages.tpl'}
{include uri='design:sensor_api_gui/posts/v1/parts/tpl-post-actions.tpl'}
{include uri='design:sensor_api_gui/posts/v1/parts/tpl-post-participants.tpl'}
{include uri='design:sensor_api_gui/posts/v1/parts/tpl-post-timeline.tpl'}
{include uri='design:sensor_api_gui/posts/tpl-alerts.tpl'}
{include uri='design:sensor_api_gui/posts/tpl-spinner.tpl'}
{include uri='design:sensor_api_gui/posts/tpl-post-gallery.tpl'}

<script>
$(document).ready(function () {ldelim}
    $('#post').sensorPost({ldelim}
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
        'alertsEndPoint': '{'social_user/alert'|ezurl(no)}'
    {rdelim}, {$post_id});
{rdelim});
</script>
