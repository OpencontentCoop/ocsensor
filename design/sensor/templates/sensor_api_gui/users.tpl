<section class="hgroup">
    <h1>{'Utenti'|i18n('sensor/menu')}</h1>
</section>

<div class="row">
    <div class="col-md-12">
        <form class="row form">
            <div class="col-xs-12 col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" data-search="q"
                           placeholder="{sensor_translate('Find user')}">
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
            {if fetch( 'user', 'has_access_to', hash( 'module', 'user', 'function', 'activation' ) )}
                <div class="col-xs-12 col-md-6 text-right">
                    <a class="btn btn-sm btn-primary"
                       href="{'user/unactivated'|ezurl(no)}">{'Unactivated users'|i18n('design/admin/parts/user/menu')}</a>
                </div>
            {/if}
        </form>

        <div style="margin: 20px 0"
             data-parent="{$user_parent_node.node_id}"
             data-classes="{$user_classes|implode(',')}"
             data-limit="20"
             data-redirect="/sensor/user"></div>
    </div>
</div>
{ezscript_require(array(
    'jquery.opendataTools.js',
    'jsrender.js', 'jsrender.helpers.js',
    'moment-with-locales.min.js'
))}
{literal}
<script id="tpl-data-spinner" type="text/x-jsrender">
<div class="col-xs-12 spinner text-center">
    <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
</div>
</script>
<script id="tpl-data-results" type="text/x-jsrender">
<div class="row">
    {{if count == 0}}
        <div class="col-xs-12 text-center">
            <i class="fa fa-times"></i> {{:~sensorTranslate('No content')}}
        </div>
    {{else}}
    <div class="col-xs-12">
        <table class="table table-striped">
            <thead>
                <th>{{:~sensorTranslate('Name')}}</th>
                <th>{{:~sensorTranslate('Fiscal code')}}</th>
                <th>{{:~sensorTranslate('Email')}}</th>
                <th>{{:~sensorTranslate('Phone')}}</th>
                <th>{{:~sensorTranslate('Last access')}}</th>
                <th width="1"></th>
            </thead>
            <tbody>
            {{for items}}
                <tr>
                    <td>{{:name}}</td>
                    <td>{{:fiscal_code}}</td>
                    <td>{{:email}}</td>
                    <td>{{:phone}}</td>
                    <td>{{if last_access_at}}{{:~formatDate(last_access_at, 'DD/MM/YYYY HH:mm')}}{{/if}}</td>
                    <td width="1">
                        <a href="/sensor/user/{{:id}}" class="btn btn-xs btn-default">
                            {/literal}{'Vedi segnalazioni'|i18n('sensor')}{literal}
                        </a>
                    </td>
                </tr>
            {{/for}}
            </tbody>
        </table>
    </div>
    {{/if}}
</div>
{{if prev || next}}
<div class="row">
    <div class="col-xs-12">
        <div class="pagination-container text-center" aria-label="Navigazione della pagina">
            <ul class="pagination">
                <li class="page-item {{if !prev}}disabled{{/if}}">
                    <a class="page-link prevPage" {{if prev}}data-page="{{>prev}}"{{/if}} href="#">
                        <i class="fa fa-arrow-left"></i>
                        <span class="sr-only">{{:~sensorTranslate('Previous page')}}</span>
                    </a>
                </li>
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

</script>
    <script>
        $(document).ready(function () {
            $('[data-parent]').each(function () {
                var resultsContainer = $(this);
                var form = resultsContainer.prev();
                var limitPagination = resultsContainer.data('limit');
                var subtree = resultsContainer.data('parent');
                var classes = resultsContainer.data('classes');
                var template = $.templates('#tpl-data-results');
                var spinner = $($.templates("#tpl-data-spinner").render({}));
                var currentPage = 0;
                var queryPerPage = [];

                var loadContents = function (uri) {
                    if (!uri) {
                        var queryParams = {
                            'limit': limitPagination,
                            'cursor': '*',
                            'q': form.find('[data-search="q"]').val()
                        };
                        uri = '/api/sensor_gui/users?' + jQuery.param(queryParams);
                    }
                    resultsContainer.html(spinner);
                    $.get(uri, function (response) {
                        queryPerPage[currentPage] = response.self
                        if (currentPage > 0){
                            response.prev = queryPerPage[(currentPage-1)];
                        }
                        var renderData = $(template.render(response));
                        renderData.find('[data-page]').on('click', function (e){
                            if ($(this).hasClass('nextPage')){
                                currentPage++;
                            }else{
                                currentPage--;
                            }
                            loadContents($(this).data('page'));
                            e.preventDefault;
                        })
                        resultsContainer.html(renderData);
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
            });
        });
    </script>
{/literal}
