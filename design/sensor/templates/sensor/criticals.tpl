{ezcss_require(array(
    'query-builder.default.min.css',
    'select2.min.css',
    'leaflet/MarkerCluster.css',
    'leaflet/MarkerCluster.Default.css',
    'leaflet.0.7.2.css',
    'plugins/blueimp/blueimp-gallery.css',
    'jquery.fileupload.css'
))}
{ezscript_require(array(
    'ezjsc::jquery', 'ezjsc::jqueryio', 'ezjsc::jqueryUI',
    'bootstrap/tooltip.js', 'sql-parser.min.js', 'query-builder.standalone.min.js', 'query-builder.it.js',
    'js.cookie.js',
    'moment-with-locales.min.js',
    'plugins/blueimp/jquery.blueimp-gallery.min.js',
    'select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js'),
    'jquery.fileupload.js',
    'leaflet.0.7.2.js',
    'leaflet.markercluster.js',
    'Leaflet.MakiMarkers.js',
    'daterangepicker.js',
    'jquery.opendataTools.js',
    'jsrender.js', 'jsrender.helpers.js',
    'jquery.maskedinput.js',
    'jquery.sensorpost.js'
))}

<div class="container">
    <section class="hgroup">
        <div id="SelectPreset" class="pull-right"></div>
        <h1>Segnalazioni critiche</h1>
    </section>
    <div class="row">
        <div class="col-md-12">
            <div id="builder"{if $has_sql} data-has_sql="1"{/if}></div>
            <div class="clearfix" style="margin: 20px 0">
                <div id="StorePreset" class="input-group input-group-lg pull-left" style="max-width: 50%{if $has_sql};display: none{/if}">
                    <span class="input-group-btn">
                        <button id="btn-get" class="btn btn-success btn-lg parse-json pull-left" data-target="basic">Salva impostazioni come</button>
                    </span>
                    <input type="text" id="PresetName" class="form-control" maxlength="50" placeholder="nome impostazione" value="{$current_preset|wash()}">
                </div>
                <button id="update-data" class="btn btn-info btn-lg pull-right"{if $has_sql|not()} style="display: none"{/if}><i class="fa fa-refresh"></i> Aggiorna i dati</button>
            </div>
            <div id="filters"></div>
            <div id="data"></div>
        </div>
    </div>
