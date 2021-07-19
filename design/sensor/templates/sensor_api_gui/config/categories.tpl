{ezcss_require(array('select2.min.css'))}
{ezscript_require(array('select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js')))}
<div class="tab-pane active" id="categories">
    <div data-items>
        <ul class="list-inline text-right">
            <li><a href="#" data-refresh><i class="fa fa-refresh"></i></a></li>
        </ul>
        <table class="categories table table-hover"></table>
        <div class="scenarios panel panel-default" style="display: none">
            <div class="panel-heading">
                <a class="close pull-right close-scenario" href="#"><i class="fa fa-times"></i></a>
                Impostazioni all'assegnazione della categoria <strong data-placeholder="name"></strong>
            </div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Zona</th>
                        <th>Riferimento</th>
                        <th>Gruppo di incaricati</th>
                        <th>Incaricato</th>
                        <th>Osservatore</th>
                        <th>Scadenza</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="categories-buttons">
    <div class="pull-left"><a class="btn btn-info" href="{'exportas/custom/sensor_categories'|ezurl(no)}">{'Esporta in CSV'|i18n('sensor/config')}</a></div>
    <div class="pull-right"><a class="btn btn-danger" id="add" data-add-parent="{$categories_parent_node.node_id}" data-add-class="sensor_category" href="{concat('add/new/sensor_category/?parent=',$categories_parent_node.node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi'|i18n('sensor/config')}</a></div>
</div>

{literal}
<script id="tpl-data-spinner" type="text/x-jsrender">
<tr>
    <td colspan="8" class="text-center">
        <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
    </td>
</tr>
</script>
<script id="tpl-data-results" type="text/x-jsrender">
{{for children ~parent_node_id=node_id ~can_create=can_create ~baseUrl=baseUrl ~redirect=redirect ~locale=locale}}
<tr>
  <th width="1">{{:id}}</th>
  <td>
      <span style="padding-left:{{:(level*20)}}px">
        {{if level == 0}}<strong>{{/if}}
        {{:name}} {{if group}} <span class="label label-default">{{:group}}</span>{{/if}}
        {{if level == 0}}</strong>{{/if}}
      </span>
  </td>
  <td>
	{{for languages}}
	    <img src="/share/icons/flags/{{:#data}}.gif" />
    {{/for}}
  </td>
  <td width="1"><a href="#" data-object="{{:id}}"><i class="fa fa-eye"></i></a></td>
  <td width="1"><a href="#" data-scenarios="{{:id}}" data-name="{{:name}}"><i class="fa fa-android"></i></a></td>
  <td width="1">
  {{if can_edit}}
    <a href="#" data-edit="{{:id}}"><i class="fa fa-pencil"></i></a>
  {{/if}}
  </td>
  <td width="1">
    {{if can_remove}}
    <a href="#" data-remove="{{:id}}"><i class="fa fa-trash"></i></a>
    {{/if}}
  </td>
  <td width="1">
    {{if children.length > 0}}
      <a href="{{:~baseUrl}}/websitetoolbar/sort/{{:node_id}}"><i class="fa fa-sort-alpha-asc "></i>
    {{/if}}
  </td>
  <td width="1">
    {{if ~can_create && level == 0}}
    <a data-create-parent="{{:node_id}}" data-create-class="{{:type}}" href="{{:~baseUrl}}/openpa/add/{{:type}}/?parent={{:node_id}}"><i class="fa fa-plus"></i></a>
    {{/if}}
  </td>
