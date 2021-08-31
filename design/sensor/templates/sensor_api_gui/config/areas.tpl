{ezcss_require(array('select2.min.css'))}
{ezscript_require(array('select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js')))}
<div class="tab-pane active" id="areas">
    <div data-items>
        <table class="areas table table-hover"></table>
        <div class="scenarios panel panel-default" style="display: none">
            <div class="panel-heading">
                <a class="close pull-right close-scenario" href="#"><i class="fa fa-times"></i></a>
                Impostazioni alla creazione di una segnalazione nella zona <strong data-placeholder="name"></strong>
            </div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Riferimento</th>
                        <!--<th>Gruppo di incaricati</th>
                        <th>Incaricato</th>-->
                        <th>Osservatore</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="categories panel panel-default" style="display: none">
            <div class="panel-heading">
                <a class="close pull-right close-categories" href="#"><i class="fa fa-times"></i></a>
                Categorie abilitate per la zona <strong data-placeholder="name"></strong>
            </div>
            <table class="table table-hover">
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div class="areas-buttons">
    <div class="pull-left"><a class="btn btn-info" href="{'exportas/custom/sensor_areas'|ezurl(no)}">{'Esporta in CSV'|i18n('sensor/config')}</a></div>
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
{{for children ~parent_node_id=node_id ~baseUrl=baseUrl ~redirect=redirect ~locale=locale}}
<tr>
  <th width="1">{{:id}}</th>
  <td>
      <span style="padding-left:{{:(level*20)}}px">
        {{if level == 0}}<strong>{{/if}}
        {{:name}}
        {{if level == 0}}</strong>{{/if}}
      </span>
  </td>
  <td>
	{{for languages}}
	    <img src="/share/icons/flags/{{:#data}}.gif" />
    {{/for}}
  </td>
  <td width="1"><a href="#" data-object="{{:id}}"><i class="fa fa-eye"></i></a></td>
  <td width="1">{{if level > 0}}<a href="#" data-categories="{{:id}}" data-name="{{:name}}"><i class="fa fa-tags"></i></a>{{/if}}</td>
  <td width="1">{{if level > 0}}<a href="#" data-scenarios="{{:id}}" data-name="{{:name}}"><i class="fa fa-android"></i></a>{{/if}}</td>
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
    {{if can_create && level == 0}}
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
<tr>
    <td class="approver">
        {{if default && default.assignments.approver.length}}{{for default.assignments.approver}}{{:name}} {{/for}}{{/if}}
        {{if default && default.assignments.reporter_as_approver}}<em>Operatore segnalatore</em>{{/if}}
    </td>
    <!--<td class="owner_group">
        {{if default && default.assignments.owner_group.length}}{{for default.assignments.owner_group}}{{:name}} {{/for}}{{/if}}
    </td>
    <td class="owner">
        {{if default && default.assignments.owner.length}}{{for default.assignments.owner}}{{:name}} {{/for}}{{/if}}
        {{if default && default.assignments.reporter_as_owner}}<em>Operatore segnalatore</em>{{/if}}
        {{if default && default.assignments.random_owner}}<em>Operatore casuale</em>{{/if}}
    </td>-->
    <td class="observer">
        {{if default && default.assignments.observer.length}}{{for default.assignments.observer}}{{:name}} {{/for}}{{/if}}
        {{if default && default.assignments.reporter_as_observer}}<em>Operatore segnalatore</em>{{/if}}
    </td>
    <td width="1">
        {{if default}}
            <a href="#" data-editscenario="{{:default.id}}"><i class="fa fa-pencil"></i></a>
        {{else}}
            <a href="#" data-createscenario><i class="fa fa-plus"></i></a>
        {{/if}}
    </td>
    <td width="1">{{if default}}<a href="#" data-removescenario="{{:default.id}}"><i class="fa fa-trash"></i></a>{{/if}}</td>
    <td width="1"></td>
</tr>
</script>
<script id="tpl-edit-scenario-row" type="text/x-jsrender">
    <td class="approver"></td>
    <!--<td class="owner_group"></td>
    <td class="owner"></td>-->
    <td class="observer"></td>
    <td width="1"><a href="#" class="store-scenario"><i class="fa fa-save"></i></a></td>
    <td width="1"><a href="#" class="close-edit-scenario"><i class="fa fa-times"></i></a></td>
    <td width="1"></td>
</script>
<script id="tpl-area-categories" type="text/x-jsrender">
{{for children ~areaId=areaId ~parentId=id tmpl="#tpl-area-category" /}}
</script>
<script id="tpl-area-category" type="text/x-jsrender">
<tr>
  <td width="1">
    <input type="hidden" name="AreaCategory[{{:~areaId}}][]" value="{{:id}}" />
    <input type="checkbox"
           data-categoryparent="{{:~parentId}}"
           data-category="{{:id}}"
           id="category-{{:id}}"
           name="AreaEnableCategory[{{:~areaId}}][]"
           value="{{:id}}"
           {{if disabled_relations.indexOf(~areaId) == -1}}checked="checked" {{/if}}/>
  </td>
  <td>
      <label for="category-{{:id}}" style="font-weight:normal;display:block;cursor:pointer">
      <span style="padding-left:{{:(level*20)}}px">
        {{if level == 0}}<strong>{{/if}}
        {{:name}}
        {{if level == 0}}</strong>{{/if}}
      </span>
      </label>
  </td>
