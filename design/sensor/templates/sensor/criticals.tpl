{ezscript_require(array('bootstrap.js', 'sql-parser.min.js', 'query-builder.standalone.min.js', 'query-builder.it.js'))}
{ezcss_require(array('query-builder.default.min.css'))}
<div class="container">
    <section class="hgroup">
        <h1>Segnalazioni critiche</h1>
    </section>
    <div class="row">
        <div class="col-md-12">
            <div id="builder"></div>
            <div class="text-center" style="margin: 20px 0">
                <button id="btn-get" class="btn btn-success btn-lg parse-json" data-target="basic">Visualizza</button>
            </div>
            <div id="data"></div>
        </div>
    </div>
</div>
{literal}
<script id="tpl-data-spinner" type="text/x-jsrender">
<div class="col-xs-12 spinner text-center">
    <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
</div>
</script>
<script id="tpl-data-results" type="text/x-jsrender">
<div class="row">
    {{if total == 0}}
        <div class="col-xs-12 text-center">
            <i class="fa fa-times"></i> {{:~sensorTranslate('No content')}}
        </div>
    {{else}}
    <div class="col-xs-12">
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
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Oggetto</th>
                    <th><i class="fa fa-forward"></i></th>
                    <th><i class="fa fa-repeat"></i></th>
                    <th class="text-center"><i class="fa fa-clock-o"></i></th>
                    <th><i class="fa fa-comment"></i></th>
                    <th>Ultimo gruppo incaricato</th>
                    <th>Riferimento</th>
                </tr>
            </thead>
            <tbody>
            {{for hits}}
                <tr>
                    <td>{{:post_id}}</td>
                    <td>{{:name}}</td>
                    <td>{{:reassign_count}}</td>
                    <td>{{:reopen_count}}</td>
                    <td>{{:duration}}</td>
                    <td>{{if has_comment_after_close}}<i class="fa fa-check"></i>{{/if}}</td>
                    <td>{{:latest_group}}</td>
                    <td>{{:group_reference}}</td>
                </tr>
            {{/for}}
            </tbody>
        </table>
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
        var spinner = $($.templates("#tpl-data-spinner").render({}));
        var resultsContainer = $('#data');
        var currentPage = 1;
        var builder = $('#builder');
        var filters = {/literal}{$filters}{literal};
        var rules = {/literal}{$rules}{literal};

        builder.queryBuilder({
            plugins: ['bt-tooltip-errors'],
            filters: filters,
            rules: rules
        });

        var loadData = function (){
            resultsContainer.html(spinner);
            $.get('/sensor/criticals/api?p='+currentPage, function (response){
                var renderData = $(template.render(response));
                resultsContainer.html(renderData);
                resultsContainer.find('[data-page]').on('click', function (e) {
                    currentPage = $(this).data('page');
                    if (currentPage >= 0) loadData();
                    $('html, body').stop().animate({
                        scrollTop: resultsContainer.offset().top
                    }, 1000);
                    e.preventDefault();
                });
            })
        }

        $('#btn-get').on('click', function() {
            var result = builder.queryBuilder('getRules');
            var sql = builder.queryBuilder('getSQL', 'named', false);
            if (!$.isEmptyObject(result)) {
                var tokenNode = document.getElementById('ezxform_token_js');
                $.post('/sensor/criticals', {
                    StoreFilters: true,
                    Rules: result,
                    Sql: sql,
                    ezxform_token: tokenNode ? tokenNode.getAttribute('title') : false
                }, function (response){
                    loadData();
                })
            }
        });
    })
</script>
{/literal}