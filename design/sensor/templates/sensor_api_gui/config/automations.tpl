{ezcss_require(array('select2.min.css'))}
{ezscript_require(array('select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js')))}
<div class="tab-pane active" id="automations">
    <div class="row" id="criteria-search">
        <h5 class="text-muted" style="padding-left: 15px">{sensor_translate('Filter by condition', 'config')}</h5>
        <div class="col-md-3">
            <div class="form-group">
                <label for="triggers">{'Evento'|i18n('sensor/post')}</label>
                <select class="select form-control" name="triggers" id="triggers" data-placeholder="{sensor_translate('Any', 'config')}">
                    <option></option>
                    {foreach $events as $identifier => $name}
                        <option value="{$identifier|wash()}">{$name|wash()}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="criterion_area">{sensor_translate('Area', 'config')}</label>
                <select class="select form-control" name="criterion_area.id" id="criterion_area" data-placeholder="{sensor_translate('Any', 'config')}">
                    <option></option>
                    {foreach $areas.children as $item}
                        {*<option value="{$item.id}" style="padding-left:{$item.level|mul(10)}px;{if $item.level|eq(0)}font-weight: bold;{/if}">{$item.name|wash()}</option>*}
                        {foreach $item.children as $child}
                            <option value="{$child.id}"
                                    style="padding-left:{$child.level|mul(10)}px;{if $child.level|eq(0)}font-weight: bold;{/if}">{$child.name|wash()}</option>
                        {/foreach}
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="criterion_category">{sensor_translate('Category', 'config')}</label>
                <select class="select form-control" name="criterion_category.id" id="criterion_category" data-placeholder="{sensor_translate('Any', 'config')}">
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
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="criterion_type">{sensor_translate('Type', 'config')}</label>
                <select class="form-control" name="criterion_type" id="criterion_type" data-placeholder="{sensor_translate('Any', 'config')}">
                    <option></option>
                    {foreach $types as $type}
                        <option value="{$type.identifier}">{$type.name|wash()}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="criterion_reporter_group">{sensor_translate('Reporter group', 'config')}</label>
                <select class="form-control" name="criterion_reporter_group.id" id="criterion_reporter_group" data-placeholder="{sensor_translate('Any', 'config')}">
                    <option></option>
                    {foreach $groups.children as $item}
                        <option value="{$item.id}">{$item.name|wash()}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
    <div class="row" id="assignments-search">
        <h5 class="text-muted" style="padding-left: 15px">{sensor_translate('Filter by assignment', 'config')}</h5>
        <div class="col-md-3">
            <div class="form-group">
                <label for="approver">{sensor_translate('Reference for the citizen', 'config')}</label>
                <select class="select form-control" name="approver.id" id="approver" data-placeholder="{sensor_translate('Any', 'config')}">
                    <option></option>
                    <option value="reporter_as_approver">{sensor_translate('Operator who opened the issue', 'config')}</option>
                    <optgroup label="{sensor_translate('Groups', 'config')}">
                        {foreach $groups.children as $item}
                            <option value="{$item.id}">{$item.name|wash()}</option>
                        {/foreach}
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="owner_group">{sensor_translate('Group of operators in charge', 'config')}</label>
                <select class="select form-control" name="owner_group.id" id="owner_group" data-placeholder="{sensor_translate('Any', 'config')}">
                    <option></option>
                    {foreach $groups.children as $item}
                        <option value="{$item.id}">{$item.name|wash()}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="owner">{sensor_translate('Operator in charge', 'config')}</label>
                <select class="form-control" name="owner.id" id="owner" data-placeholder="{sensor_translate('Any', 'config')}">
                    <option></option>
                    <option value="reporter_as_owner">{sensor_translate('Operator who opened the issue', 'config')}</option>
                    <option value="random_owner">{sensor_translate('Random operator', 'config')}</option>
                    <optgroup label="{sensor_translate('Operators', 'config')}">
                        {foreach $operators.children as $item}
                            <option value="{$item.id}">{$item.name|wash()}</option>
                        {/foreach}
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="observer">{sensor_translate('Observer', 'config')}</label>
                <select class="form-control" name="observer.id" id="observer" data-placeholder="{sensor_translate('Any', 'config')}">
                    <option></option>
                    <option value="reporter_as_observer">{sensor_translate('Operator who opened the issue', 'config')}</option>
                    <optgroup label="Gruppi">
                        {foreach $groups.children as $item}
                            <option value="{$item.id}">{$item.name|wash()}</option>
                        {/foreach}
                    </optgroup>
                    <optgroup label="Operatori">
                        {foreach $operators.children as $item}
                            <option value="{$item.id}">{$item.name|wash()}</option>
                        {/foreach}
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="criterion_category">{sensor_translate('Category', 'config')}</label>
                <select class="select form-control" name="category.id" id="category" data-placeholder="{sensor_translate('Any', 'config')}">
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
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="pull-right" style="margin-left: 10px"><a class="btn btn-info" href="{concat('exportas/csv/sensor_scenario/',$scenario_parent_node.node_id)|ezurl(no)}">{sensor_translate('Export to CSV', 'config')}</a></div>
            <div class="pull-right"><a class="btn btn-danger" id="add" data-add-parent="{$scenario_parent_node.node_id}" data-add-class="sensor_scenario" href="#"><i class="fa fa-plus"></i> {sensor_translate('Add new', 'config')}</a></div>
            <p class="text-muted">
                <strong>{sensor_translate('Warning:', 'config')}</strong> {sensor_translate('the scenario that coincides with the greatest number of conditions is executed.', 'config')}<br/>
                {sensor_translate('Under the same number of conditions, the scenario with the lower ID is applied', 'config')}
            </p>
        </div>
    </div>
    <div data-items></div>
