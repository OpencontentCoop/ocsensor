{default html_class='full' placeholder=false()}

{if $placeholder}
    <label>{$placeholder}</label>
{/if}

{if is_set( $attribute_base )|not()}
  {def $attribute_base = 'ContentObjectAttribute'}
{/if}

{def $areas = sensor_areas()}

{if is_set( $areas['children'] )}
    {set $areas = $areas['children']}
{/if}

{def $current_areas = array()}
{if ne( count( $attribute.content.relation_list ), 0)}
    {foreach $attribute.content.relation_list as $relation}
        {set $current_areas = $current_areas|append($relation.contentobject_id)}
    {/foreach}
{/if}

<input type="hidden" name="single_select_{$attribute.id}" value="1" />
{if ne( count( $areas ), 0)}
    <select {if ezini( 'SensorConfig', 'MoveMarkerOnSelectArea', 'ocsensor.ini' )|eq('enabled')}id="poi"{/if}
            class="{$html_class} select-sensor-area{if ezini( 'SensorConfig', 'ReadOnlySelectArea', 'ocsensor.ini' )|eq('enabled')} select-sensor-area-disabled" readonly="readonly" tabindex="-1{/if}"
            name="{$attribute_base}_data_object_relation_list_{$attribute.id}[]">
        <option>{sensor_translate('Not specified')}</option>
        {foreach $areas as $item}
            <option value="{$item.id}"
                    data-id="{$item.id}"
                    {if is_set($item.geo.coords[0])}
                        data-lat="{$item.geo.coords[0]}" data-lng="{$item.geo.coords[1]}"
                    {/if}
                    style="padding-left:{$item.level|mul(10)}px;{if $item.level|eq(0)}font-weight: bold;{/if}"
                    {if ezini('GeoCoderSettings', 'MarkerMustBeInArea', 'ocsensor.ini')|eq('enabled')}
                        disabled="disabled"
                    {/if}
                    {if $current_areas|contains($item.id)} selected="selected"{/if}>
                {$item.name|wash()}
            </option>
            {foreach $item.children as $child}
                <option value="{$child.id}"
                        data-id="{$child.id}"
                        {if is_set($child.geo.coords[0])}
                            data-lat="{$child.geo.coords[0]}" data-lng="{$child.geo.coords[1]}"
                        {/if}
                        {if $child.bounding_box}data-geojson{/if}
                        style="padding-left:{$child.level|mul(10)}px;{if $child.level|eq(0)}font-weight: bold;{/if}"
                        {if and(ezini('GeoCoderSettings', 'MarkerMustBeInArea', 'ocsensor.ini')|eq('enabled'),$child.bounding_box|not())}
                            disabled="disabled"
                        {/if}
                        {if $current_areas|contains($child.id)} selected="selected"{/if}>
                    {$child.name|wash()}
                </option>
            {/foreach}
        {/foreach}
    </select>
{/if}

{/default}
