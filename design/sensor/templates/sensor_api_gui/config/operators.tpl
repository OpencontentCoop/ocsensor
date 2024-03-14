<form class="row form">
    {if count($groups)}
        <div class="col-xs-12 col-md-3" style="padding-right: 0">
            <select class="form-control" name="operator_group">
                <option selected="selected" value="">{sensor_translate('Filter by operator group', 'config')}</option>
                {foreach $groups.children as $group}
                    <option value="{$group.id}">{$group.name|wash()}</option>
                {/foreach}
            </select>
        </div>
    {/if}
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
</form>
<div class="table-responsive" style="margin: 20px 0"
     data-parent="{$operator_parent_node.node_id}"
     data-classes="{$operator_class.identifier}"
     data-limit="20"
     data-redirect="/sensor/config/operators"></div>

<div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/',$operator_class.identifier,'/',$operator_parent_node.node_id)|ezurl(no)}">{sensor_translate('Export to CSV', 'config')}</a></div>
<div class="pull-right">
    <a class="btn btn-danger" id="add" data-add-parent="{$operator_parent_node.node_id}" data-add-class="sensor_operator" href="{concat('add/new/sensor_operator/?parent=',$operator_parent_node.node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {sensor_translate('Add new', 'config')} {$operator_class.name}</a>
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
    var classes = resultsContainer.data('classes');
    var redirect = resultsContainer.data('redirect');
    var subtreeSelect = form.find('[name="user_group"]');
    var groupSelect = form.find('[name="operator_group"]');

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
      var subtree = resultsContainer.data('parent');
      if (subtreeSelect.length > 0 && subtreeSelect.val().length > 0) {
        subtree = subtreeSelect.val();
      }
      var query = classQuery + ' subtree [' + subtree + '] and raw[meta_main_node_id_si] !in [' + subtree + ']';
      if (groupSelect.length > 0) {
        var group = groupSelect.val();
        if (group.length > 0) {
          query += ' and struttura_di_competenza.id in [' + group + ']';
        }
      }
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
          },
          {
            data: "metadata.remoteId",
            name: 'remote_id',
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
              let output = '<p style="margin: 0;font-weight: bold"">'+$.opendataTools.helpers.i18n(row.metadata.name)+'</p>';
              if ($.opendataTools.helpers.i18n(row.data, 'ruolo')){
                output += '<small style="font-style: italic">'+$.opendataTools.helpers.i18n(row.data, 'ruolo')+' </small>'
              }
              if ($.opendataTools.helpers.i18n(row.data, 'struttura_di_competenza')){
                output += '<small>'+$.opendataTools.helpers.i18n(row.data, 'struttura_di_competenza').map(item => $.opendataTools.helpers.i18n(item.name)).join(' ')+'</small>'
              }
              return output;
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
              let output = '<div class="notification-dropdown-container dropdown" data-user="' + row.metadata.id + '">';
              output += '<div class="button-group">';
              output += '<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">';
              output += '<i class="fa fa-bell"></i> <span class="caret"></span>';
              output += '</button>';
              output += '<ul class="notification-dropdown-menu dropdown-menu">';
              output += '</ul>';
              output += '</div>';
              output += '</div>';
              return output;
            },
            targets: [6]
          },
          {
            render: function (data, type, row) {
              let output = '<span style="white-space:nowrap">';
              if (row.metadata.isEnabled) {
                output += '<a style="width: 35px;" class="btn btn-link btn-sm text-black" data-user_access_edit="' + row.metadata.id + '" data-type="' + row.metadata.classIdentifier + '">' +
                  '<i data-user=' + row.metadata.id + ' class="fa fa-user"></i>' +
                  '</a>';
              } else {
                output += '<a style="width: 35px;padding-left: 6px;" class="btn btn-link btn-sm text-black" data-user_access_edit="' + row.metadata.id + '" data-type="' + row.metadata.classIdentifier + '">' +
                  '<span class="fa-stack"><i class="fa fa-user fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span>' +
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
            targets: [7]
          },
        ],
      }
    }).on('draw.dt', function (e, settings) {
      let renderData = $(e.currentTarget);
      renderData.find('.notification-dropdown-container').on('show.bs.dropdown', function () {
        var user = $(this).data('user');
        var menu = $(this).find('.notification-dropdown-menu');
        buildNotificationMenu(user, menu);
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
      renderData.find('[data-user_access_edit]').on('click', function (e) {
        $('#item').opendataForm({
          user: $(this).data('user_access_edit')
        }, {
          connector: 'operator-settings',
          onBeforeCreate: function () {
            $('#modal').modal('show');
            setTimeout(function () {
              $('#modal .leaflet-container').trigger('click');
            }, 1000);
          },
          onSuccess: function () {
            $('#modal').modal('hide');
            datatable.datatable.ajax.reload(null, false);
          }
        });
        e.preventDefault();
      });
    }).data('opendataDataTable');

    if (subtreeSelect.length > 0) {
      subtreeSelect.val(subtreeSelect.find('option').first().val())
      subtreeSelect.select2().on('change', function () {
        datatable.settings.builder.query = buildQuery();
        datatable.loadDataTable();
      })
    }
    if (groupSelect.length > 0) {
      groupSelect.val(groupSelect.find('option').first().val())
      groupSelect.select2().on('change', function () {
        datatable.settings.builder.query = buildQuery();
        datatable.loadDataTable();
      })
    }
    datatable.loadDataTable();

  });
</script>
{/literal}