</tr>
{{if children.length > 0}}
    {{include tmpl="#tpl-data-results"/}}
{{/if}}
{{/for}}
</script>
<script id="tpl-scenario-results" type="text/x-jsrender">
{/literal}{foreach $areas.children as $item}{foreach $item.children as $area}{literal}
<tr data-area="{/literal}{$area.id|wash()}{literal}">
    <th>{/literal}{$area.name|wash()}{literal}</th>
    <td class="approver">
        {{if area_{/literal}{$area.id|wash()}{literal} && area_{/literal}{$area.id|wash()}{literal}.assignments.approver.length}}
            {{for area_{/literal}{$area.id|wash()}{literal}.assignments.approver}}{{:name}} {{/for}}
        {{/if}}
        {{if area_{/literal}{$area.id|wash()}{literal} && area_{/literal}{$area.id|wash()}{literal}.assignments.reporter_as_approver}}<em>Operatore segnalatore</em>{{/if}}
    </td>
    <td class="owner_group">
        {{if area_{/literal}{$area.id|wash()}{literal} && area_{/literal}{$area.id|wash()}{literal}.assignments.owner_group.length}}
            {{for area_{/literal}{$area.id|wash()}{literal}.assignments.owner_group}}{{:name}} {{/for}}
        {{/if}}
    </td>
    <td class="owner">
        {{if area_{/literal}{$area.id|wash()}{literal} && area_{/literal}{$area.id|wash()}{literal}.assignments.owner.length}}
            {{for area_{/literal}{$area.id|wash()}{literal}.assignments.owner}}{{:name}} {{/for}}
        {{/if}}
        {{if area_{/literal}{$area.id|wash()}{literal} && area_{/literal}{$area.id|wash()}{literal}.assignments.reporter_as_owner}}<em>Operatore segnalatore</em>{{/if}}
        {{if area_{/literal}{$area.id|wash()}{literal} && area_{/literal}{$area.id|wash()}{literal}.assignments.random_owner}}<em>Operatore casuale</em>{{/if}}
    </td>
    <td class="observer">
        {{if area_{/literal}{$area.id|wash()}{literal} && area_{/literal}{$area.id|wash()}{literal}.assignments.observer.length}}
            {{for area_{/literal}{$area.id|wash()}{literal}.assignments.observer}}{{:name}} {{/for}}
        {{/if}}
        {{if area_{/literal}{$area.id|wash()}{literal} && area_{/literal}{$area.id|wash()}{literal}.assignments.reporter_as_observer}}<em>Operatore segnalatore</em>{{/if}}
    </td>
    <td class="expiry">
        {{if area_{/literal}{$area.id|wash()}{literal} && area_{/literal}{$area.id|wash()}{literal}.expiry}}
            {{:area_{/literal}{$area.id|wash()}{literal}.expiry}} giorni
        {{/if}}
    </td>
    <td width="1">
        {{if area_{/literal}{$area.id|wash()}{literal}}}
            <a href="#" data-editscenario="{{:area_{/literal}{$area.id|wash()}{literal}.id}}"><i class="fa fa-pencil"></i></a>
        {{else}}
            <a href="#" data-createscenario="{/literal}{$area.id|wash()}{literal}"><i class="fa fa-plus"></i></a>
        {{/if}}
    </td>
    <td width="1">{{if area_{/literal}{$area.id|wash()}{literal}}}<a href="#" data-removescenario="{{:area_{/literal}{$area.id|wash()}{literal}.id}}"><i class="fa fa-trash"></i></a>{{/if}}</td>
</tr>
{/literal}{/foreach}{/foreach}{literal}
<tr class="bg-warning">
    <td><em>default</em></td>
    <td class="approver">
        {{if default && default.assignments.approver.length}}{{for default.assignments.approver}}{{:name}} {{/for}}{{/if}}
        {{if default && default.assignments.reporter_as_approver}}<em>Operatore segnalatore</em>{{/if}}
    </td>
    <td class="owner_group">
        {{if default && default.assignments.owner_group.length}}{{for default.assignments.owner_group}}{{:name}} {{/for}}{{/if}}
    </td>
    <td class="owner">
        {{if default && default.assignments.owner.length}}{{for default.assignments.owner}}{{:name}} {{/for}}{{/if}}
        {{if default && default.assignments.reporter_as_owner}}<em>Operatore segnalatore</em>{{/if}}
        {{if default && default.assignments.random_owner}}<em>Operatore casuale</em>{{/if}}
    </td>
    <td class="observer">
        {{if default && default.assignments.observer.length}}{{for default.assignments.observer}}{{:name}} {{/for}}{{/if}}
        {{if default && default.assignments.reporter_as_observer}}<em>Operatore segnalatore</em>{{/if}}
    </td>
    <td class="expiry">
        {{if default && default.expiry}}
            {{:default.expiry}} giorni
        {{/if}}
    </td>
    <td width="1">
        {{if default}}
            <a href="#" data-editscenario="{{:default.id}}"><i class="fa fa-pencil"></i></a>
        {{else}}
            <a href="#" data-createscenario><i class="fa fa-plus"></i></a>
        {{/if}}
    </td>
    <td width="1">{{if default}}<a href="#" data-removescenario="{{:default.id}}"><i class="fa fa-trash"></i></a>{{/if}}</td>
