{if ne( count( $areas ), 0)}
    <select {if ezini( 'SensorConfig', 'MoveMarkerOnSelectArea', 'ocsensor.ini' )|eq('enabled')}id="poi"{/if}
            class="form-control select-sensor-area{if ezini( 'SensorConfig', 'ReadOnlySelectArea', 'ocsensor.ini' )|eq('enabled')} select-sensor-area-disabled" readonly="readonly" tabindex="-1{/if}"
            name="areas[]">
        {if ezini( 'SensorConfig', 'ReadOnlySelectArea', 'ocsensor.ini' )|eq('enabled')}<option value=""></option>{/if}
        {foreach $areas as $item}
            <option value="{$item.id}"
                    data-id="{$item.id}"
                    {if is_set($item.geo.coords[0])}
                        data-lat="{$item.geo.coords[0]}" data-lng="{$item.geo.coords[1]}"
                    {/if}
                    style="padding-left:{$item.level|mul(10)}px;{if $item.level|eq(0)}font-weight: bold;{/if}"
                    {if ezini('GeoCoderSettings', 'MarkerMustBeInArea', 'ocsensor.ini')|eq('enabled')}disabled="disabled"{/if}>{$item.name|wash()}</option>
            {foreach $item.children as $child}
                <option value="{$child.id}"
                        data-id="{$child.id}"
                        {if is_set($child.geo.coords[0])}
                            data-lat="{$child.geo.coords[0]}" data-lng="{$child.geo.coords[1]}"
                        {/if}
                        {if $child.bounding_box}data-geojson{/if}
                        style="padding-left:{$child.level|mul(10)}px;{if $child.level|eq(0)}font-weight: bold;{/if}"
                        {if and(ezini('GeoCoderSettings', 'MarkerMustBeInArea', 'ocsensor.ini')|eq('enabled'),$child.bounding_box|not())}disabled="disabled"{/if}>{$child.name|wash()}</option>
            {/foreach}
        {/foreach}
    </select>
{/if}