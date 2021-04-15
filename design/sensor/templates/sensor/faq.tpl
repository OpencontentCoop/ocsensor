{ezscript_require(array(
  'jquery.opendataTools.js',
  'jsrender.js'
))}
<form class="row form hide">
    <div class="col-xs-12">
        <div class="input-group">
            <input type="text" class="form-control" data-search="q" placeholder="{'Cerca nelle faq'|i18n('sensor/config')}">
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
</form>
<div style="margin: 20px 0"
     data-parent="{sensor_faqcontainer().node_id}"
     data-classes="sensor_faq"></div>

{literal}
<script id="tpl-data-spinner" type="text/x-jsrender">
<div class="col-xs-12 spinner text-center">
    <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
</div>
</script>
<script id="tpl-data-results" type="text/x-jsrender">
{{if totalCount > 0}}
    {{for searchHits}}
        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
          <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading-{{:metadata.id}}">
              <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-{{:metadata.id}}" aria-expanded="false" aria-controls="collapse-{{:metadata.id}}">
                  {{if ~i18n(data, 'question')}}{{:~i18n(data, 'question')}}{{/if}}
                </a>
              </h4>
            </div>
            <div id="collapse-{{:metadata.id}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{{:metadata.id}}">
              <div class="panel-body">
                {{if ~i18n(data, 'answer')}}{{:~i18n(data, 'answer')}}{{/if}}
              </div>
            </div>
          </div>
        </div>
    {{/for}}
{{/if}}
{{if pageCount > 1}}
<div class="row">
    <div class="col-xs-12">
        <div class="pagination-container text-center" aria-label="Esempio di navigazione della pagina">
            <ul class="pagination">
                <li class="page-item {{if !prevPageQuery}}disabled{{/if}}">
                    <a class="page-link prevPage" {{if prevPageQuery}}data-page="{{>prevPage}}"{{/if}} href="#">
                        <i class="fa fa-arrow-left"></i>
                        <span class="sr-only">Pagina precedente</span>
                    </a>
                </li>
                {{for pages ~current=currentPage}}
                    <li class="page-item{{if ~current == query}} active{{/if}}"><a href="#" class="page-link page" data-page_number="{{:page}}" data-page="{{:query}}"{{if ~current == query}} data-current aria-current="page"{{/if}}>{{:page}}</a></li>
                {{/for}}

                <li class="page-item {{if !nextPageQuery}}disabled{{/if}}">
                    <a class="page-link nextPage" {{if nextPageQuery}}data-page="{{>nextPage}}"{{/if}} href="#">
                        <span class="sr-only">Pagina successiva</span>
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
$.views.helpers($.opendataTools.helpers);
$(document).ready(function () {
    var resultsContainer = $('[data-parent]');
    var form = resultsContainer.prev();
    var subtree = resultsContainer.data('parent');
    var classes = resultsContainer.data('classes');
    var currentPage = 0;
    var queryPerPage = [];
    var limitPagination = 100;
    var template = $.templates('#tpl-data-results');
    var spinner = $($.templates("#tpl-data-spinner").render({}));
    var buildQuery = function () {
        var classQuery = '';
        if (classes.length) {
            classQuery = 'classes [' + classes + ']';
        }
        var query = classQuery + ' subtree [' + subtree + '] and raw[meta_main_node_id_si] !in [' + subtree + ']';
        var searchText = form.find('[data-search="q"]').val().replace(/"/g, '').replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");
        if (searchText.length > 0) {
            query += " and q = '\"" + searchText + "\"'";
        }
        query += ' sort [priority=>desc,published=>asc]';
        return query;
    };
    var loadContents = function () {
        var baseQuery = buildQuery();
        var paginatedQuery = baseQuery + ' and limit ' + limitPagination + ' offset ' + currentPage * limitPagination;
        resultsContainer.html(spinner);
        $.opendataTools.find(paginatedQuery, function (response) {
            if (response.totalCount > 0){
                form.removeClass('hide');
            }
            queryPerPage[currentPage] = paginatedQuery;
            response.currentPage = currentPage;
            response.prevPage = currentPage - 1;
            response.nextPage = currentPage + 1;
            var pagination = response.totalCount > 0 ? Math.ceil(response.totalCount / limitPagination) : 0;
            var pages = [];
            var i;
            for (i = 0; i < pagination; i++) {
                queryPerPage[i] = baseQuery + ' and limit ' + limitPagination + ' offset ' + (limitPagination * i);
                pages.push({'query': i, 'page': (i + 1)});
            }
            response.pages = pages;
            response.pageCount = pagination;
            response.prevPageQuery = jQuery.type(queryPerPage[response.prevPage]) === "undefined" ? null : queryPerPage[response.prevPage];
            response.showType = classes.split(',').length > 1;
            var renderData = $(template.render(response));
            resultsContainer.html(renderData);
            resultsContainer.find('.page, .nextPage, .prevPage').on('click', function (e) {
                currentPage = $(this).data('page');
                if (currentPage >= 0) loadContents();
                $('html, body').stop().animate({
                    scrollTop: form.offset().top
                }, 1000);
                e.preventDefault();
            });
            var more = $('<li class="page-item"><span class="page-link">...</span></li');
            var displayPages = resultsContainer.find('.page[data-page_number]');
            var currentPageNumber = resultsContainer.find('.page[data-current]').data('page_number');
            var length = 7;
            if (displayPages.length > (length + 2)) {
                if (currentPageNumber <= (length - 1)) {
                    resultsContainer.find('.page[data-page_number="' + length + '"]').parent().after(more.clone());
                    for (i = length; i < pagination; i++) {
                        resultsContainer.find('.page[data-page_number="' + i + '"]').parent().hide();
                    }
                } else if (currentPageNumber >= length) {
                    resultsContainer.find('.page[data-page_number="1"]').parent().after(more.clone());
                    var itemToRemove = (currentPageNumber + 1 - length);
                    for (i = 2; i < pagination; i++) {
                        if (itemToRemove > 0) {
                            resultsContainer.find('.page[data-page_number="' + i + '"]').parent().hide();
                            itemToRemove--;
                        }
                    }
                    if (currentPageNumber < (pagination - 1)) {
                        resultsContainer.find('.page[data-current]').parent().after(more.clone());
                    }
                    for (i = (currentPageNumber + 1); i < pagination; i++) {
                        resultsContainer.find('.page[data-page_number="' + i + '"]').parent().hide();
                    }
                }
            }
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
        form.find(".select").val(null).trigger("change");
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
</script>
<style>
.panel-default > .panel-heading{
    position: relative;
    padding-right: 30px;
}
.panel-heading a{
    display: block;
}
.panel-heading a:after {
    font-family: 'Glyphicons Halflings';
    content: "\e113";
    float: right;
    color: grey;
    position: absolute;
    right: 10px;
    top: 30%;
}
.panel-heading a.collapsed:after {
    content: "\e114";
}
</style>
{/literal}
