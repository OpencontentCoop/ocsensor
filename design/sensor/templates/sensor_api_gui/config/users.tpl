<form class="row form">
    {if count($user_groups)}
        <div class="col-xs-12 col-md-3" style="padding-right: 0">
            <select class="form-control" name="user_group">
                <option selected="selected" value="{$user_parent_node.node_id}">{sensor_translate('Filter by group', 'config')}</option>
                {foreach $user_groups as $user_group}
                    <option value="{$user_group.node_id}">{$user_group.name|wash()}</option>
                {/foreach}
            </select>
        </div>
    {/if}
    <div class="col-xs-12 col-md-7">
        <div class="input-group">
            <input type="text" class="form-control" data-search="q" placeholder="{sensor_translate('Search', 'config')}">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-search"></i>
                </button>
                <button type="reset" class="btn btn-danger hide">
                    <i class="fa fa-times"></i>
                </button>
            </span>
        </div>
    </div>
    {if fetch( 'user', 'has_access_to', hash( 'module', 'user', 'function', 'activation' ) )}
    <div class="col-xs-12 col-md-2 text-right">
        <a class="btn btn-sm btn-primary" href="{'user/unactivated'|ezurl(no)}">{'Unactivated users'|i18n('design/admin/parts/user/menu')}</a>
    </div>
    {/if}
</form>
<div style="margin: 20px 0"
     data-parent="{$user_parent_node.node_id}"
     data-classes="{$user_classes|implode(',')}"
     data-limit="20"
     data-redirect="/sensor/config/users"></div>

<div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/user/',ezini("UserSettings", "DefaultUserPlacement"))|ezurl(no)}">{sensor_translate('Export to CSV', 'config')}</a></div>

{literal}
<script id="tpl-data-spinner" type="text/x-jsrender">
<div class="col-xs-12 spinner text-center">
    <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
</div>
</script>
<script id="tpl-data-results" type="text/x-jsrender">
<div class="row">
    {{if totalCount == 0}}
        <div class="col-xs-12 text-center">
            <i class="fa fa-times"></i> {{:~sensorTranslate('No content')}}
        </div>
    {{else}}
    <div class="col-xs-12">
        <table class="table table-striped">
            <tbody>
            {{for searchHits}}
                <tr>
                    <td>
                        {{if metadata.classIdentifier != 'user'}}<i class="fa fa-user-circle"></i>{{/if}}
                        {{if ~i18n(metadata.name)}}{{:~i18n(metadata.name)}}{{/if}}
                    </td>
                    <td width="1">
                        <span style="white-space:nowrap">
                        {{for translations}}
                            {{if active}}
                                <img style="max-width:none" src="/share/icons/flags/{{:language}}.gif" />
                            {{/if}}
                        {{/for}}
                        </span>
                    </td>
                    <td width="1">
                        <div class="notification-dropdown-container dropdown" data-user="{{:metadata.id}}">
                            <div class="button-group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-bell"></i> <span class="caret"></span>
                                </button>
                                <ul class="notification-dropdown-menu dropdown-menu">
                                </ul>
                            </div>
                        </div>
                    </td>
                    <td width="1">
                        <a class="btn btn-link btn-xs text-black" data-user_access_edit="{{:metadata.id}}" data-type="{{:metadata.classIdentifier}}"><i data-user={{:metadata.id}} class="fa fa-user"></i></a>
                    </td>
                    <td width="1">
                        {{if metadata.userAccess.canEdit}}
                            <a href="#" data-edit="{{:metadata.id}}"><i class="fa fa-pencil"></i></a>
                        {{/if}}
                    </td>
                    <td width="1">
                        {{if metadata.userAccess.canRemove}}
                            <a href="#" data-remove="{{:metadata.id}}"><i class="fa fa-trash"></i></a>
                        {{/if}}
                    </td>
                </tr>
            {{/for}}
            </tbody>
        </table>
    </div>
    {{/if}}
</div>

{{if pageCount > 1}}
<div class="row">
    <div class="col-xs-12">
        <div class="pagination-container text-center" aria-label="{{:~sensorTranslate('Navigation')}}">
            <ul class="pagination">
                <li class="page-item {{if !prevPageQuery}}disabled{{/if}}">
                    <a class="page-link prevPage" {{if prevPageQuery}}data-page="{{>prevPage}}"{{/if}} href="#">
                        <i class="fa fa-arrow-left"></i>
                        <span class="sr-only">{{:~sensorTranslate('Previous page')}}</span>
                    </a>
                </li>
                <li class="page-item {{if !nextPageQuery}}disabled{{/if}}">
                    <a class="page-link nextPage" {{if nextPageQuery}}data-page="{{>nextPage}}"{{/if}} href="#">
                        <span class="sr-only">{{:~sensorTranslate('Next page')}}</span>
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
{{/if}}

