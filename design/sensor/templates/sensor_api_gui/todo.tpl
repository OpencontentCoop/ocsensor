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
    'jsrender.js',
    'jquery.sensorpost.js'
))}

{def $current_language = ezini('RegionalSettings', 'Locale')}
{def $current_locale = fetch( 'content', 'locale' , hash( 'locale_code', $current_language ))}
{def $moment_language = $current_locale.http_locale_code|explode('-')[0]|downcase()|extract_left( 2 )}

{include uri='design:sensor_api_gui/todo/tpl-todo-item-row.tpl'}
{include uri='design:sensor_api_gui/todo/tpl-todo-rows.tpl'}
{include uri='design:sensor_api_gui/todo/tpl-todo-empty.tpl'}

<div class="row">
    <div class="col-md-2 col-xs-1">
        <ul class="nav nav-pills nav-stacked" id="inbox-menu">
            <li role="presentation" class="active">
                <a href="#" data-inboxidentifier="todolist">
                    <i class="fa fa-inbox"></i>
                    <span class="hidden-sm hidden-xs nav-label">In arrivo</span> <span class="badge hidden-sm hidden-xs pull-right"><i class="fa fa-refresh fa-spin"></i></span>
                </a>
            </li>
            <li role="presentation">
                <a href="#" data-inboxidentifier="special">
                    <i class="fa fa-star"></i>
                    <span class="hidden-sm hidden-xs nav-label">Speciali</span> <span class="badge hidden-sm hidden-xs pull-right"><i class="fa fa-refresh fa-spin"></i></span>
                </a>
            </li>
            <li role="presentation">
                <a href="#" data-inboxidentifier="conversations">
                    <i class="fa fa-comments"></i>
                    <span class="hidden-sm hidden-xs nav-label">Note private</span>{* <span class="badge hidden-sm hidden-xs pull-right"><i class="fa fa-refresh fa-spin"></i></span>*}
                </a>
            </li>
            <li role="presentation">
                <a href="#" data-inboxidentifier="moderate">
                    <i class="fa fa-commenting-o"></i>
                    <span class="hidden-sm hidden-xs nav-label">Da moderare</span> <span class="badge hidden-sm hidden-xs pull-right"><i class="fa fa-refresh fa-spin"></i></span>
                </a>
            </li>
            <li role="presentation">
                <a href="#" data-inboxidentifier="closed">
                    <i class="fa fa-close"></i>
                    <span class="hidden-sm hidden-xs nav-label">Chiuse</span> <span class="badge hidden-sm hidden-xs pull-right"><i class="fa fa-refresh fa-spin"></i></span>
                </a>
            </li>
        </ul>
    </div>
    <div class="col-md-10 col-xs-10">
        <table class="table table-hover" id="inbox">
            <thead>
                <tr>
                    <td colspan="5">
                        <ul class="list-inline pull-left">
                            <li><i class="fa fa-refresh text-muted faa-passing animated"></i></li>
                        </ul>
                        <ul class="list-inline pull-right text-right">
                            <li class="text-muted"><i class="fa fa-ellipsis-h text-muted faa-passing animated"></i></li>
                            <li><i class="fa fa-chevron-left text-muted"></i></li>
                            <li><i class="fa fa-chevron-right text-muted"></i></li>
                        </ul>
                    </td>
                </tr>
            </thead>
            <tbody>
            {for 0 to 10 as $counter}
                <tr>
                    <td width="1" style="vertical-align:middle"><i class="fa fa-circle text-muted faa-passing animated"></i></td>
                    <td width="1" style="vertical-align:middle"><i class="fa fa-star-o text-muted faa-passing animated"></i></td>
                    <td width="1" style="vertical-align:middle;white-space:nowrap"><i class="fa fa-ellipsis-h text-muted faa-passing animated"></i></td>
                    <td style="vertical-align:middle">
                        <div class="row">
                            <div class="col-md-4">
                                <i class="fa fa-ellipsis-h text-muted faa-passing animated"></i>
                            </div>
                            <div class="col-md-8">
                                <i class="fa fa-ellipsis-h text-muted faa-passing animated"></i>
                            </div>
                        </div>
                    </td>
                    <td width="1" style="vertical-align:middle;white-space:nowrap"><i class="fa fa-ellipsis-h text-muted faa-passing animated"></i></td>
                </tr>
            {/for}
            </tbody>
        </table>
        <div id="preview" style="display: none">
            <table class="table" id="inbox">
                <thead>
                <tr>
                    <td colspan="6" style="border-bottom: 1px solid #ddd">
                        <ul class="list-inline pull-left">
                            <li><a href="#" data-closepreview><i class="fa fa-arrow-left"></i></a></li>
                        </ul>
                        <ul class="list-inline pull-right">
                            <li><span class="preview-action"></span></li>
                            <li><a href="#" data-refreshpreview><i class="fa fa-refresh"></i></a></li>
                        </ul>
                    </td>
                </tr>
                </thead>
            </table>
            <div id="post-preview" class="post-gui" style="position: relative;min-height: 400px;"></div>
            {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post.tpl'}
            {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-title.tpl'}
            {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-detail.tpl'}
            {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-messages.tpl'}
            {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-sidebar.tpl'}
            {include uri='design:sensor_api_gui/posts/tpl-alerts.tpl'}
            {include uri='design:sensor_api_gui/posts/tpl-spinner.tpl'}
            {include uri='design:sensor_api_gui/posts/tpl-post-gallery.tpl'}
        </div>
    </div>
</div>

{literal}
<script>
    $(document).ready(function () {
        moment.locale('{/literal}{$moment_language}{literal}');
        $.views.helpers($.extend({}, $.opendataTools.helpers, {
            'fromNow': function (value) {
                return moment(new Date(value)).fromNow();
            },
            'progressiveDate': function (value) {
                var date = moment(new Date(value));
                var today = moment();
                var yesterday = moment().subtract(1, 'day');
                if (date.isSame(today, "day")){
                    return date.format('HH:mm')
                }
                if (date.isSame(yesterday, "day")){
                    return 'Ieri, ' + date.format('HH:mm')
                }
                if (date.isSame(new Date(), "month")){
                    return date.format('D MMM')
                }
                return date.format('DD/MM/YY')
            }
        }));
        var menu = $('#inbox-menu');
        var body = $('#inbox');
        var preview = $('#preview');
        var templateRows = $.templates('#tpl-todo-rows');
        var templateEmpty = $.templates('#tpl-todo-empty');
        var limit = 20;
        var currentPage = 1;
        var postPreview = $('#post-preview');
        var sensorPostViewer = postPreview.sensorPost({
            'apiEndPoint': '/api/sensor_gui',
            'sensorPostDefinition': '{/literal}{sensor_post_class()|json_encode()|wash(javascript)}{literal}',
            'currentUserId': {/literal}{fetch(user,current_user).contentobject_id|int()}{literal},
            'areas': '{/literal}{sensor_areas()|json_encode()|wash(javascript)}{literal}',
            'categories': '{/literal}{sensor_categories()|json_encode()|wash(javascript)}{literal}',
            'operators': '{/literal}{sensor_operators()|json_encode()|wash(javascript)}{literal}',
            'groups': '{/literal}{sensor_groups()|json_encode()|wash(javascript)}{literal}',
            'settings': '{/literal}{sensor_settings()|json_encode()|wash(javascript)}{literal}',
            'spinnerTpl': '#tpl-spinner',
            'postTpl': '#tpl-post',
            'alertsEndPoint': '{/literal}{'social_user/alert'|ezurl(no)}{literal}'
        }).data('plugin_sensorPost');

        var getList = function (identifier, page, limit, cb, context) {
            menu.find('[data-inboxidentifier="'+identifier+'"]').find('.badge').html('<i class="fa fa-refresh fa-spin"></i>');
            page = page || 1;
            $.ajax({
                type: "GET",
                url: '/api/sensor_gui/inbox/'+identifier,
                data: {
                    page: page,
                    limit: limit
                },
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                success: function (response,textStatus,jqXHR) {
                    if(response.error_message || response.error_code){
                        console.log(response.error_message);
                    }else {
                        currentPage = response.current_page;
                        cb.call(context, response);
                    }
                },
                error: function (jqXHR) {
                    var error = {
                        error_code: jqXHR.status,
                        error_message: jqXHR.statusText
                    };
                    console.log(error.error_message);
                }
            });
        };
        var renderList = function(identifier, data){
            menu.find('[data-inboxidentifier="'+identifier+'"]').find('.badge').text(data.count);
            data.limit = limit;
            if (data.count === 0){
                body.html(templateEmpty.render(data));
            }else{
                data.start = data.current_page === 1 ? 1 : limit * (data.current_page - 1) + 1;
                data.end = data.current_page === 1 ? limit : limit * data.current_page;
                if (data.end > data.count){
                    data.end = data.count;
                }
                var rendered = $(templateRows.render(data));
                rendered.find('[data-reload]').on('click', function (e){
                    getList(identifier, currentPage, limit, function (data){
                        renderList(identifier, data);
                    });
                    e.preventDefault();
                });
                rendered.find('[data-next]').on('click', function (e){
                    if ($(this).hasClass('disabled')){
                        return false;
                    }
                    currentPage = currentPage + 1;
                    getList(identifier, currentPage, limit, function (data){
                        renderList(identifier, data);
                    });
                    e.preventDefault();
                });
                rendered.find('[data-prev]').on('click', function (e){
                    currentPage = currentPage - 1;
                    if (currentPage <= 0 || $(this).hasClass('disabled')){
                        return false;
                    }
                    getList(identifier, currentPage, limit, function (data){
                        renderList(identifier, data);
                    });
                    e.preventDefault();
                });
                rendered.find('[data-star]').on('click', function (e){
                    var self = $(this);
                    var id = self.data('star');
                    var csrfToken;
                    var tokenNode = document.getElementById('ezxform_token_js');
                    if ( tokenNode ){
                        csrfToken = tokenNode.getAttribute('title');
                    }
                    if (self.hasClass('fa-star-o')){
                        $.ajax({
                            type: "POST",
                            url: '/api/sensor_gui/special/'+id+'/1',
                            headers: {'X-CSRF-TOKEN': csrfToken},
                            success: function (response,textStatus,jqXHR) {
                                if(response.error_message || response.error_code){
                                    console.log(response.error_message);
                                }else {
                                    self.removeClass('fa-star-o text-muted');
                                    self.addClass('fa-star text-primary');
                                    refreshMenuItem('special');
                                    refreshMenuItem('todolist');
                                }
                            },
                            error: function (jqXHR) {
                                var error = {
                                    error_code: jqXHR.status,
                                    error_message: jqXHR.statusText
                                };
                                console.log(error.error_message);
                            }
                        });
                    }else{
                        $.ajax({
                            type: "POST",
                            url: '/api/sensor_gui/special/'+id+'/0',
                            headers: {'X-CSRF-TOKEN': csrfToken},
                            success: function (response,textStatus,jqXHR) {
                                if(response.error_message || response.error_code){
                                    console.log(response.error_message);
                                }else {
                                    self.addClass('fa-star-o text-muted');
                                    self.removeClass('fa-star text-primary');
                                    refreshMenuItem('special');
                                    refreshMenuItem('todolist');
                                }
                            },
                            error: function (jqXHR) {
                                var error = {
                                    error_code: jqXHR.status,
                                    error_message: jqXHR.statusText
                                };
                                console.log(error.error_message);
                            }
                        });
                    }
                });
                rendered.find('tr[data-previewid]').on('click', function (e){
                    var row = $(e.currentTarget);
                    var target = $(e.target);
                    var action = row.find('.todo-action').text();
                    if (!$(e.target).hasClass('fa-star') && !$(e.target).hasClass('fa-star-o')){
                        showPreview(row.data('previewid'), action);
                    }
                });
                body.html(rendered);
            }
        }

        menu.find('[data-inboxidentifier]').on('click', function (e){
            closePreview();
            menu.find('li').removeClass('active');
            var identifier = $(this).data('inboxidentifier');
            $(this).parent().addClass('active');
            currentPage = 1;
            getList(identifier, currentPage, limit, function (data){
                renderList(identifier, data);
            })
            e.preventDefault();
        });
        var refreshMenuItem = function(identifier) {
            getList(identifier, currentPage, 1, function (data) {
                menu.find('[data-inboxidentifier="' + identifier + '"]').find('.badge').text(data.count);
            })
        }
        var refreshMenu = function() {
            menu.find('[data-inboxidentifier]').each(function () {
                refreshMenuItem($(this).data('inboxidentifier'));
            });
        };
        $('[data-closepreview]').on('click', function (e){
            closePreview();
            e.preventDefault();
        });
        $('[data-refreshpreview]').on('click', function (e){
            postPreview.find('#current-post-id').trigger('click');
            e.preventDefault();
        });
        var closePreview = function(){
            sensorPostViewer.removeAlert();
            body.show();
            preview.find('.preview-action').text('');
            preview.hide();
            postPreview.html('');
        };
        var showPreview = function (postId, action){
            body.hide();
            preview.find('.preview-action').text(action);
            preview.show();
            sensorPostViewer.removeAlert().startLoading().load(postId);
        };

        refreshMenu();
        getList('todolist', currentPage, limit, function (data){
            renderList('todolist', data);
        });

        if (Socket){
            var events = {
                'on_create': ['todolist'],
                'on_read': ['todolist'],
                'on_assign': ['todolist'],
                'on_group_assign': ['todolist'],
                'on_add_image': ['todolist'],
                'on_add_comment': ['todolist', 'moderate'],
                'on_fix': ['todolist'],
                'on_close': ['todolist', 'closed'],
                'on_reopen': ['todolist', 'closed'],
                'on_send_private_message': ['todolist', 'conversations'],
                'on_add_comment_to_moderate': ['todolist', 'moderate'],
            };
            $.each(events, function (eventName, menuItems){
                Socket.on(eventName, function (){
                    $.each(menuItems, function (){
                        refreshMenuItem(this);
                    })
                    body.find('[data-reload]').trigger('click');
                })
            })
        }
    })
</script>
{/literal}