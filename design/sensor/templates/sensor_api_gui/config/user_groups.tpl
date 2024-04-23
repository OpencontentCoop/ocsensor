<form class="row form"></form>
<div style="margin: 20px 0"
     data-parent="{$user_groups_parent_node_id}"
     data-classes="{$user_groups_class}"
     data-limit="20"
     data-redirect="/sensor/config/user_groups"></div>

<div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/',$user_groups_class,'/',$user_groups_parent_node_id)|ezurl(no)}">{sensor_translate('Export to CSV', 'config')}</a></div>
<div class="pull-right">
    <a class="btn btn-danger" id="add" data-add-parent="{$user_groups_parent_node_id}" data-add-class="{$user_groups_class}" href="{concat('add/new/', $user_groups_class, '/?parent=',$user_groups_parent_node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {sensor_translate('Add new', 'config')}</a>
</div>

{literal}
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
                      datatable.datatable.ajax.reload(null, false);
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
            var operatorsId = {/literal}{$operator_parent_object_id}{literal};

            var buildQuery = function () {
                var classQuery = '';
                if (classes.length) {
                    classQuery = 'classes [' + classes + ']';
                }
                var query = classQuery + ' subtree [' + subtree + '] and raw[meta_main_node_id_si] !in [' + subtree + '] and id != ' + operatorsId;
                query += ' sort [name=>asc]';

                return query;
            };


          var order = [[1, 'asc']];
          var datatable = resultsContainer.opendataDataTable({
            table: {
              template: '<table class="table table-striped table-sm display no-wrap w-100"></table>'
            },
            builder: {
              query: buildQuery()
            },
            datatable: {
              order: order,
              language: {"url": datatableLanguage},
              ajax: {
                url: "/opendata/api/datatable/search/"
              },
              lengthMenu: [20, 40, 60],
              columns: [
                {data: "metadata.id", name: 'id', title: '#', "searchable": false, "orderable": false, width: '1'},
                {
                  data: "metadata.name." + $.opendataTools.settings('language'),
                  name: 'name',
                  title: '{/literal}{'Name'|i18n( 'design/admin/node/view/full' )}{literal}'
                },
                {
                  data: "metadata.published",
                  name: 'published',
                  title: '{/literal}{'Published'|i18n( 'design/admin/node/view/full' )}{literal}'
                },
                {data: "metadata.modified", name: 'modified', title: '{/literal}{'Modified'|i18n( 'design/admin/node/view/full' )|explode(' ')|implode('&middot;')}{literal}'},
                {data: "metadata.id", name: 'id', title: '', "searchable": false, "orderable": false, width: '1'},
                {
                  data: "metadata.classIdentifier",
                  name: 'class',
                  title: '',
                  "searchable": false,
                  "orderable": false,
                  width: '1'
                }
              ],
              columnDefs: [
                {
                  render: function (data, type, row) {
                    return '<strong>'+data+'</strong>';

                  },
                  targets: [0]
                },
                {
                  render: function (data, type, row) {
                    let output = '<p style="margin: 0;font-weight: bold"">'+$.opendataTools.helpers.i18n(row.metadata.name)+'</p>';
                    return output;
                  },
                  targets: [1]
                },
                {
                  render: function (data, type, row) {
                    var date = moment(data, moment.ISO_8601);
                    return date.format('DD/MM/YYYY');

                  },
                  targets: [2,3]
                },
                {
                  render: function (data, type, row) {
                    let output = '<span style="white-space:nowrap">';
                    var currentTranslations = row.metadata.languages;
                    var translations = [];
                    $.each($.opendataTools.settings('languages'), function () {
                      if ($.inArray('' + this, currentTranslations) >= 0) {
                        output += '<img style="max-width:none;margin-right: 3px" src="/share/icons/flags/' + this + '.gif" />';
                      }
                    });
                    output += '</span>';
                    return output;
                  },
                  targets: [4]
                },
                {
                  render: function (data, type, row) {
                    let output = '<span style="white-space:nowrap">';
                    if (row.metadata?.userAccess?.canEdit) {
                      output += '<a style="width: 35px;" class="btn btn-link btn-sm text-black" data-upload="' + row.metadata.id + '>' +
                        '<i class="fa fa-user"></i>' +
                        '</a>';
                    }
                    if (row.metadata?.userAccess?.canEdit) {
                      output += '<a style="width: 35px;" class="btn btn-link btn-sm text-black" href="#" data-edit="' + row.metadata.id + '"><i class="fa fa-pencil"></i></a>';
                    }
                    if (row.metadata?.userAccess?.canRemove) {
                      output += '<a style="width: 35px;" class="btn btn-link btn-sm text-black" href="#" data-remove="' + row.metadata.id + '"><i class="fa fa-trash"></i></a>';
                    }
                    output += '</span>';
                    return output;
                  },
                  targets: [5]
                },
              ],
            }
          }).on('draw.dt', function (e, settings) {
            let renderData = $(e.currentTarget);
            renderData.find('[data-upload]').on('click', function(e){
              $('#item').opendataForm({
                id: $(this).data('upload')
              },{
                connector: 'import-user',
                onBeforeCreate: function(){
                  $('#modal').modal('show');
                },
                onSuccess: function () {
                  $('#modal').modal('hide');
                  datatable.datatable.ajax.reload(null, false);
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
                  datatable.datatable.ajax.reload(null, false);
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
                  datatable.datatable.ajax.reload(null, false);
                },
                'alpaca': {
                  "connector": {
                    "config": {
                      "connector": 'delete-user-group'
                    }
                  }
                }
              });
              e.preventDefault();
            });
          }).data('opendataDataTable');

          datatable.loadDataTable();
        });
    </script>
{/literal}
