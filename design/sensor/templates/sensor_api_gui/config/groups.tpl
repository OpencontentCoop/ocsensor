<form class="row form"></form>
<div style="margin: 20px 0"
     data-parent="{$groups_parent_node.node_id}"
     data-classes="{$group_class}"
     data-limit="20"
     data-redirect="/sensor/config/groups"></div>

<div class="pull-left add-class">
    <a class="btn btn-info" href="{concat('exportas/csv/',$group_class,'/',$groups_parent_node.node_id)|ezurl(no)}">{sensor_translate('Export to CSV', 'config')}</a>
</div>
<div class="pull-right add-class">
    <a class="btn btn-danger" id="add" data-add-parent="{$groups_parent_node.node_id}" data-add-class="{$group_class}" href="{concat('add/new/', $group_class,'/?parent=',$groups_parent_node.node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {sensor_translate('Add new', 'config')}</a>
</div>

{literal}
<script>
  $(document).ready(function () {
    $('#add').on('click', function (e) {
      $('#item').opendataFormCreate({
        class: $(this).data('add-class'),
        parent: $(this).data('add-parent')
      }, {
        onBeforeCreate: function () {
          $('#modal').modal('show');
        },
        onSuccess: function () {
          $('#modal').modal('hide');
          datatable.datatable.ajax.reload(null,false);
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

    var buildQuery = function () {
      var classQuery = '';
      if (classes.length) {
        classQuery = 'classes [' + classes + ']';
      }
      var query = classQuery + ' subtree [' + subtree + '] and raw[meta_main_node_id_si] !in [' + subtree + ']';
      query += ' sort [name=>asc]';

      return query;
    };

    var order = [[2, 'asc']];
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
          {data: "metadata.id", name: 'id', title: '', "searchable": false, "orderable": false, width: '1'},
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
              return '<img src="/sensor/avatar/'+data+'" class="img-circle" style="width: 30px; height: 30px;max-width:none" />';

            },
            targets: [1]
          },
          {
            render: function (data, type, row) {
              return '<p style="margin: 0;font-weight: bold"">'+$.opendataTools.helpers.i18n(row.metadata.name)+'</p>';
            },
            targets: [2]
          },
          {
            render: function (data, type, row) {
              var date = moment(data, moment.ISO_8601);
              return date.format('DD/MM/YYYY');

            },
            targets: [3,4]
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
            targets: [5]
          },
          {
            render: function (data, type, row) {
              let output = '<span style="white-space:nowrap">';
              output += '<a style="width: 35px;" class="btn btn-link btn-sm text-black" data-object="' + row.metadata.id + '"><i class="fa fa-eye"></i></a>';
              if (row.metadata?.userAccess?.canEdit) {
                output += '<a style="width: 35px;" class="btn btn-link btn-sm text-black" href="#" data-edit="' + row.metadata.id + '"><i class="fa fa-pencil"></i></a>';
              }
              if (row.metadata?.userAccess?.canRemove) {
                output += '<a style="width: 35px;" class="btn btn-link btn-sm text-black" href="#" data-remove="' + row.metadata.id + '"><i class="fa fa-trash"></i></a>';
              }
              output += '</span>';
              return output;
            },
            targets: [6]
          },
        ],
      }
    }).on('draw.dt', function (e, settings) {
      let renderData = $(e.currentTarget);
      renderData.find('[data-object]').on('click', function (e) {
        $('#item').opendataFormView({
          object: $(this).data('object')
        }, {
          onBeforeCreate: function () {
            $('#modal').modal('show');
          }
        });
        e.preventDefault();
      });
      renderData.find('[data-edit]').on('click', function (e) {
        $('#item').opendataFormEdit({
          object: $(this).data('edit')
        }, {
          onBeforeCreate: function () {
            $('#modal').modal('show');
          },
          onSuccess: function () {
            $('#modal').modal('hide');
            datatable.datatable.ajax.reload(null, false);
          }
        });
        e.preventDefault();
      });
      renderData.find('[data-remove]').on('click', function (e) {
        $('#item').opendataFormDelete({
          object: $(this).data('remove')
        }, {
          onBeforeCreate: function () {
            $('#modal').modal('show');
          },
          onSuccess: function () {
            $('#modal').modal('hide');
            datatable.datatable.ajax.reload(null, false);
          }
        });
        e.preventDefault();
      });
    }).data('opendataDataTable');
    datatable.loadDataTable();
  });
</script>
{/literal}