</tr>
</script>
<script id="tpl-edit-scenario-row" type="text/x-jsrender">
    <th>{{:name}}</th>
    <td class="approver"></td>
    <td class="owner_group"></td>
    <td class="owner"></td>
    <td class="observer"></td>
    <td class="expiry"></td>
    <td width="1"><a href="#" class="store-scenario"><i class="fa fa-save"></i></a></td>
    <td width="1"><a href="#" class="close-edit-scenario"><i class="fa fa-times"></i></a></td>
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

        var resultsContainer = $('[data-items]');
        var table = resultsContainer.find('table.categories');
        var buttons = $('.categories-buttons');
        var scenario = resultsContainer.find('.scenarios');
        var scenarioTable = scenario.find('table tbody');
        var form = resultsContainer.prev();
        var selfCursor, nextCursor;
        var template = $.templates('#tpl-data-results');
        var spinner = $($.templates("#tpl-data-spinner").render({}));
        var scenarioTemplate = $.templates('#tpl-scenario-results');
        var scenarioEditTemplate = $.templates('#tpl-edit-scenario-row');
        var operators = JSON.parse('{/literal}{$operators|wash(javascript)}{literal}');
        var groups = JSON.parse('{/literal}{$groups|wash(javascript)}{literal}');

        var loadScenario = function(id, name){
            table.hide();
            buttons.hide();
            scenario.find('[data-placeholder="name"]').text(name);
            scenario.show();
            scenarioTable.html(spinner);
            $.getJSON('/api/sensor_gui/scenarios', {
                trigger: 'on_add_category',
                category: id
            }, function (scenarios) {
                var data = {};
                $.each(scenarios, function (){
                    if (this.criteria.hasOwnProperty('area')){
                        data['area_' + this.criteria.area[0]] = this;
                    }else{
                        data['default'] = this;
                    }
                });
                var renderData = $(scenarioTemplate.render(data));
                renderData.find('[data-removescenario]').on('click', function(e){
                    $('#item').opendataFormDelete({
                        object: $(this).data('removescenario')
                    },{
                        onBeforeCreate: function(){
                            $('#modal').modal('show');
                        },
                        onSuccess: function () {
                            $('#modal').modal('hide');
                            loadScenario(id,name);
                        }
                    });
                    e.preventDefault();
                });
                renderData.find('[data-createscenario]').on('click', function(e){
                    $('[data-createscenario]').hide();
                    $('[data-editscenario]').hide();
                    $('[data-removescenario]').hide();
                    var row = $(this).parents('tr');
                    var editData =  $(scenarioEditTemplate.render({
                        name: row.find('th').text()
                    }));
                    row.html(editData);
                    loadScenarioEdit(row);
                    editData.find('a').on('click', function (event){
                        if ($(this).hasClass('store-scenario')){
                            registerScenario(id, name, row);
                        }else {
                            loadScenario(id, name);
                        }
                        e.preventDefault();
                    });
                    e.preventDefault();
                });
                renderData.find('[data-editscenario]').on('click', function(e){
                    $('[data-createscenario]').hide();
                    $('[data-editscenario]').hide();
                    $('[data-removescenario]').hide();
                    var row = $(this).parents('tr');
                    var scenarioId = $(this).data('editscenario');
                    var currentData = $.grep(scenarios, function (scenario){return scenario.id === scenarioId;})[0];
                    var editData =  $(scenarioEditTemplate.render({
                        name: row.find('th').text()
                    }));
                    row.html(editData);
                    loadScenarioEdit(row, currentData);
                    editData.find('a').on('click', function (event){
                        if ($(this).hasClass('store-scenario')){
                            registerScenario(id, name, row, scenarioId);
                        }else {
                            loadScenario(id, name);
                        }
                        e.preventDefault();
                    });
                    e.preventDefault();
                });
                scenarioTable.html(renderData);
            });
        };
        var registerScenario = function (id, name, row, scenarioId){
            var payload = {
                assignments: {},
                triggers: ['on_add_category'],
                criteria:{
                    category: [id],
                    area: [row.data('area')]
                },
                expiry: null
            };
            var hasContent = false;
            row.find('select').each(function (){
                var value = $(this).val();
                if (value && value.length > 0) {
                    if (typeof value === 'string'){
                        value = [value];
                    }
                    payload.assignments[$(this).attr('name')] = value;
                    hasContent = true;
                }
            });
            row.find('input[type="checkbox"]').each(function (){
                payload.assignments[$(this).attr('name')] = $(this).is(':checked');
                if ($(this).is(':checked')) hasContent = true;
            });
            var expiry = row.find('input[type="number"]').val();
            if (expiry){
                payload.expiry = parseInt(expiry);
            }
            if (hasContent){
                var endpoint = '/api/sensor_gui/scenarios';
                if (scenarioId){
                    endpoint += '/'+scenarioId;
                }

                var csrfToken;
                var tokenNode = document.getElementById('ezxform_token_js');
                if ( tokenNode ){
                    csrfToken = tokenNode.getAttribute('title');
                }

                $.ajax({
                    type: scenarioId ? 'PUT' : 'POST',
                    async: false,
                    url: endpoint,
                    headers: {'X-CSRF-TOKEN': csrfToken},
                    data: JSON.stringify(payload),
                    success: function (data) {
                        loadScenario(id, name);
                    },
                    error: function (data) {
                        alert(data);
                        loadScenario(id, name);
                    },
                    contentType: "application/json",
                    dataType: 'json'
                });
            }else{
                loadScenario(id, name);
            }
        }
        var loadDataInSelect = function (select, type, data){
            var select2Options = {
                width: '100%',
                allowClear: true,
                templateResult: function (item) {
                    var style = item.element ? $(item.element).attr('style') : '';
                    return $('<span style="display:inline-block;' + style + '">' + item.text + '</span>');
                }
            };
            select.select2(select2Options)
            if (type === 1 && groups.children.length > 0) {
                select.html('').append(new Option('', '', false, false)).trigger('change');
                $.each(groups.children, function () {
                    var selected = $.inArray(this.id, $.map(data, function(val){return val.id})) > -1;
                    var newOption = new Option(this.name, this.id, selected, selected);
                    if (select.find('option[value="' + this.id + '"]').length === 0) {
                        select.append(newOption);
                    }
                });
                select.trigger('change');
                select.trigger('select2:select');
            }
            if (type === 2 && operators.children.length > 0) {
                select.html('').append(new Option('', '', false, false)).trigger('change');
                $.each(operators.children, function () {
                    var selected = $.inArray(this.id, $.map(data, function(val){return val.id})) > -1;
                    var newOption = new Option(this.name, this.id, selected, selected);
                    if (select.find('option[value="' + this.id + '"]').length === 0) {
                        select.append(newOption);
                    }
                });
                select.trigger('change');
                select.trigger('select2:select');
            }
            if (type === 3 && operators.children.length > 0) {
                select.html('').append(new Option('', '', false, false)).trigger('change');
                $.each(groups.children, function () {
                    var selected = $.inArray(this.id, $.map(data, function(val){return val.id})) > -1;
                    var newOption = new Option(this.name, this.id, selected, selected);
                    if (select.find('option[value="' + this.id + '"]').length === 0) {
                        select.append(newOption);
                    }
                });
                $.each(operators.children, function () {
                    var selected = $.inArray(this.id, $.map(data, function(val){return val.id})) > -1;
                    var newOption = new Option(this.name, this.id, selected, selected);
                    if (select.find('option[value="' + this.id + '"]').length === 0) {
                        select.append(newOption);
                    }
                });
                select.trigger('change');
                select.trigger('select2:select');
            }

            return select;
        };
        var onChangeOwnerSelect = function (currentUser, groupAssignSelect) {
            if (parseInt(currentUser) > 0) {
                var currentGroupSelected = groupAssignSelect.val();
                $.get('/api/sensor_gui/operators/' + currentUser, function (data) {
                    if ($.inArray(parseInt(currentGroupSelected), data.groups) === -1) {
                        if (data.groups.length > 0) {
                            groupAssignSelect.val(data.groups[0]).trigger('change');
                        } else {
                            groupAssignSelect.val('').trigger('change');
                        }
                    }
                });
            }
        };
        var onChangeOwnerGroupSelect = function (currentGroup, userAssignSelect) {
            if (parseInt(currentGroup) > 0) {
                var currentUserSelected = userAssignSelect.val();
                userAssignSelect.html('').append(new Option('', '', false, false)).trigger('change');
                $.get('/api/sensor_gui/groups/' + currentGroup, function (data) {
                    $.each(data, function () {
                        var selected = parseInt(currentUserSelected) === parseInt(this.id);
                        var newOption = new Option(this.name, this.id, selected, selected);
                        if (userAssignSelect.find('option[value="' + this.id + '"]').length === 0) {
                            userAssignSelect.append(newOption).trigger('change');
                        }
                    });
                });
            }
        };
        var loadScenarioEdit = function(row, data){
            var approverSelect = $('<select name="approver" data-placeholder="Nessuno"></select>').appendTo(row.find('.approver'));
            loadDataInSelect(approverSelect, 1, data ? data.assignments.approver : []);
            var reporterApproverCheckbox = $('<label class="checkbox"><input type="checkbox" name="reporter_as_approver" /> Imposta l\'operatore segnalatore</label>')
                .appendTo(row.find('.approver'))
                .find('input');
            reporterApproverCheckbox.on('change', function (e){
                if ($(this).is(':checked')){
                    approverSelect.val(null).trigger('change').attr('disabled', true);
                }else{
                    approverSelect.attr('disabled', false);
                }
            });
            if (data && data.assignments.reporter_as_approver){
                reporterApproverCheckbox.attr('checked', true);
            }
            var ownerGroupSelect = $('<select name="owner_group" data-placeholder="Nessuno"></select>').appendTo(row.find('.owner_group'));
            loadDataInSelect(ownerGroupSelect, 1, data ? data.assignments.owner_group : []);
            var ownerSelect = $('<select name="owner" data-placeholder="Nessuno"></select>').appendTo(row.find('.owner'));
            loadDataInSelect(ownerSelect, 2, data ? data.assignments.owner : []);

            ownerGroupSelect.on('select2:select', function (e) {
                onChangeOwnerGroupSelect($(e.currentTarget).val(), ownerSelect);
            });
            ownerSelect.on('select2:select', function (e) {
                onChangeOwnerSelect($(e.currentTarget).val(), ownerGroupSelect);
            });

            var reporterOwnerCheckbox = $('<label class="checkbox"><input type="checkbox" name="reporter_as_owner" /> Imposta l\'operatore segnalatore</label>')
                .appendTo(row.find('.owner'))
                .find('input');
            var randomOwnerCheckbox = $('<label class="checkbox"><input type="checkbox" name="random_owner" /> Imposta un operatore casuale</label>')
                .appendTo(row.find('.owner'))
                .find('input');
            reporterOwnerCheckbox.on('change', function (e){
                if ($(this).is(':checked')){
                    ownerGroupSelect.val(null).trigger('change').attr('disabled', true);
                    ownerSelect.val(null).trigger('change').attr('disabled', true);
                    randomOwnerCheckbox.attr('checked', false).attr('disabled', true);
                }else{
                    if (ownerGroupSelect.val()) {
                        randomOwnerCheckbox.attr('disabled', false);
                    }
                    ownerGroupSelect.attr('disabled', false);
                    ownerSelect.attr('disabled', false);
                }
            });
            randomOwnerCheckbox.on('change', function (e){
                if ($(this).is(':checked')){
                    ownerSelect.val(null).trigger('change').attr('disabled', true);
                    reporterOwnerCheckbox.attr('checked', false).attr('disabled', true);
                }else{
                    reporterOwnerCheckbox.attr('disabled', false);
                    ownerSelect.attr('disabled', false);
                }
            });
            if (data && data.assignments.reporter_as_owner){
                reporterOwnerCheckbox.attr('checked', true).trigger('change');
            }
            if (data && data.assignments.random_owner){
                randomOwnerCheckbox.attr('checked', true).trigger('change');
                reporterOwnerCheckbox.attr('checked', false).attr('disabled', true);
            }
            if (data && data.assignments.owner_group.length){
                randomOwnerCheckbox.attr('disabled', false);
            }else{
                randomOwnerCheckbox.attr('checked', false).attr('disabled', true);
            }
            ownerGroupSelect.on('change', function (){
               if (ownerGroupSelect.val()){
                   randomOwnerCheckbox.attr('disabled', false);
               }else{
                   randomOwnerCheckbox.attr('checked', false).attr('disabled', true);
               }
            });
            var observerSelect = $('<select name="observer" data-placeholder="Nessuno" multiple></select>').appendTo(row.find('.observer'));
            loadDataInSelect(observerSelect, 3, data ? data.assignments.observer : []);
            var reporterObserverCheckbox = $('<label class="checkbox"><input type="checkbox" name="reporter_as_observer" /> Aggiungi l\'operatore segnalatore</label>')
                .appendTo(row.find('.observer'))
                .find('input');
            if (data && data.assignments.reporter_as_observer){
                reporterObserverCheckbox.attr('checked', true).trigger('change');
            }
            var expiryInput = $('<input type="number" name="expiry" class="form-control"/>').appendTo(row.find('.expiry'));
            if (data && data.expiry){
                expiryInput.val(data.expiry);
            }
        };
        var closeScenario = function(id, name){
            table.show();
            buttons.hide();
            scenario.find('[data-placeholder]').text('');
            scenario.hide();
        };
        $('.close-scenario').on('click', function(e){
            closeScenario();
            e.preventDefault();
        });

        var loadContents = function(){
            table.html(spinner);
            $.getJSON('/api/sensor_gui/category_tree', function (response) {
                response.baseUrl = $.opendataTools.settings('accessPath');
                response.redirect = $.opendataTools.settings('accessPath')+'/sensor/config/categories';
                response.locale = $.opendataTools.settings('language');
                var renderData = $(template.render(response));
                table.html(renderData);

                renderData.find('[data-object]').on('click', function(e){
                    $('#item').opendataFormView({
                        object: $(this).data('object')
                    },{
                        onBeforeCreate: function(){
                            $('#modal').modal('show')
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
                renderData.find('[data-create-parent]').on('click', function(e){
                    $('#item').opendataFormCreate({
                        class: $(this).data('create-class'),
                        parent: $(this).data('create-parent')
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
                renderData.find('[data-scenarios]').on('click', function(e){
                    loadScenario($(this).data('scenarios'), $(this).data('name'));
                    e.preventDefault();
                });
            });
        };
        //form[0].reset();
        loadContents();

        form.find('button[type="submit"]').on('click', function(e){
            form.find('button[type="reset"]').removeClass('hide');
            loadContents();
            e.preventDefault();
        });
        form.find('button[type="reset"]').on('click', function(e){
            form[0].reset();
            form.find('button[type="reset"]').addClass('hide');
            loadContents();
            e.preventDefault();
        });
        $('[data-refresh]').on('click', function(e){
            loadContents();
            e.preventDefault();
        });
    });
</script>
{/literal}