{ezscript_require(array(
  'jquery.opendataTools.js',
  'jsrender.js'
))}
<form class="row form hide" data-faq-search>
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
<div class="row" style="margin-top: 20px">
    {if sensor_settings().ShowFaqCategories}
    <div class="col-md-3 hide" data-categoryfilter>
        <div class="list-group">
        {foreach sensor_categories()['children'] as $item}
            <a class="list-group-item hide"
               data-categoryidlist="{$item.id}{if count($item.children)|gt(0)},{foreach $item.children as $child}{$child.id}{delimiter},{/delimiter}{/foreach}{/if}"
               href="#">{$item.name|wash()}</a>
        {/foreach}
        </div>
    </div>
    {/if}
    <div class="col-md-12"
         data-root="{sensor_faqcontainer().node_id}"
         data-classes="sensor_faq"></div>
</div>

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
                {{if ~i18n(data, 'category')}}<div class="text-right"><small><i class="fa fa-tag"></i> {{for ~i18n(data, 'category')}}{{:~i18n(name)}}{{/for}}</small></div>{{/if}}
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
    var resultsContainer = $('[data-root]');
    var form = $('[data-faq-search]');
    var subtree = resultsContainer.data('root');
    var classes = resultsContainer.data('classes');
    var currentPage = 0;
    var queryPerPage = [];
    var limitPagination = 50;
    var template = $.templates('#tpl-data-results');
    var spinner = $($.templates("#tpl-data-spinner").render({}));
    var isLoadedCategoryMenu = false;
    var buildQuery = function () {
        var classQuery = '';
        if (classes.length) {
            classQuery = 'classes [' + classes + '] facets [category.id]';
        }
        var query = classQuery + ' subtree [' + subtree + '] and raw[meta_main_node_id_si] !in [' + subtree + ']';
        var searchText = form.find('[data-search="q"]').val().replace(/"/g, '').replace(/'/g, "").replace(/\(/g, "").replace(/\)/g, "").replace(/\[/g, "").replace(/\]/g, "");
        if (searchText.length > 0) {
            query += " and q = '\"" + searchText + "\"'";
        }
        var categoryList = [];
        $("[data-categoryidlist].active").each(function (){
            categoryList = $.merge(categoryList, ($(this).data('categoryidlist')+'').split(','));
        })
        if (categoryList.length > 0){
            query += " and category.id in [" + categoryList.join(',') + "]";
        }
        query += ' sort [priority=>desc,published=>asc]';
        return query;
    };
    var loadCategoryMenu = function (facets){
        if (!isLoadedCategoryMenu){
            var countGroup = 0;
            $("[data-categoryidlist]").each(function (){
                var self = $(this);
                var categoryList = (self.data('categoryidlist')+'').split(',');
                $.each(facets, function (id, count){
                    if ($.inArray(id+'', categoryList) > -1 && self.hasClass('hide')){
                        self.removeClass('hide').on('click', function (e){
                            $(this).toggleClass('active');
                            loadContents();
                            e.preventDefault();
                        });
                        countGroup++
                    }
                })
            })
            if (countGroup > 1) {
                $('[data-categoryfilter]').removeClass('hide')
                resultsContainer.removeClass('col-md-12').addClass('col-md-9')
            }
        }
    }
    var loadContents = function () {
        var baseQuery = buildQuery();
        var paginatedQuery = baseQuery + ' and limit ' + limitPagination + ' offset ' + currentPage * limitPagination;
        resultsContainer.html(spinner);
        $.opendataTools.find(paginatedQuery, function (response) {
            loadCategoryMenu(response.facets[0].data);
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
#accordion .panel-default > .panel-heading{
    position: relative;
    padding-right: 30px;
}
#accordion .panel-heading a{
    display: block;
}
#accordion .panel-heading a:after {
    font-family: 'Glyphicons Halflings';
    content: "\e113";
    float: right;
    color: grey;
    position: absolute;
    right: 10px;
    top: 30%;
}
#accordion .panel-heading a.collapsed:after {
    content: "\e114";
}
/*.panel-collapse>.list-group .list-group-item:first-child {border-top-right-radius: 0;border-top-left-radius: 0;}*/
/*.panel-collapse>.list-group .list-group-item {border-width: 1px 0;}*/
/*.panel-collapse>.list-group {margin-bottom: 0;}*/
/*.panel-collapse .list-group-item {border-radius:0;}*/

/*.panel-collapse .list-group .list-group {margin: 0;margin-top: 10px;}*/
/*.panel-collapse .list-group-item li.list-group-item {margin: 0 -15px;border-top: 1px solid #ddd !important;border-bottom: 0;padding-left: 30px;}*/
/*.panel-collapse .list-group-item li.list-group-item:last-child {padding-bottom: 0;}*/

/*.panel-collapse div.list-group div.list-group{margin: 0;}*/
/*.panel-collapse div.list-group .list-group a.list-group-item {border-top: 1px solid #ddd !important;border-bottom: 0;padding-left: 30px;}*/
/*.panel-collapse .list-group-item li.list-group-item {border-top: 1px solid #DDD !important;}*/
</style>
{/literal}
