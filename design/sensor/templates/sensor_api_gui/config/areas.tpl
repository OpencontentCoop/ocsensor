<div class="tab-pane active" id="areas">
    <div data-items>
        <table class="table table-hover"></table>
    </div>
</div>

<div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/sensor_area/',$areas_parent_node.node_id)|ezurl(no)}">{'Esporta in CSV'|i18n('sensor/config')}</a></div>

{literal}
<script id="tpl-data-spinner" type="text/x-jsrender">
<tr>
    <td colspan="7" class="text-center">
        <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
    </td>
</tr>
</script>
<script id="tpl-data-results" type="text/x-jsrender">
{{for children ~parent_node_id=node_id ~can_create=can_create ~baseUrl=baseUrl ~redirect=redirect ~locale=locale}}
<tr>
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
  <td width="1"><a href="#" data-object={{:id}}><i class="fa fa-eye"></i></a></td>
  <td width="1">
  {{if can_edit}}
    <a href="#" data-edit={{:id}}><i class="fa fa-pencil"></i></a>
  {{/if}}
  </td>
  <td width="1">
    {{if can_remove}}
      <a href="#" data-remove={{:id}}><i class="fa fa-trash"></i></a>
    {{/if}}
  </td>
  <td width="1">
    {{if children.length > 0}}
      <a href="{{:~baseUrl}}/websitetoolbar/sort/{{:node_id}}"><i class="fa fa-sort-alpha-asc "></i>
    {{/if}}
  </td>

  <td width="1">
    {{if ~can_create && level == 0}}
    <a href="{{:~baseUrl}}/openpa/add/{{:type}}/?parent={{:node_id}}"><i class="fa fa-plus"></i></a>
    {{/if}}
  </td>
</tr>
{{if children.length > 0}}
    {{include tmpl="#tpl-data-results"/}}
{{/if}}
{{/for}}
</script>
<script>
    $.views.helpers($.opendataTools.helpers);
    $(document).ready(function () {
        $('[data-items]').each(function(){
            var resultsContainer = $(this);
            var table = resultsContainer.find('table');
            var form = resultsContainer.prev();
            var selfCursor, nextCursor;
            var template = $.templates('#tpl-data-results');
            var spinner = $($.templates("#tpl-data-spinner").render({}));

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