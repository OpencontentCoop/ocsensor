{def $category_field = is_sensor_public_field('category')}
{if $category_field}
    {def $categories = sensor_categories()}
    {if is_set( $categories['children'] )}
        {set $categories = $categories['children']}
    {/if}
    {if ne( count( $categories ), 0)}
        <label class="form-group has-float-label" style="margin-top: 30px;">
            <select class="form-control" name="category">
                {if $category_field.is_required|not()}<option></option>{/if}
                {foreach $categories as $item}
                    {if count($item.children)|eq(0)}
                        <option value="{$item.id}">{$item.name|wash()}</option>
                    {else}
                        <optgroup label="{$item.name|wash()}">
                    {/if}
                    {foreach $item.children as $child}
                        <option value="{$child.id}">{$child.name|wash()}</option>
                    {/foreach}
                    {if count($item.children)|gt(0)}
                        </optgroup>
                    {/if}
                {/foreach}
            </select>
            <span>Scegli la categoria</span>
        </label>
    {/if}
{/if}