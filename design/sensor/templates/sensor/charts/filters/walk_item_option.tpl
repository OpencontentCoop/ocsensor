<option value="{$item.id}" style="padding-left:{5|sum( $recursion|mul(10) )}px;{if $recursion|eq(0)}font-weight: bold;{/if}"
        {if ne( count( $current ), 0)}
            {foreach $current as $selectedItem}
                {if eq( $selectedItem.id, $item.id )} selected="selected"{break}{/if}
            {/foreach}
        {/if}
>{$item.name|wash}</option>
{if $item.children|count()|gt(0)}
    {set $recursion = $recursion|inc()}
    {foreach $item.children as $subitem}
        {include name=itemchildren uri='design:sensor/charts/filters/walk_item_option.tpl' item=$subitem recursion=$recursion current=$current}
    {/foreach}
{/if}