</div>
<div id="modal-preview" class="modal fade">
    <div class="modal-dialog modal-lg" style="width: 75%">
        <div class="modal-content">
            <div class="modal-body">
                <div class="clearfix">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div id="preview" style="display: none">
                    <div id="post-preview" class="post-gui" style="position: relative;min-height: 400px;"></div>
                    {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post.tpl'}
                    {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-title.tpl'}
                    {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-detail.tpl'}
                    {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-messages.tpl'}
                    {include uri='design:sensor_api_gui/posts/v2/parts/tpl-post-sidebar.tpl'}
                    {include uri='design:sensor_api_gui/posts/tpl-alerts.tpl'}
                    {include uri='design:sensor_api_gui/posts/tpl-spinner.tpl'}
                    {include uri='design:sensor_api_gui/posts/tpl-post-gallery.tpl'}
                </div>
            </div>
        </div>
    </div>
</div>
{literal}
<script id="tpl-select-preset" type="text/x-jsrender">
{{if current}}
<div class="dropdown">
    <button class="btn btn-default btn-lg {{if presets.length > 1}}dropdown-toggle{{/if}}" type="button" id="dropdownMenu1" {{if presets.length > 1}}data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"{{/if}}>
        {{:current}}
        {{if presets.length > 1}}<span class="caret"></span>{{/if}}
    </button>
    {{if presets.length > 1}}
    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
    {{for presets ~current=current}}
        {{if name != ~current}}
            <li><a href="#" style="white-space: normal;padding-right: 5px;"><span data-select="{{:name}}">{{:name}}</span> <i style="margin-top: 3px;" data-remove="{{:name}}" class="fa fa-trash pull-right"></i></a></li>
        {{/if}}
    {{/for}}
    </ul>
    {{/if}}
</div>
{{/if}}
</script>
<script id="tpl-data-spinner" type="text/x-jsrender">
<div class="col-xs-12 spinner text-center">
    <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
</div>
</script>
<script id="tpl-data-filters" type="text/x-jsrender">
    <div class="row">
        <div class="col-xs-6">
            <div>
                <select data-name="group-filter" name="GroupSelect[]" class="select form-control" data-placeholder="Filtra per gruppo" multiple>
                    <option></option>
                    {{for groups}}
                        {{if name}}
                        <option value="{{>name}}" {{if selected}} selected="selected"{{/if}}>{{>name}}</option>
                        {{/if}}
                    {{/for}}
                </select>
            </div>
        </div>
        <div class="col-xs-6">
            <div>
                <select data-name="reference-filter" name="ReferenceSelect[]" class="select form-control" data-placeholder="Filtra per riferimento" multiple>
                    <option></option>
                    {{for references}}
                        {{if name}}
                        <option value="{{>name}}" {{if selected}} selected="selected"{{/if}}>{{>name}}</option>
                        {{/if}}
                    {{/for}}
                </select>
            </div>
        </div>
    </div>
</script>
<script id="tpl-data-results" type="text/x-jsrender">
<div>
    {{if total == 0}}
        <div class="row">
            <div class="col-xs-12 text-center">
                <i class="fa fa-times"></i> {{:~sensorTranslate('No content')}}
            </div>
        </div>
    {{else}}
    <div class="row">
        <div class="col-xs-12">
            <div class="pagination-container text-center" aria-label="{{:~sensorTranslate('Navigation')}}">
                <ul class="pagination">
                    <li class="page-item"><a href="/sensor/criticals/csv-export" class="text" style="cursor: pointer;"><i class="fa fa-download"></i> {{:total_unfiltered}} {{:~sensorTranslate('issues')}}</a></li>
                </ul>
                <ul class="pagination">
                    <li class="page-item {{if !previous}}disabled{{/if}}">
                        <a class="page-link prevPage" {{if previous}}data-page="{{:previous}}"{{/if}} href="#">
                            <i class="fa fa-arrow-left"></i>
                            <span class="sr-only">{{:~sensorTranslate('Previous page')}}</span>
                        </a>
                    </li>
                    <li class="page-item disabled"><a href="#" class="page-link page">Pagina {{:page}} di {{:pages}} - {{:total}} elementi</a></li>
                    <li class="page-item {{if !next}}disabled{{/if}}">
                        <a class="page-link nextPage" {{if next}}data-page="{{>next}}"{{/if}} href="#">
                            <span class="sr-only">{{:~sensorTranslate('Next page')}}</span>
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Oggetto</th>
                        <th><i class="fa fa-forward" title="Riassegnazioni"></i></th>
                        <th><i class="fa fa-repeat" title="Riaperture"></i></th>
                        <th class="text-center"><i class="fa fa-clock-o" title="Giornate di lavorazione"></i></th>
                        <th><i class="fa fa-comment" title="Commenti successivi alla chiusura"></i></th>
                        <th><i class="fa fa-comments-o" title="Numero di commenti"></i></th>
                        <th><i class="fa fa-comments" title="Numero di note private"></i></th>
                        <th>Ultimo gruppo incaricato</th>
                        <th>Riferimento</th>
                    </tr>
                </thead>
                <tbody>
                {{for hits}}
                    <tr>
                        <td><a href="#" class="btn btn-xs btn-primary" data-preview="{{:post_id}}">{{:post_id}}</a></td>
                        <td>{{:name}}</td>
                        <td>{{:reassign_count}}</td>
                        <td>{{:reopen_count}}</td>
                        <td>{{:duration}}</td>
                        <td>{{if has_comment_after_close}}<i class="fa fa-check"></i>{{/if}}</td>
                        <td>{{:comment_count}}</td>
                        <td>{{:private_message_count}}</td>
                        <td>{{:latest_group}}</td>
                        <td>{{:group_reference}}</td>
                    </tr>
                {{/for}}
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="pagination-container text-center" aria-label="{{:~sensorTranslate('Navigation')}}">
                <ul class="pagination">
                    <li class="page-item {{if !previous}}disabled{{/if}}">
                        <a class="page-link prevPage" {{if previous}}data-page="{{:previous}}"{{/if}} href="#">
                            <i class="fa fa-arrow-left"></i>
                            <span class="sr-only">{{:~sensorTranslate('Previous page')}}</span>
                        </a>
                    </li>
                    <li class="page-item disabled"><a href="#" class="page-link page">Pagina {{:page}} di {{:pages}} - {{:total}} elementi</a></li>
                    <li class="page-item {{if !next}}disabled{{/if}}">
                        <a class="page-link nextPage" {{if next}}data-page="{{>next}}"{{/if}} href="#">
                            <span class="sr-only">{{:~sensorTranslate('Next page')}}</span>
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    {{/if}}
</div>
</script>
<script>
    $(document).ready(function (){
        var template = $.templates('#tpl-data-results');
        var filtersTpl = $.templates('#tpl-data-filters');
        var selectPreset = $.templates('#tpl-select-preset');
        var spinner = $($.templates("#tpl-data-spinner").render({}));
        var filtersContainer = $('#filters');
        var resultsContainer = $('#data');
        var selectPresetContainer = $('#SelectPreset');
        var currentPage = 1;
        var currentGroups = [];
        var currentReferences = [];
        var builder = $('#builder');
        var filters = {/literal}{$filters}{literal};
        var rules = {/literal}{$rules}{literal};
        var updateDataButton = $('#update-data');
        var storePresetButton = $('#StorePreset');
        var postGui = $('#preview');
        var postWindow = $('#post-preview');
        var onOpenPost = function (){
            $('#modal-preview').modal();
            postGui.show();
        };
        $('#modal-preview').on('hide.bs.modal', function (e) {
            postWindow.html('');
            window.history.replaceState({'post_id': null}, document.title, '/sensor/criticals');
        })

        var sensorPostViewer = postWindow.sensorPost({
            'apiEndPoint': '/api/sensor_gui',
            'sensorPostDefinition': '{/literal}{sensor_post_class()|json_encode()|wash(javascript)}{literal}',
            'currentUserId': {/literal}{fetch(user,current_user).contentobject_id|int()}{literal},
            'areas': '{/literal}{sensor_areas()|json_encode()|wash(javascript)}{literal}',
            'categories': '{/literal}{sensor_categories()|json_encode()|wash(javascript)}{literal}',
            'operators': '{/literal}{sensor_operators()|json_encode()|wash(javascript)}{literal}',
            'groups': '{/literal}{sensor_groups()|json_encode()|wash(javascript)}{literal}',
            'settings': '{/literal}{sensor_settings()|json_encode()|wash(javascript)}{literal}',
            'spinnerTpl': '#tpl-spinner',
            'postTpl': '#tpl-post',
            'alertsEndPoint': '{/literal}{'social_user/alert'|ezurl(no)}{literal}'
        }).data('plugin_sensorPost');

        builder.queryBuilder({
            plugins: ['bt-tooltip-errors'],
            filters: filters,
            rules: rules
        });

        var isFiltersAlreadyLoaded = false;
        var loadData = function (){
            resultsContainer.html(spinner);
            var requestData = {'p': currentPage};
            if (currentGroups.length > 0){
                requestData.latest_group = currentGroups;
            }
            if (currentReferences.length > 0){
                requestData.references = currentReferences;
            }
            console.log(requestData);
            $.get('/sensor/criticals/api', requestData, function (response){

                if (!isFiltersAlreadyLoaded) {
                    var renderFilters = $(filtersTpl.render(response));
                    filtersContainer.html(renderFilters);
                    filtersContainer.find('[data-name="group-filter"]').select2().on('change', function (e) {
                        currentPage = 1;
                        currentGroups = filtersContainer.find('[data-name="group-filter"]').val() || [];
                        loadData();
                        $('html, body').stop().animate({
                            scrollTop: builder.offset().top
                        }, 1000);
                    });
                    filtersContainer.find('[data-name="reference-filter"]').select2().on('change', function (e) {
                        currentPage = 1;
                        currentReferences = filtersContainer.find('[data-name="reference-filter"]').val() || [];
                        loadData();
                        $('html, body').stop().animate({
                            scrollTop: builder.offset().top
                        }, 1000);
                    });
                    isFiltersAlreadyLoaded = true;
                }

                var renderData = $(template.render(response));
                resultsContainer.html(renderData);
                resultsContainer.find('[data-page]').on('click', function (e) {
                    currentPage = $(this).data('page');
                    if (currentPage >= 0) loadData();
                    $('html, body').stop().animate({
                        scrollTop: builder.offset().top
                    }, 1000);
                    e.preventDefault();
                });
                resultsContainer.find('[data-preview]').on('click', function (e) {
                    var postId = $(this).data('preview');
                    sensorPostViewer.openPost(postId, onOpenPost);
                    e.preventDefault();
                });
            })
        }

        var loadPresetMenu = function (){
            $.post('/sensor/criticals/rules', function (response){
                var renderData = $(selectPreset.render(response.sql));
                renderData.find('[data-select]').on('click', function(e){
                    e.preventDefault();
                    var tokenNode = document.getElementById('ezxform_token_js');
                    var presetName = $(this).data('select');
                    if (presetName.length > 0) {
                        resultsContainer.html(spinner);
                        updateDataButton.hide();
                        $.post('/sensor/criticals/preset', {
                            PresetName: presetName,
                            ezxform_token: tokenNode ? tokenNode.getAttribute('title') : false
                        }, function (response) {
                            $('#PresetName').val(presetName);
                            builder.queryBuilder('setRules', response.rules.presets[presetName].rules);
                            storePresetButton.hide();
                            currentPage = 1;
                            currentGroups = [];
                            currentReferences = [];
                            loadData();
                            loadPresetMenu();
                            updateDataButton.show();
                        })
                    }
                })
                renderData.find('[data-remove]').on('click', function(e){
                    e.preventDefault();
                    var tokenNode = document.getElementById('ezxform_token_js');
                    var presetName = $(this).data('remove');
                    if (presetName.length > 0) {
                        $.post('/sensor/criticals/remove-preset', {
                            PresetName: presetName,
                            ezxform_token: tokenNode ? tokenNode.getAttribute('title') : false
                        }, function (response) {
                            loadPresetMenu();
                        })
                    }
                })
                selectPresetContainer.html(renderData);
            })
        }

        $('#btn-get').on('click', function(e) {
            var presetName = $('#PresetName').val();
            var result = builder.queryBuilder('getRules');
            var sql = builder.queryBuilder('getSQL', 'named', false);
            if (!$.isEmptyObject(result) && presetName.length > 0) {
                resultsContainer.html(spinner);
                var tokenNode = document.getElementById('ezxform_token_js');
                $.post('/sensor/criticals', {
                    StoreFilters: true,
                    PresetName: presetName,
                    Rules: result,
                    Sql: sql,
                    ezxform_token: tokenNode ? tokenNode.getAttribute('title') : false
                }, function (response){
                    currentPage = 1;
                    currentGroups = [];
                    currentReferences = [];
                    loadData();
                    loadPresetMenu();
                    updateDataButton.show();
                    storePresetButton.hide();
                })
            }
            e.preventDefault();
        });

        updateDataButton.on('click', function(e) {
            resultsContainer.html(spinner);
            var tokenNode = document.getElementById('ezxform_token_js');
            $.post('/sensor/criticals', {
                UpdateData: true,
                ezxform_token: tokenNode ? tokenNode.getAttribute('title') : false
            }, function (response){
                currentPage = 1;
                currentGroups = [];
                currentReferences = [];
                loadData();
            })
            e.preventDefault();
        });

        builder.on('rulesChanged.queryBuilder', function(e, level) {
            storePresetButton.show();
        });

        loadPresetMenu();
        if ($('[data-has_sql]').length > 0){
            loadData();
        }

    })
</script>
{/literal}