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
    'jquery.sensorpost.js'
))}

{include uri='design:sensor_api_gui/todo/tpl-todo-item-row.tpl'}
{include uri='design:sensor_api_gui/todo/tpl-todo-rows.tpl'}
{include uri='design:sensor_api_gui/todo/tpl-todo-empty.tpl'}

<div class="row">
    <div class="col-md-2 col-xs-1">
        <ul class="nav nav-pills nav-stacked" id="inbox-menu">
            <li role="presentation" class="active">
                <a href="#" data-inboxidentifier="todolist">
                    <i class="fa fa-inbox"></i>
                        <span class="hidden-sm hidden-xs nav-label">{sensor_translate('Incoming')}</span> <span class="badge hidden-sm hidden-xs pull-right"><i class="fa fa-refresh fa-spin"></i></span>
                </a>
            </li>
            <li role="presentation">
                <a href="#" data-inboxidentifier="special">
                    <i class="fa fa-star"></i>
                    <span class="hidden-sm hidden-xs nav-label">{sensor_translate('Specials')}</span> <span class="badge hidden-sm hidden-xs pull-right"><i class="fa fa-refresh fa-spin"></i></span>
                </a>
            </li>
            {if can_set_sensor_tag()}
                <li role="presentation">
                    <a href="#" data-inboxidentifier="important">
                        <i class="fa fa-bell"></i>
                        <span class="hidden-sm hidden-xs nav-label">{sensor_translate('Importants')}</span> <span class="badge hidden-sm hidden-xs pull-right"><i class="fa fa-refresh fa-spin"></i></span>
                    </a>
                </li>
            {/if}
            <li role="presentation">
                <a href="#" data-inboxidentifier="conversations">
                    <i class="fa fa-comments"></i>
                    <span class="hidden-sm hidden-xs nav-label">{sensor_translate('Private notes')}</span>{* <span class="badge hidden-sm hidden-xs pull-right"><i class="fa fa-refresh fa-spin"></i></span>*}
                </a>
            </li>
            <li role="presentation">
                <a href="#" data-inboxidentifier="moderate">
                    <i class="fa fa-commenting-o"></i>
                    <span class="hidden-sm hidden-xs nav-label">{sensor_translate('To moderate')}</span> <span class="badge hidden-sm hidden-xs pull-right"><i class="fa fa-refresh fa-spin"></i></span>
                </a>
            </li>
            <li role="presentation">
                <a href="#" data-inboxidentifier="closed">
                    <i class="fa fa-close"></i>
                    <span class="hidden-sm hidden-xs nav-label">{sensor_translate('Closed')}</span> {*<span class="badge hidden-sm hidden-xs pull-right"><i class="fa fa-refresh fa-spin"></i></span>*}
                </a>
            </li>
            {if sensor_settings('UseInboxFilters')}
            <li class="divider hidden-xs hidden-sm"><hr /></li>
            <li class="hidden-xs hidden-sm">
                <label for="filter-type">{sensor_translate('Filter by type')}</label>
                <select class="form-control" data-filter="type" id="filter-type" data-placeholder="{sensor_translate('Filter by type')}">
                    <option value="">{sensor_translate('All')}</option>
                    {foreach sensor_types() as $type}
                        <option value="{$type.identifier|wash()}">{$type.name|wash()}</option>
                    {/foreach}
                </select>
            </li>
            {/if}
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
                            <li><a href="#" data-refreshpreview><i class="fa fa-refresh"></i></a></li>
                            <li><span class="preview-action"></span></li>
                        </ul>
                        <ul class="list-inline pull-right">
                            <li><a href="#" data-previouspreview><i class="fa fa-chevron-left text-muted"></i></a></li>
                            <li><a href="#" data-nextpreview><i class="fa fa-chevron-right text-muted"></i></a></li>
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

<div id="respondInboxForm" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="clearfix" data-action-wrapper>
                    <label for="responseInbox">{sensor_translate('Official response')}</label>
                    <textarea id="responseInbox" name="text" class="form-control" rows="4" required="required"></textarea>
                    <input class="btn respond btn-bold pull-right"
                           type="submit"
                           style="margin-top:10px"
                           value="{sensor_translate('Store the official response and close the issue')}" />
                    <div class="pull-right">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="keepResponse" checked="checked" />
                                {sensor_translate('Remember answer')}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{literal}