</script>
    <script>
        $(document).ready(function () {
            $('[data-parent]').each(function () {
                var notificationUrl = "{/literal}{'sensor/notifications'|ezurl(no)}/{literal}";
                var resultsContainer = $(this);
                var form = resultsContainer.prev();
                var limitPagination = resultsContainer.data('limit');
                var subtree = resultsContainer.data('parent');
                var classes = resultsContainer.data('classes');
                var redirect = resultsContainer.data('redirect');
                var currentPage = 0;
                var queryPerPage = [];
                var template = $.templates('#tpl-data-results');
                var spinner = $($.templates("#tpl-data-spinner").render({}));

                var onOptionClick = function (event) {
                    var $target = $(event.currentTarget);
                    var identifier = $target.data('identifier');
                    var user = $target.data('user');
                    var menu = $target.parents('.notification-dropdown-container .notification-dropdown-menu');

                    $(event.target).blur();
                    var enable = $(event.target).prop('checked');
                    if ($(event.target).attr('type') === 'checkbox') {
                        jQuery.ajax({
                            url: notificationUrl + user + '/' + identifier,
                            type: enable ? 'post' : 'delete',
                            success: function (response) {
                                buildNotificationMenu(user, menu);
                            }
                        });
                    }

                    event.stopPropagation();
                    event.preventDefault();
                };

                var buildNotificationMenu = function (user, menu) {
                    menu.html('<li style="padding: 50px; text-align: center; font-size: 2em;"><i class="fa fa-gear fa-spin fa2x"></i></li>');
                    $.get(notificationUrl + user, function (response) {
                        if (response.result && response.result === 'success') {
                            menu.html('');
                            var add = $('<li><a href="#" class="small" data-user="' + user + '" data-identifier="all" tabIndex="-1"><input type="checkbox"/><b> Attiva tutto</b></a></li>');
                            add.find('a').on('click', function (e) {
                                onOptionClick(e)
                            });
                            menu.append(add);
                            var remove = $('<li><a href="#" class="small" data-user="' + user + '" data-identifier="none" tabIndex="-1"><input type="checkbox"/><b> Disattiva tutto</b></a></li>');
                            remove.find('a').on('click', function (e) {
                                onOptionClick(e)
                            });
                            menu.append(remove);
                            $.each(response.data, function () {
                                var item = $('<li><a href="#" class="small" data-user="' + user + '" data-identifier="' + this.identifier + '" tabIndex="-1"><input type="checkbox"/>&nbsp;' + this.name + '</a></li>');
                                if (this.enabled) {
                                    item.find('input').attr('checked', true);
                                }
                                item.find('a').on('click', function (e) {
                                    onOptionClick(e)
                                });
                                menu.append(item);
                            })
                        } else {
                            console.log(response);
                        }
                    });
                };

                var buildQuery = function () {
                    var classQuery = '';
                    if (classes.length) {
                        classQuery = 'classes [' + classes + ']';
                    }
                    var query = classQuery;
                    var searchText = form.find('[data-search="q"]').val().replace(/"/g, '').replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");
                    if (searchText.length > 0) {
                        query += " and q = '\"" + searchText + "\"'";
                    }
                    var subtreeSelect = form.find('[name="user_group"]');
                    if (subtreeSelect.length > 0){
                        subtree = subtreeSelect.val();
                    }
                    query += ' subtree [' + subtree + '] and raw[meta_main_node_id_si] !in [' + subtree + ']'
                    query += ' sort [last_name=>asc,name=>asc]';

                    return query;
                };

                var loadContents = function () {
                    var baseQuery = buildQuery();
                    var paginatedQuery = baseQuery + ' and limit ' + limitPagination + ' offset ' + currentPage * limitPagination;
                    resultsContainer.html(spinner);
                    $.opendataTools.find(paginatedQuery, function (response) {
                        queryPerPage[currentPage] = paginatedQuery;
                        response.currentPage = currentPage;
                        response.prevPage = currentPage - 1;
                        response.nextPage = currentPage + 1;
                        var pagination = response.totalCount > 0 ? Math.ceil(response.totalCount / limitPagination) : 0;
                        var pages = [];
                        var i;
                        for (i = 0; i < pagination; i++) {
                            queryPerPage[i] = baseQuery + ' and limit ' + limitPagination + ' offset ' + (limitPagination * i);
                            pages.push({'query': i, 'page': (i + 1)});
                        }
                        response.pages = pages;
                        response.pageCount = pagination;

                        response.prevPageQuery = jQuery.type(queryPerPage[response.prevPage]) === "undefined" ? null : queryPerPage[response.prevPage];
                        response.showType = classes.split(',').length > 1;
                        $.each(response.searchHits, function () {
                            if (this.metadata.classIdentifier === 'place') {
                                var osmParts = this.metadata.remoteId.split('-');
                                if (osmParts.length === 2) {
                                    this.placeOsmDetailUrl = 'https://nominatim.openstreetmap.org/details?osmtype=' + osmParts[0].toUpperCase().charAt(0) + '&osmid=' + osmParts[1];
                                }
                            }
                            this.showType = classes.split(',').length > 1;
                            this.baseUrl = $.opendataTools.settings('accessPath');
                            var self = this;
                            this.languages = $.opendataTools.settings('languages');
                            var currentTranslations = this.metadata.languages;
                            var translations = [];
                            $.each($.opendataTools.settings('languages'), function () {
                                translations.push({
                                    'id': self.metadata.id,
                                    'language': '' + this,
                                    'active': $.inArray('' + this, currentTranslations) >= 0
                                });
                            });
                            this.translations = translations;
                            this.redirect = redirect;
                            this.locale = $.opendataTools.settings('language');
                            var stateIdentifier = false;
                            $.each(this.metadata.stateIdentifiers, function () {
                                var parts = this.split('.');
                                if (parts[0] === 'privacy') {
                                    stateIdentifier = parts[1];
                                }
                            });
                            this.visibility = stateIdentifier;
                        });
                        var renderData = $(template.render(response));

                        renderData.find('[data-edit]').on('click', function(e){
                            $('#item').opendataFormEdit({
                                object: $(this).data('edit')
                            },{
                                onBeforeCreate: function(){
                                    $('#modal').modal('show');
                                    setTimeout(function() {
                                        $('#modal .leaflet-container').trigger('click');
                                    }, 1000);
                                },
                                onSuccess: function () {
                                    $('#modal').modal('hide');
                                    loadContents();
                                }
                            });
                            e.preventDefault();
                        });
                        renderData.find('[data-remove]').on('click', function(e){
                            $('#item').opendataFormDelete({
                                object: $(this).data('remove')
                            },{
                                onBeforeCreate: function(){
                                    $('#modal').modal('show');
                                },
                                onSuccess: function () {
                                    $('#modal').modal('hide');
                                    loadContents();
                                }
                            });
                            e.preventDefault();
                        });

                        resultsContainer.html(renderData);

                        resultsContainer.find('.notification-dropdown-container').on('show.bs.dropdown', function () {
                            var user = $(this).data('user');
                            var menu = $(this).find('.notification-dropdown-menu');
                            buildNotificationMenu(user, menu);
                        });

                        renderData.find('[data-user_access_edit]').on('click', function(e){
                            var connector = $(this).data('type') === 'user' ? 'user-settings' : 'operator-settings';
                            $('#item').opendataForm({
                                user: $(this).data('user_access_edit')
                            },{
                                connector: connector,
                                onBeforeCreate: function(){
                                    $('#modal').modal('show');
                                    setTimeout(function() {
                                        $('#modal .leaflet-container').trigger('click');
                                    }, 1000);
                                },
                                onSuccess: function () {
                                    $('#modal').modal('hide');
                                    loadContents();
                                }
                            });
                            e.preventDefault();
                        });

                        renderData.find('.fa-user').each(function () {
                            var self = $(this);
                            var id = $(this).data('user');
                            $.get('/api/sensor_gui/users/' + id, function (data) {
                                if(!data.isEnabled){
                                    self.parent().html('<span class="fa-stack"><i class="fa fa-user fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span>');
                                }
                            })
                        });

                        resultsContainer.find('.page, .nextPage, .prevPage').on('click', function (e) {
                            currentPage = $(this).data('page');
                            if (currentPage >= 0) loadContents();
                            $('html, body').stop().animate({
                                scrollTop: form.offset().top
                            }, 1000);
                            e.preventDefault();
                        });
                    });
                };

                form[0].reset();
                loadContents();

                form.find('button[type="submit"]').on('click', function (e) {
                    form.find('button[type="reset"]').removeClass('hide');
                    currentPage = 0;
                    loadContents();
                    e.preventDefault();
                });
                form.find('button[type="reset"]').on('click', function (e) {
                    form[0].reset();
                    form.find('button[type="reset"]').addClass('hide');
                    currentPage = 0;
                    loadContents();
                    e.preventDefault();
                });
                form.on('submit', function () {
                    form.find('button[type="reset"]').removeClass('hide');
                    currentPage = 0;
                    loadContents();
                    e.preventDefault();
                });
            });
        });
    </script>
{/literal}
