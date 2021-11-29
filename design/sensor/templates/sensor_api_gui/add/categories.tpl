{def $category_access = sensor_edit_category_access()}
{def $category_field = is_sensor_public_field('category')}
{if $category_access|ne('none')}
    {def $categories = sensor_categories()}
    {if $category_field|not()}
        {set $category_field = hash('is_required', true())}
    {/if}
    {if is_set( $categories['children'] )}
        {set $categories = $categories['children']}
    {/if}
    {if ne( count( $categories ), 0)}
        <label class="form-group has-float-label" style="margin-top: 30px;">
            <select class="form-control" name="category"{if $category_field.is_required} required{/if}>
                {if $category_field.is_required|not()}<option data-avoid_areas="[]"></option>{/if}
                {foreach $categories as $item}
                    {if or($category_access|eq('all'), $category_access|contains($item['node_id']))}
                        {if count($item.children)|eq(0)}
                            <option data-avoid_areas="{$item.disabled_relations|json_encode}" value="{$item.id}">{$item.name|wash()}</option>
                        {else}
                            <optgroup data-avoid_areas="{$item.disabled_relations|json_encode}" label="{$item.name|wash()}">
                        {/if}
                        {foreach $item.children as $child}
                            <option data-avoid_areas="{$child.disabled_relations|json_encode}" value="{$child.id}">{$child.name|wash()}</option>
                        {/foreach}
                        {if count($item.children)|gt(0)}
                            </optgroup>
                        {/if}
                    {/if}
                {/foreach}
            </select>
            <span>Scegli la categoria</span>
        </label>
    {/if}
{/if}
