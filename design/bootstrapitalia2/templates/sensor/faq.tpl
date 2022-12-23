{ezpagedata_set( 'has_container', true() )}
{ezscript_require(array(
  'jquery.opendataTools.js',
  'jsrender.js', 'jsrender.helpers.js'
))}

<form class="row form hide" data-faq-search>
    <div class="input-group mb-3">
        <input type="text"
               autocomplete="off"
               data-search="q"
               name="SearchText"
               class="form-control rounded-0"
               placeholder="{sensor_translate('Search in faq')}">
        <div class="input-group-append">
            <button class="btn btn-outline-secondary  rounded-0 m-0" type="submit">
                {display_icon('it-search', 'svg', 'icon icon-sm')} <span class="sr-only">{'Search'|i18n('design/base')}</span>
            </button>
            <button type="reset" class="btn btn-danger hide rounded-0 m-0">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
</form>
<div class="row mb-5">
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
    <div class="accordion">
    {{for searchHits}}
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-{{:metadata.id}}">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapse-{{:metadata.id}}" aria-expanded="false" aria-controls="collapse-{{:metadata.id}}">
                    {{if ~i18n(data, 'question')}}{{:~i18n(data, 'question')}}{{/if}}
                </button>
            </h2>
            <div id="collapse-{{:metadata.id}}" class="accordion-collapse collapse" role="region" aria-labelledby="heading-{{:metadata.id}}">
                <div class="accordion-body">
                    {{if ~i18n(data, 'answer')}}{{:~i18n(data, 'answer')}}{{/if}}
                    {{if ~i18n(data, 'category')}}<div class="text-right"><small><i class="fa fa-tag"></i> {{for ~i18n(data, 'category')}}{{:~i18n(name)}}{{/for}}</small></div>{{/if}}
                </div>
            </div>
        </div>
    {{/for}}
    </div>
{{/if}}
{{if pageCount > 1}}
<div class="row mt-lg-4">
    <div class="col">
        <nav class="pagination-wrapper justify-content-center" aria-label="{/literal}{'Navigation'|i18n('design/ocbootstrap/menu')}{literal}">
            <ul class="pagination">
                {{if prevPageQuery}}
                <li class="page-item">
                    <a class="page-link prevPage" data-page="{{>prevPage}}" href="#">
                        <svg class="icon icon-primary">
                            <use xlink:href="/extension/openpa_bootstrapitalia/design/standard/images/svg/sprite.svg#it-chevron-left"></use>
                        </svg>
                        <span class="sr-only">{{:~sensorTranslate('Previous page')}}</span>
                    </a>
                </li>
                {{/if}}
                {{for pages ~current=currentPage}}
                    <li class="page-item"><a href="#" class="page-link page" data-page_number="{{:page}}" data-page="{{:query}}"{{if ~current == query}} data-current aria-current="page"{{/if}}>{{:page}}</a></li>
                {{/for}}
                {{if nextPageQuery }}
                <li class="page-item">
                    <a class="page-link nextPage" data-page="{{>nextPage}}" href="#">
                        <span class="sr-only">{{:~sensorTranslate('Next page')}}</span>
                        <svg class="icon icon-primary">
                            <use xlink:href="/extension/openpa_bootstrapitalia/design/standard/images/svg/sprite.svg#it-chevron-right"></use>
                        </svg>
                    </a>
                </li>
                {{/if}}
            </ul>
        </nav>
    </div>
</div>
{{/if}}
</script>
<script>
$.opendataTools.settings('endpoint', {
    search: "{/literal}{'/opendata/api/content/search/'|ezurl(no)}{literal}"
});
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
{/literal}
