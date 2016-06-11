<form id="chart-filters">
    <strong>{'Aree tematiche'|i18n('sensor/chart')}</strong>
    <ul class="list-unstyled">
        {foreach sensor_categories().children as $category}
        <div class="checkbox">
                <label><input type="checkbox" name="category_id_list"
                              value="{$category.id}"/> <small>{$category.name|wash()}</small>
                </label>
            </div>
        {/foreach}
    </ul>
{*
    <strong>{'Punti sulla mappa'|i18n('sensor/config')}</strong>
        {foreach sensor_areas().children as $area}
        <div class="checkbox">
                <label><input type="checkbox" name="area_id_list"
                              value="{$area.id}"/> <small>{$area.name|wash()}</small>
                </label>
            </div>
        {/foreach}
*}
</form>