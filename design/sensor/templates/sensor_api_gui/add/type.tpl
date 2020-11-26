{if sensor_settings().HideTypeChoice}
    <input type="hidden" name="type" value="{sensor_types()[0].identifier}" />
{else}
<select name="type" class="form-control" tabindex="2">
    <option value="{sensor_types()[0].identifier}">Scegli il tipo di segnalazione</option>
        {foreach sensor_types() as $type}
        <option value="{$type.identifier}">
            {$type.name|wash()}
        </option>
    {/foreach}
</select>
{/if}