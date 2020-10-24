<form class="row form">
    <div class="col-xs-12 col-md-6">
        <div class="input-group">
            <input type="text" class="form-control" data-search="q" placeholder="{'Cerca'|i18n('sensor/config')}">
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
</form>
<div style="margin: 20px 0"
     data-parent="{$operator_parent_node.node_id}"
     data-classes="{$operator_class.identifier}"
     data-limit="20"
     data-redirect="/sensor/config/operators"></div>

<div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/',$operator_class.identifier,'/',$operator_parent_node.node_id)|ezurl(no)}">{'Esporta in CSV'|i18n('sensor/config')}</a></div>
<div class="pull-right">
    <a class="btn btn-danger" id="add" data-add-parent="{$operator_parent_node.node_id}" data-add-class="sensor_operator" href="{concat('add/new/sensor_operator/?parent=',$operator_parent_node.node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi'|i18n('sensor/config')} {$operator_class.name}</a>
</div>

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
            <i class="fa fa-times"></i> {/literal}{'Nessun contenuto'|i18n('sensor')}{literal}
        </div>
    {{else}}
    <div class="col-xs-12">
        <table class="table table-striped">
            <tbody>
            {{for searchHits}}
                <tr>
                    <td>
                        {{if ~i18n(metadata.name)}}{{:~i18n(metadata.name)}}{{/if}}
                        {{if ~i18n(data, 'struttura_di_competenza')}}<br /><small>{{for ~i18n(data, 'struttura_di_competenza')}}{{:~i18n(name)}} {{/for}}</small>{{/if}}
                    </td>
                    <td width="1">
                        <span style="white-space:nowrap">
                        {{for translations}}
                            {{if active}}
                                <a href="{{:baseUrl}}/content/edit/{{:id}}/f/{{:language}}"><img style="max-width:none" src="/share/icons/flags/{{:language}}.gif" /></a>
                            {{else}}
                                <a href="{{:baseUrl}}/content/edit/{{:id}}/a"><img style="max-width:none;opacity:0.2" src="/share/icons/flags/{{:language}}.gif" /></a>
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
                    <td width="1" class="text-center">
                        <a class="btn btn-link btn-xs text-black" href="{{:baseUrl}}/social_user/setting/{{:metadata.id}}"><i data-user={{:metadata.id}} class="fa fa-user"></i></a>
                    </td>
                    <td width="1">
                        {{if metadata.userAccess.canEdit}}
                            <a href="#" data-edit={{:metadata.id}}><i class="fa fa-pencil"></i></a>
                        {{/if}}
                    </td>
                    <td width="1">
                        {{if metadata.userAccess.canRemove}}
                            <a href="#" data-remove={{:metadata.id}}><i class="fa fa-trash"></i></a>
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
        <div class="pagination-container text-center" aria-label="Esempio di navigazione della pagina">
            <ul class="pagination">

                <li class="page-item {{if !prevPageQuery}}disabled{{/if}}">
                    <a class="page-link prevPage" {{if prevPageQuery}}data-page="{{>prevPage}}"{{/if}} href="#">
                        <i class="fa fa-arrow-left"></i>
                        <span class="sr-only">Pagina precedente</span>
                    </a>
                </li>

                {{for pages ~current=currentPage}}
                    <li class="page-item{{if ~current == query}} active{{/if}}"><a href="#" class="page-link page" data-page_number="{{:page}}" data-page="{{:query}}"{{if ~current == query}} data-current aria-current="page"{{/if}}>{{:page}}</a></li>
                {{/for}}

                <li class="page-item {{if !nextPageQuery}}disabled{{/if}}">
                    <a class="page-link nextPage" {{if nextPageQuery}}data-page="{{>nextPage}}"{{/if}} href="#">
                        <span class="sr-only">Pagina successiva</span>
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
        $.views.helpers($.opendataTools.helpers);
        $(document).ready(function () {

            $('#add').on('click', function(e){
                $('#item').opendataFormCreate({
                    class: $(this).data('add-class'),
                    parent: $(this).data('add-parent')
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

            var notificationUrl = "{/literal}{'sensor/notifications'|ezurl(no)}/{literal}";
            var resultsContainer = $('[data-parent]');
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
                var query = classQuery + ' subtree [' + subtree + '] and raw[meta_main_node_id_si] !in [' + subtree + ']';
                var searchText = form.find('[data-search="q"]').val().replace(/"/g, '').replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");
                if (searchText.length > 0) {
                    query += " and q = '" + searchText + "'";
                }
                query += ' sort [name=>asc]';

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
                        var currentTranslations = $(this.languages).filter($.map(this.data, function (value, key) {
                            return key;
                        }));
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
                    resultsContainer.html(renderData);

                    resultsContainer.find('.notification-dropdown-container').on('show.bs.dropdown', function () {
                        var user = $(this).data('user');
                        var menu = $(this).find('.notification-dropdown-menu');
                        buildNotificationMenu(user, menu);
                    });
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
                            },
                            'alpaca': {
                                "connector": {
                                    "config": {
                                        "connector": 'remove-operator'
                                    }
                                }
                            }
                        });
                        e.preventDefault();
                    });

                    renderData.find('.fa-user').each(function () {
                        var self = $(this);
                        var id = $(this).data('user');
                        $.get('/api/sensor_gui/operators/' + id, function (data) {
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
                    var more = $('<li class="page-item"><span class="page-link">...</span></li');
                    var displayPages = resultsContainer.find('.page[data-page_number]');

                    var currentPageNumber = resultsContainer.find('.page[data-current]').data('page_number');
                    var length = 7;
                    if (displayPages.length > (length + 2)) {
                        if (currentPageNumber <= (length - 1)) {
                            resultsContainer.find('.page[data-page_number="' + length + '"]').parent().after(more.clone());
                            for (i = length; i < pagination; i++) {
                                resultsContainer.find('.page[data-page_number="' + i + '"]').parent().hide();
                            }
                        } else if (currentPageNumber >= length) {
                            resultsContainer.find('.page[data-page_number="1"]').parent().after(more.clone());
                            var itemToRemove = (currentPageNumber + 1 - length);
                            for (i = 2; i < pagination; i++) {
                                if (itemToRemove > 0) {
                                    resultsContainer.find('.page[data-page_number="' + i + '"]').parent().hide();
                                    itemToRemove--;
                                }
                            }
                            if (currentPageNumber < (pagination - 1)) {
                                resultsContainer.find('.page[data-current]').parent().after(more.clone());
                            }
                            for (i = (currentPageNumber + 1); i < pagination; i++) {
                                resultsContainer.find('.page[data-page_number="' + i + '"]').parent().hide();
                            }
                        }
                    }
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
    </script>
{/literal}