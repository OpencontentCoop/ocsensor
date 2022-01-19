{if sensor_settings().HideTypeChoice}
    <input type="hidden" name="type" value="{sensor_types()[0].identifier}" />
{else}
<div class="post-subject">
    <label class="form-group has-float-label">
    <select name="type" class="form-control" tabindex="1">
        {foreach sensor_types() as $type}
            <option value="{$type.identifier}">
                {$type.name|wash()}
            </option>
        {/foreach}
    </select>
    <span>{sensor_translate('Choose the type of issue')}</span>
    </label>
</div>
{/if}