</tr>
{{if children.length > 0}}
    {{for children ~areaId=~areaId ~parentId=id tmpl="#tpl-area-category" /}}
{{/if}}
</script>
<script>
    $.views.helpers($.opendataTools.helpers);
    $(document).ready(function () {
        $('[data-items]').each(function(){
            var resultsContainer = $(this);
            var table = resultsContainer.find('table.areas');
            var buttons = $('.categories-buttons');
            var scenario = resultsContainer.find('.scenarios');
            var scenarioTable = scenario.find('table tbody');
            var categories = resultsContainer.find('.categories');
            var categoriesTable = categories.find('table tbody');
            var form = resultsContainer.prev();
            var selfCursor, nextCursor;
            var template = $.templates('#tpl-data-results');
            var spinner = $($.templates("#tpl-data-spinner").render({}));
            var scenarioTemplate = $.templates('#tpl-scenario-results');
            var scenarioEditTemplate = $.templates('#tpl-edit-scenario-row');
            var categoriesTemplate = $.templates('#tpl-area-categories');
            var operators = JSON.parse('{/literal}{$operators|wash(javascript)}{literal}');
            var groups = JSON.parse('{/literal}{$groups|wash(javascript)}{literal}');

            var loadScenario = function(id, name){
                closeCategories();
                table.hide();
                buttons.hide();
                scenario.find('[data-placeholder="name"]').text(name);
                scenario.show();
                scenarioTable.html(spinner);
                $.getJSON('/api/sensor_gui/scenarios', {
                    trigger: 'on_create',
                    area: id
                }, function (scenarios) {
                    var data = {};
                    $.each(scenarios, function (){
                        data['default'] = this;
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
                    triggers: ['on_create'],
                    criteria:{
                        area: [id]
                    }
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
                loadDataInSelect(observerSelect, 2, data ? data.assignments.observer : []);
                var reporterObserverCheckbox = $('<label class="checkbox"><input type="checkbox" name="reporter_as_observer" /> Aggiungi l\'operatore segnalatore</label>')
                    .appendTo(row.find('.observer'))
                    .find('input');
                if (data && data.assignments.reporter_as_observer){
                    reporterObserverCheckbox.attr('checked', true).trigger('change');
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

            var loadCategories = function (id, name) {
                closeScenario();
                table.hide();
                buttons.hide();
                categories.find('[data-placeholder="name"]').text(name);
                categories.show();
                categoriesTable.html(spinner);
                $.getJSON('/api/sensor_gui/category_tree', function (response) {
                    response.areaId = parseInt(id);
                    var renderData = $(categoriesTemplate.render(response));
                    categoriesTable.html(renderData);
                    categoriesTable.find('[data-category]').on('change', function(e){
                        var catId = $(this).data('category');
                        var parentCatId = $(this).data('categoryparent');
                        var isChecked = $(this).is(':checked');
                        if (!isChecked) {
                            categoriesTable.find('[data-categoryparent="' + catId + '"]').prop('checked', false);
                        }else if (isChecked) {
                            categoriesTable.find('[data-category="' + parentCatId + '"]').prop('checked', true);
                        }
                        setTimeout(function() {
                            storeAreaCategories(id, name);
                        }, 20);
                    });
                });
            };
            var storeAreaCategories = function (id, name){
                categories.css('opacity', '0.7');
                var disabledCategories = [];
                categoriesTable.find('[data-category]').each(function (){
                    if (!$(this).is(':checked')){
                        disabledCategories.push($(this).val());
                    }
                })
                var csrfToken;
                var tokenNode = document.getElementById('ezxform_token_js');
                if (tokenNode) {
                    csrfToken = tokenNode.getAttribute('title');
                }
                var endpoint = '/api/sensor_gui/areas/'+id+'/disabled_categories';
                $.ajax({
                    type: 'POST',
                    async: false,
                    url: endpoint,
                    headers: {'X-CSRF-TOKEN': csrfToken},
                    data: JSON.stringify(disabledCategories),
                    success: function (data) {
                        //loadCategories(id, name);
                        categories.css('opacity', '1');
                    },
                    error: function (data) {
                        alert(data.responseJSON.error_message);
                        categories.css('opacity', '1');
                        loadCategories(id, name);
                    },
                    contentType: "application/json",
                    dataType: 'json'
                });

            };
            var closeCategories = function(id, name){
                table.show();
                buttons.hide();
                categories.find('[data-placeholder]').text('');
                categories.hide();
            };
            $('.close-categories').on('click', function(e){
                closeCategories();
                e.preventDefault();
            });

            var loadContents = function(){
                table.html(spinner);
                $.getJSON('/api/sensor_gui/area_tree', function (response) {
                    response.baseUrl = $.opendataTools.settings('accessPath');
                    response.redirect = $.opendataTools.settings('accessPath')+'/sensor/config/areas';
                    response.locale = $.opendataTools.settings('language');
                    var renderData = $(template.render(response));
                    table.html(renderData);

                    renderData.find('[data-object]').on('click', function(e){
                        $('#item').opendataFormView({
                            object: $(this).data('object')
                        },{
                            onBeforeCreate: function(){
                                $('#modal').modal('show');
                                setTimeout(function() {
                                    $('#modal .leaflet-container').trigger('click');
                                }, 1000);
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
                    renderData.find('[data-categories]').on('click', function(e){
                        loadCategories($(this).data('categories'), $(this).data('name'));
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
        });
    });
</script>
{/literal}