</div>

{literal}
<script id="tpl-data-spinner" type="text/x-jsrender">
<div class="col-xs-12 spinner text-center">
    <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
</div>
</script>
<script id="tpl-data-results" type="text/x-jsrender">
{{if totalCount > 0}}
<div class="row">
    <div class="col-xs-12">
        <div class="pagination-container text-center" aria-label="{{:~sensorTranslate('Navigation')}}">
            {{if pageCount > 1}}
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
            {{/if}}
            <ul class="pagination pull-right">
                <li class="page-item">
                    <a data-batch class="btn btn-warning" href="#">{{:~sensorTranslate('Edit')}} {{:totalCount}} <span style="text-transform:lowercase">{{:~sensorTranslate('Automations')}}</span></a>
                </li>
            </ul>
        </div>
    </div>
</div>
{{/if}}
<div class="row">
    {{if totalCount == 0}}
        <div class="col-xs-12 text-center">
            <i class="fa fa-times"></i> {{:~sensorTranslate('No content')}}
        </div>
    {{else}}
    <div class="col-xs-12">
        <table class="table table-striped">
            <thead>
                <th width="1">#</th>
                <th>{{:~sensorTranslate('Event')}}</th>
                <th>{{:~sensorTranslate('Conditions')}}</th>
                <th>{{:~sensorTranslate('Assignments')}}</th>
                <th>{{:~sensorTranslate('Expiration')}}</th>
                <th></th>
                <th></th>
            </thead>
            <tbody>
            {{for searchHits}}
                <tr>
                    <th>
                        {{:metadata.id}}
                    </th>
                    <td>
                        <ul class="list-unstyled">
                            {{for ~eventName(~i18n(data, 'triggers'))}}<li>{{:#data}}</li>{{/for}}
                        </ul>
                    </td>
                    <td>
                        <ul class="list-unstyled">
                            {{if ~i18n(data, 'criterion_area')}}<li><strong>{{:~sensorTranslate('Area')}}:</strong> {{for ~i18n(data, 'criterion_area')}}{{:~i18n(name)}} {{/for}}</li>{{/if}}
                            {{if ~i18n(data, 'criterion_category')}}<li><strong>{{:~sensorTranslate('Category')}}:</strong> {{for ~i18n(data, 'criterion_category')}}{{:~i18n(name)}} {{/for}}</li>{{/if}}
                            {{if ~i18n(data, 'criterion_type')}}<li><strong>{{:~sensorTranslate('Type')}}:</strong> {{:~i18n(data, 'criterion_type')}}</li>{{/if}}
                            {{if ~i18n(data, 'criterion_reporter_group')}}<li><strong>{{:~sensorTranslate('Group of operator who opened the issue')}}:</strong> {{for ~i18n(data, 'criterion_reporter_group')}}{{:~i18n(name)}} {{/for}}</li>{{/if}}
                        </ul>
                    </td>
                    <td>
                        <ul class="list-unstyled">
                            {{if ~i18n(data, 'approver') || ~i18n(data, 'reporter_as_approver')}}
                                <li><strong>{{:~sensorTranslate('Reference for the citizen')}}:</strong>
                                {{if ~i18n(data, 'approver')}}{{for ~i18n(data, 'approver')}}{{:~i18n(name)}} {{/for}}{{/if}}
                                {{if ~i18n(data, 'reporter_as_approver')}}<em>{{:~sensorTranslate('Operator who opened the issue')}}</em>{{/if}}
                                </li>
                            {{/if}}
                            {{if ~i18n(data, 'owner_group')}}
                                <li><strong>{{:~sensorTranslate('Group of operators in charge')}}:</strong> {{for ~i18n(data, 'owner_group')}}{{:~i18n(name)}} {{/for}}</li>
                            {{/if}}
                            {{if ~i18n(data, 'owner') || ~i18n(data, 'reporter_as_owner') || ~i18n(data, 'random_owner')}}
                                <li><strong>{{:~sensorTranslate('Operator in charge')}}:</strong>
                                    {{if ~i18n(data, 'owner')}}{{for ~i18n(data, 'owner')}}{{:~i18n(name)}} {{/for}}{{/if}}
                                    {{if ~i18n(data, 'reporter_as_owner')}}<em>{{:~sensorTranslate('Operator who opened the issue')}}</em>{{/if}}
                                    {{if ~i18n(data, 'random_owner')}}<em>{{:~sensorTranslate('Random operator')}}</em>{{/if}}
                                </li>
                            {{/if}}
                            {{if ~i18n(data, 'observer') || ~i18n(data, 'reporter_as_observer')}}
                                <li><strong>{{:~sensorTranslate('Observer')}}:</strong>
                                    {{if ~i18n(data, 'observer')}}{{for ~i18n(data, 'observer')}}{{:~i18n(name)}} {{/for}}{{/if}}
                                    {{if ~i18n(data, 'reporter_as_observer')}}<em>{{:~sensorTranslate('Operator who opened the issue')}}</em>{{/if}}
                                </li>
                            {{/if}}
                            {{if ~i18n(data, 'category')}}
                                <li><strong>{{:~sensorTranslate('Category')}}:</strong>
                                    {{if ~i18n(data, 'category')}}{{for ~i18n(data, 'category')}}{{:~i18n(name)}} {{/for}}{{/if}}
                                </li>
                            {{/if}}
                        </ul>
                    </td>
                    <td>
                        {{if ~i18n(data, 'expiry')}}
                            {{:~i18n(data, 'expiry')}} {{:~sensorTranslate('days')}}
                        {{/if}}
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
        var wrapper = $('#automations');
        var resultsContainer = wrapper.find('[data-items]');
        var limitPagination = 20;
        var currentPage = 0;
        var queryPerPage = [];
        var template = $.templates('#tpl-data-results');
        var spinner = $($.templates("#tpl-data-spinner").render({}));

        wrapper.find('select').select2({
            width: '100%',
            allowClear: true,
            templateResult: function (item) {
                var style = item.element ? $(item.element).attr('style') : '';
                return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
            }
        }).on('select2:select', function (e) {
            loadContents();
        }).on('select2:unselect', function (e) {
            $(this).val('');
            loadContents();
        });

        var buildQuery = function () {
            var query = '';
            wrapper.find('select').each(function (){
                var value = $(this).val();
                if (value.length > 0) {
                    if ($.inArray(value, ['reporter_as_approver', 'reporter_as_owner', 'reporter_as_observer', 'random_owner']) > -1) {
                        query += value + ' = 1 and ';
                    } else if ($(this).attr('name') === 'criterion_type') {
                        query += $(this).attr('name') + ' = \'' + $(this).val() + '\' and ';
                    } else if ($(this).attr('name') === 'triggers') {
                        query += '(triggers in [' + $(this).val() + '] or raw[triggers_lk] in [' + $(this).val() + ']) and ';
                    } else {
                        query += $(this).attr('name') + ' in [' + $(this).val() + '] and ';
                    }
                }
                // console.log($(this).attr('name'), $(this).val());
            });
            query += 'classes [sensor_scenario] ';
            query += 'sort [raw[criteria_count_i]=>desc,id=>asc]';
            console.log(query);
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

                var renderData = $(template.render(response));
                resultsContainer.html(renderData);
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
                renderData.find('[data-batch]').on('click', function(e){
                    $('#item').opendataForm({
                        query: baseQuery
                    },{
                        connector: 'batch-scenarios',
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
                        scrollTop: wrapper.offset().top
                    }, 1000);
                    e.preventDefault();
                });
            });
        };
        wrapper.find('select').val(null).trigger('change');
        loadContents();

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
    });
</script>
{/literal}
