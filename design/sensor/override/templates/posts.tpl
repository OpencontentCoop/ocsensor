{ezpagedata_set('left_menu',false())}
{def $page_limit = 10}
{def $data = facet_navigation(
    hash(
        'subtree_array', array( sensor_postcontainer().node_id ),
        'class_id', array( 'sensor_post' ),
        'offset', $view_parameters.offset,
        'sort_by', hash( 'published', 'desc' ),
        'facet', array(
            hash( 'field', solr_subfield('area', 'name', 'string'), 'name', sensor_translate('Area'), 'limit', 500, 'sort', 'alpha' ),
            hash( 'field', solr_field('type', 'string'), 'name', sensor_translate('Type'), 'limit', 500, 'sort', 'alpha' ),
            hash( 'field', solr_subfield('category', 'name', sensor_translate('Category'), 'limit', 500, 'sort', 'alpha' ),
            hash( 'field', solr_meta_field('object_states'), 'name', sensor_translate('Status'), 'limit', 500, 'sort', 'alpha' )
        ),
        'limit', $page_limit
    ),
    $view_parameters,
    $node.url_alias,
)}

<section class="service_teasers">
    {foreach $data.contents as $item}
        {include name=posts_item uri='design:sensor/post/list_item.tpl' node=$item}
    {/foreach}
    {include name=navigator
             uri='design:navigator/google.tpl'
             page_uri=$node.url_alias
             item_count=$data.count
             view_parameters=$view_parameters
             item_limit=$page_limit}
</section>

{if fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'config' ) )}
    <div id="posts_search">
        <div class="container">
            <form class="form-horizontal" role="search" action={concat('facet/proxy/', $node.node_id)|ezurl()}>
                <div class="col-md-2">
                    <input id="searchfacet" data-content="{sensor_translate('Hit enter to search')}" type="text" class="form-control" placeholder="{sensor_translate('Search')}" name="query" value="{$data.query|wash()}">
                </div>
                {if $data.navigation|count}
                    {foreach $data.navigation as $name => $items}
                        <div class="col-md-2">
                            <select class="facet-select form-control chosen" data-placeholder="{$name|wash()}" name="{$name|wash()}">
                                <option value="">{$name|wash()}</option>
                                {foreach $items as $item}
                                    {if $name|eq('Stato')}
                                        {def $state = $item.name|objectstate_by_id()}
                                        {if array( 'sensor', 'privacy', 'moderation' )|contains( $state.group.identifier )}
                                            <option {if $item.active}selected="selected"{/if} value="{$item.query|wash()}">
                                                {$state.group.current_translation.name|wash()}/{$state.current_translation.name|wash()}
                                                {if $item.count|gt(0)}({$item.count}){/if}
                                            </option>
                                        {/if}
                                        {undef $state}
                                    {else}
                                        <option {if $item.active}selected="selected"{/if} value="{$item.query|wash()}">
                                            {$item.name|wash()}
                                            {if $item.count|gt(0)}({$item.count}){/if}
                                        </option>
                                    {/if}
                                {/foreach}
                            </select>
                        </div>
                    {/foreach}
                {/if}
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info"><span class="fa fa-search"></span></button>
                    <a href="{$node.url_alias|ezurl(no)}" title="Reset" class="btn btn-danger"><span class="fa fa-close"></span></a>
                </div>
            </form>
        </div>
    </div>
{/if}