<script>
    $(document).ready(function () {
        var menu = $('#inbox-menu');
        var body = $('#inbox');
        var preview = $('#preview');
        var templateRows = $.templates('#tpl-todo-rows');
        var templateEmpty = $.templates('#tpl-todo-empty');
        var limit = 20;
        var currentPage = 1;
        var postPreview = $('#post-preview');
        var apiEndPoint = '/api/sensor_gui';
        var respondInboxForm = $('#respondInboxForm');
        var sensorPostViewer = postPreview.sensorPost({
            'apiEndPoint': apiEndPoint,
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

        var filterSelectors = $('[data-filter]');
        var getList = function (identifier, page, limit, cb, context) {
            menu.find('[data-inboxidentifier="'+identifier+'"]').find('.badge').html('<i class="fa fa-refresh fa-spin"></i>');
            page = page || 1;
            var filters = [];
            filterSelectors.each(function (){
                var field = $(this).data('filter');
                var value = $(this).val();
                if (value.length > 0){
                    filters.push({
                        field: field,
                        value: value
                    });
                }
            });
            $.ajax({
                type: "GET",
                url: '/api/sensor_gui/inbox/'+identifier,
                data: {
                    page: page,
                    limit: limit,
                    filters: filters
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
        filterSelectors.on('change', function (e) {
            refreshMenu();
            menu.find('li.active a').trigger('click');
        });
        var renderList = function(identifier, data){
            menu.find('[data-inboxidentifier="'+identifier+'"]').find('.badge').text(data.count);
            data.limit = limit;
            if (data.count === 0){
                var renderedEmpty = $(templateEmpty.render(data));
                renderedEmpty.find('[data-reload]').on('click', function (e){
                    getList(identifier, currentPage, limit, function (data){
                        renderList(identifier, data);
                    });
                    e.preventDefault();
                });
                body.html(renderedEmpty);
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
                    var doEnable = self.hasClass('fa-star-o');
                    if (doEnable){
                        self.removeClass('fa-star-o text-muted');
                        self.addClass('fa-refresh fa-spin');
                        $.ajax({
                            type: "POST",
                            url: '/api/sensor_gui/special/'+id+'/1',
                            headers: {'X-CSRF-TOKEN': csrfToken},
                            success: function (response,textStatus,jqXHR) {
                                if(response.error_message || response.error_code){
                                    console.log(response.error_message);
                                }else {
                                    self.removeClass('fa-refresh fa-spin');
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
                                self.removeClass('fa-refresh fa-spin');
                                self.addClass('fa-star-o text-muted');
                            }
                        });
                    }else{
                        self.removeClass('fa-star text-primary');
                        self.addClass('fa-refresh fa-spin');
                        $.ajax({
                            type: "POST",
                            url: '/api/sensor_gui/special/'+id+'/0',
                            headers: {'X-CSRF-TOKEN': csrfToken},
                            success: function (response,textStatus,jqXHR) {
                                if(response.error_message || response.error_code){
                                    console.log(response.error_message);
                                }else {
                                    self.removeClass('fa-refresh fa-spin');
                                    self.addClass('fa-star-o text-muted');
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
                                self.removeClass('fa-refresh fa-spin');
                                self.addClass('fa-star text-primary');
                            }
                        });
                    }
                });
                rendered.find('[data-important]').on('click', function (e){
                    var self = $(this);
                    var id = self.data('important');
                    var csrfToken;
                    var tokenNode = document.getElementById('ezxform_token_js');
                    if ( tokenNode ){
                        csrfToken = tokenNode.getAttribute('title');
                    }
                    var doEnable = self.hasClass('fa-bell-o');
                    if (doEnable){
                        self.removeClass('fa-bell-o text-muted');
                        self.addClass('fa-refresh fa-spin');
                        $.ajax({
                            type: "POST",
                            url: '/api/sensor_gui/tagged-important/'+id+'/1',
                            headers: {'X-CSRF-TOKEN': csrfToken},
                            success: function (response,textStatus,jqXHR) {
                                if(response.error_message || response.error_code){
                                    console.log(response.error_message);
                                }else {
                                    self.removeClass('fa-refresh fa-spin');
                                    self.addClass('fa-bell text-primary');
                                    refreshMenuItem('important');
                                    refreshMenuItem('todolist');
                                }
                            },
                            error: function (jqXHR) {
                                var error = {
                                    error_code: jqXHR.status,
                                    error_message: jqXHR.statusText
                                };
                                console.log(error.error_message);
                                self.removeClass('fa-refresh fa-spin');
                                self.addClass('fa-bell-o text-muted');
                            }
                        });
                    }else{
                        self.removeClass('fa-bell text-primary');
                        self.addClass('fa-refresh fa-spin');
                        $.ajax({
                            type: "POST",
                            url: '/api/sensor_gui/tagged-important/'+id+'/0',
                            headers: {'X-CSRF-TOKEN': csrfToken},
                            success: function (response,textStatus,jqXHR) {
                                if(response.error_message || response.error_code){
                                    console.log(response.error_message);
                                }else {
                                    self.removeClass('fa-refresh fa-spin');
                                    self.addClass('fa-bell-o text-muted');
                                    refreshMenuItem('important');
                                    refreshMenuItem('todolist');
                                }
                            },
                            error: function (jqXHR) {
                                var error = {
                                    error_code: jqXHR.status,
                                    error_message: jqXHR.statusText
                                };
                                console.log(error.error_message);
                                self.removeClass('fa-refresh fa-spin');
                                self.addClass('fa-bell text-primary');
                            }
                        });
                    }
                });
                rendered.find('tr[data-previewid]').on('click', function (e){
                    var row = $(e.currentTarget);
                    var target = $(e.target);
                    var action = row.find('.todo-action').text();
                    if (!$(e.target).hasClass('fa-star')
                        && !$(e.target).hasClass('fa-star-o')
                        && !$(e.target).hasClass('fa-bell')
                        && !$(e.target).hasClass('fa-bell-o')
                        && !$(e.target).hasClass('fa-refresh')
                        && !$(e.target).hasClass('todo_action')){
                        showPreview(row.data('previewid'), action);
                    }
                });
                rendered.find('tr').hover(
                    function (){
                        $(this).find('.todo-actions').removeClass('hide');
                        $(this).find('.todo-date').css('visibility', 'hidden');
                    },
                    function (){
                        $(this).find('.todo-actions').addClass('hide');
                        $(this).find('.todo-date').css('visibility', 'visible');
                    }
                );
                rendered.find('[data-todo_action="close"]').on('click', function (e){
                    respondInboxForm.find('.respond').data('postId', $(this).parents('tr[data-previewid]').data('previewid'));
                    respondInboxForm.find('[name="text"]').val(getStoredResponse());
                    respondInboxForm.modal('show');
                    e.preventDefault();
                });
                rendered.find('[data-todo_action="close_with_last"]').on('click', function (e){
                    var postId = $(this).parents('tr[data-previewid]').data('previewid');
                    var text = getStoredResponse();
                    doCloseContextAction(postId, text);
                    e.preventDefault();
                });
                rendered.find('[data-todo_action="close_with_note"]').on('click', function (e){
                    var postId = $(this).parents('tr[data-previewid]').data('previewid');
                    var text = $(this).data('response');
                    doCloseContextAction(postId, text);
                    e.preventDefault();
                });
                rendered.find('.has-tooltip').tooltip({
                    show: {
                        effect: false
                    },
                    content: function (callback) {
                        callback($(this).prop('title'));
                    },
                    position: {
                        my: "center top+5",
                        at: "center bottom",
                        collision: "flipfit"
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
        var setPrevPreview = function (postId){
            var prevRow = body.find('tr[data-previewid="'+postId+'"]').prev();
            if (prevRow.length > 0){
                preview.find('[data-previouspreview]')
                    .data('id', prevRow.data('previewid'))
                    .data('action', prevRow.find('.todo-action').text())
                    .find('i').removeClass('text-muted');
            }else{
                preview.find('[data-previouspreview]')
                    .data('id', false)
                    .data('action', false)
                    .find('i').addClass('text-muted');
            }
        };
        var setNextPreview = function (postId){
            var nextRow = body.find('tr[data-previewid="'+postId+'"]').next();
            if (nextRow.length > 0){
                preview.find('[data-nextpreview]')
                    .data('id', nextRow.data('previewid'))
                    .data('action', nextRow.find('.todo-action').text())
                    .find('i').removeClass('text-muted');
            }else{
                preview.find('[data-nextpreview]')
                    .data('id', false)
                    .data('action', false)
                    .find('i').addClass('text-muted');
            }
            return false;
        };
        preview.find('[data-previouspreview]').on('click', function (e){
            if ($(this).data('id')) {
                showPreview($(this).data('id'), $(this).data('action'));
            }
            e.preventDefault();
        });
        preview.find('[data-nextpreview]').on('click', function (e){
            if ($(this).data('id')) {
                showPreview($(this).data('id'), $(this).data('action'));
            }
            e.preventDefault();
        });
        var showPreview = function (postId, action){
            body.hide();
            preview.find('.preview-action').text(action);
            setPrevPreview(postId);
            setNextPreview(postId);
            preview.show();
            sensorPostViewer.removeAlert().startLoading().load(postId);
        };

        var doCloseContextAction = function (postId, text){
            var row = body.find('tr[data-previewid="'+postId+'"]');
            row.css('opacity', '.2');
            var action = 'add_response,close';
            runContextAction(postId, action, {'text': text}, false, function () {
                row.remove();
            }, function (data) {
                if (data.responseJSON) {
                    alert(data.responseJSON.error_message);
                } else {
                    alert(data.responseText);
                }
                row.css('opacity', '1');
            });
        };
        respondInboxForm.find('.respond').on('click', function (e){
            var container = $(this).parents('.modal');
            container.modal('hide');
            var text = container.find('[name="text"]').val();
            var keep = container.find('[name="keepResponse"]').is(':checked');
            if (keep){
                if (text.length > 0) {
                    storeResponse(text);
                }else{
                    removeStoredResponse(text);
                }
            }
            var postId = $(this).data('postId');
            doCloseContextAction(postId, text);
            e.preventDefault();
        });
        var storeResponse = function (text){
            if (window.sessionStorage !== undefined){
                sessionStorage.setItem('todo-response', text);
            }
            $('[data-todo_action="close_with_last"]').each(function (){
                $(this).removeClass('hide').attr('title', $(this).data('base_title')+'<em>'+text+'</em>');
            })
        }
        var removeStoredResponse = function(){
            if (window.sessionStorage !== undefined){
                sessionStorage.removeItem('todo-response');
            }
            $('[data-todo_action="close_with_last"]').each(function (){
                $(this).addClass('hide').attr('title', false);
            })
        }
        var getStoredResponse = function (){
            return (window.sessionStorage !== undefined) ? sessionStorage.getItem('todo-response') : null
        }
        var isRunningContextAction = false;
        var runContextAction = function (postId, action, payload, confirmMessage, onSuccess, onError) {
            isRunningContextAction = true;
            var confirmation = true;
            if (confirmMessage) {
                confirmation = confirm(confirmMessage);
            }
            if (confirmation) {
                var csrfToken;
                var tokenNode = document.getElementById('ezxform_token_js');
                if ( tokenNode ){
                    csrfToken = tokenNode.getAttribute('title');
                }
                $.ajax({
                    type: 'POST',
                    url: apiEndPoint + '/actions/' + postId + '/' + action,
                    data: JSON.stringify(payload),
                    headers: {'X-CSRF-TOKEN': csrfToken},
                    success: function (data) {
                        if ($.isFunction(onSuccess)) {
                            onSuccess(data);
                        }
                        isRunningContextAction = false;
                    },
                    error: function (data) {
                        if ($.isFunction(onError)) {
                            onError(data);
                        }
                        isRunningContextAction = false;
                    },
                    contentType: "application/json",
                    dataType: 'json'
                });
            }
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
                    if (!isRunningContextAction) {
                        body.find('[data-reload]').trigger('click');
                    }
                })
            })
        }
    })
</script>
{/literal}
