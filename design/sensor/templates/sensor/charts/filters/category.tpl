<div class="panel panel-default">
    <div class="panel-heading">Area tematica</div>
    <div class="panel-body" style="max-height: 200px;overflow-y: scroll">
        {def $categories = sensor_categories().children}
        {foreach $categories as $category}
            <ul class="list-unstyled" id="category_filter">
                {if $category.children|count()|gt(0)}
                    <li class="nested">

                        <div class="checkbox" style="margin: 5px 0">
                            <label>
                                <input type="checkbox" class="select_children" value="{$category.id}"/>
                                <strong>{$category.name|wash}</strong>
                            </label>
                        </div>

                        <ul class="list-unstyled children" style="padding-left: 15px;">
                            <li>
                                <div class="checkbox" style="margin: 5px 0">
                                    <label>
                                        <input type="checkbox" name="category_id_list" value="{$category.id}" class="child"/>
                                        {$category.name|wash} (senza descrittori)
                                    </label>
                                </div>
                            </li>
                            {foreach $category.children as $item}
                                <li>
                                    <div class="checkbox" style="margin: 5px 0">
                                        <label>
                                            <input type="checkbox" name="category_id_list" value="{$item.id}" class="child"/>
                                            {$item.name|wash}
                                        </label>
                                    </div>
                                </li>
                            {/foreach}
                        </ul>
                    </li>
                {else}
                    <li>
                        <div class="checkbox" style="margin: 5px 0">
                            <label>
                                <input type="checkbox" name="category_id_list" value="{$category.id}"/>
                                {$category.name|wash}
                            </label>
                        </div>
                    </li>
                {/if}
            </ul>
        {/foreach}


        {ezscript_require( array( 'ezjsc::jquery' ) )}
        {literal}
        <script type="text/javascript">
            $(function () {
                var containerId = '#category_filter';
                $(document).on( 'change', containerId + ' input', function (e) {
                    var target = $(e.currentTarget);
                    if ( target.hasClass('select_children') )
                        target.parents('li').find('ul li input').prop( "checked", target.prop("checked") );
                    else if ( target.hasClass('child') ){
                        var countSelected = target.parents('ul.children').find('input:checked').length;
                        var countSelectable = target.parents('ul.children').find('input').length;
                        if( countSelected == 0) {
                            target.parents('li.nested').find('input.select_children').prop("checked", false);
                        }else if ( countSelected == countSelectable)
                            target.parents('li.nested').find('input.select_children').prop( "checked", true );
                    }
                    $('#chart-filters').trigger('sensor:charts:filterchange');
                });
            });
        </script>
        {/literal}
    </div>
</div>