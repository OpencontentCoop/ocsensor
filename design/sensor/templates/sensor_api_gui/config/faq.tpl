{ezcss_require(array('select2.min.css'))}
{ezscript_require(array('select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js')))}
<form class="row form">
    <div class="col-md-4 form-group" id="category-filter">
        <select class="select form-control" name="">
            <option></option>
            {foreach $categories.children as $item}
                <option value="{$item.id}"
                        style="padding-left:{$item.level|mul(10)}px;{if $item.level|eq(0)}font-weight: bold;{/if}">{$item.name|wash()}</option>
                {foreach $item.children as $child}
                    <option value="{$child.id}" style="padding-left:{$child.level|mul(10)}px;{if $child.level|eq(0)}font-weight: bold;{/if}">{$child.name|wash()}</option>
                {/foreach}
            {/foreach}
        </select>
    </div>
    <div class="col-xs-12 col-md-6">
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
</form>
<div style="margin: 20px 0"
     data-parent="{$faq_parent_node.node_id}"
     data-classes="{$faq_class}"
     data-limit="20"
     data-redirect="/sensor/config/faq"></div>
<div class="pull-left add-class">
    <a class="btn btn-info" href="{concat('exportas/csv/',$faq_class,'/',$faq_parent_node.node_id)|ezurl(no)}">{sensor_translate('Export to CSV', 'config')}</a>
</div>
<div class="pull-right add-class">
    <a class="btn btn-danger" id="add" data-add-parent="{$faq_parent_node.node_id}" data-add-class="{$faq_class}" href="{concat('add/new/', $faq_class,'/?parent=',$faq_parent_node.node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {sensor_translate('Add new', 'config')}</a>
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
            <i class="fa fa-times"></i> {{:~sensorTranslate('No content')}}
        </div>
    {{else}}
    <div class="col-xs-12">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{:~sensorTranslate('Faq')}}</th>
                    <th>{{:~sensorTranslate('Category')}}</th>
                    <th>{{:~sensorTranslate('Priority')}}</th>
                    <th colspan="4"></th>
                </tr>
            </thead>
            <tbody>
            {{for searchHits}}
                <tr>
                    <th>{{:metadata.id}}</th>
                    <td>
                        {{if ~i18n(data, 'question')}}{{:~i18n(data, 'question')}}{{/if}}
                    </td>
                    <td>
                        {{if ~i18n(data, 'category')}}{{for ~i18n(data, 'category')}}{{:~i18n(name)}} {{/for}}{{/if}}
                    </td>
                    <td>
                        {{if ~i18n(data, 'priority')}}{{:~i18n(data, 'priority')}}{{/if}}
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
                    <td width="1"><a href="#" data-object={{:metadata.id}}><i class="fa fa-eye"></i></a></td>
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
    $('#add').on('click', function(e){
        $('#item').opendataFormCreate({
            class: $(this).data('add-class'),
            parent: $(this).data('add-parent')
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
    var buildQuery = function () {
        var classQuery = '';
        if (classes.length) {
            classQuery = 'classes [' + classes + ']';
        }
        var query = classQuery + ' subtree [' + subtree + '] and raw[meta_main_node_id_si] !in [' + subtree + ']';
        var searchText = form.find('[data-search="q"]').val().replace(/"/g, '').replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");
        if (searchText.length > 0) {
            query += " and q = '\"" + searchText + "\"'";
        }
        var category = form.find('select').val();
        if (category.length > 0){
            query += " and category.id in [" + category + "]";
        }
        query += ' sort [priority=>desc,published=>asc]';
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
            resultsContainer.html(renderData);
            renderData.find('[data-object]').on('click', function(e){
                $('#item').opendataFormView({
                    object: $(this).data('object')
                },{
                    onBeforeCreate: function(){
                        $('#modal').modal('show');
                    }
                });
                e.preventDefault();
            });
            renderData.find('[data-edit]').on('click', function(e){
                $('#item').opendataFormEdit({
                    object: $(this).data('edit')
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
        form.find(".select").val(null).trigger("change");
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
    form.find(".select").select2({
        templateResult: function (item) {
            var style = item.element ? $(item.element).attr('style') : '';
            return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
        }
    });
});
</script>
<style>
.select2-container--default .select2-selection--single .select2-selection__arrow,
.select2-container .select2-selection--single{height: 34px;}
.select2-container--default .select2-selection--single .select2-selection__rendered {line-height: 34px;}
</style>
{/literal}
