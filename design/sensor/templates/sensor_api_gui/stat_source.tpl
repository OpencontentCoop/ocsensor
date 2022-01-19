{ezscript_require(array('jquery.dataTables.js', 'jquery.opendataDataTable.js', 'dataTables.bootstrap.js', 'dataTables.responsive.min.js', 'moment-with-locales.min.js', 'moment-timezone-with-data.js'))}
{ezcss_require(array('dataTables.bootstrap.css','responsive.dataTables.min.css'))}

<section class="hgroup">
    <h1>{sensor_translate('Statistics source')}</h1>
</section>

<div class="datatable-container">
</div>


<script>
{literal}
var settings = {
    endpoint: '{/literal}{concat('/customdatatable/',$repository)|ezurl(no)}{literal}',
    columns: JSON.parse('{/literal}{$columns|json_encode()}{literal}')
};
$(document).ready(function () {
    var renderAll = function(data, type, row){
        if ($.isArray(data)) {
            return data.join(', ');
        }
        if ($.isPlainObject(data)) {
            let str = '';
            for (let p in data) {
                if (data.hasOwnProperty(p)) {
                    str += p + ': ' + data[p] + '<br />';
                }
            }
            return str;
        }
        return data;
    };
    var repositoryDatatable = $('.datatable-container').opendataDataTable({
        'table': {
            'template': '<table class="table table-striped"></table>'
        },
        'builder': {
            'query': ''
        },
        'datatable': {
            'responsive': true,
            'order': [[ 0, 'asc' ]],
            'ajax': {
                url: settings.endpoint,
                type: settings.columns.length > 15 ? 'POST' : 'GET'
            },
            'columns': settings.columns,
            'columnDefs': [{
                'className': 'dtr-control',
                'render': function (data, type, row) {
                    return renderAll(data, type, row);
                },
                'targets': 0
            }, {
                'render': function (data, type, row) {
                    return renderAll(data, type, row);
                },
                'targets': '_all'
            }]
        }
    }).data('opendataDataTable');
    repositoryDatatable.loadDataTable();
});
{/literal}
</script>
