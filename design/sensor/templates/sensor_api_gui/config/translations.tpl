{ezcss_require(array('select2.min.css'))}
{ezscript_require(array('select2.full.min.js', concat('select2-i18n/', fetch( 'content', 'locale' ).country_code|downcase, '.js')))}

<section>

    <h4>Add custom translations or override existing translation</h4>

    <div class="form-group">
        <div class="input-group">
            <select class="form-control" id="select-override">
                <option value=""></option>
                {foreach $static_translations as $key => $values}
                    {def $current = false}
                    <option value="{$key|wash()}"
                            data-key="{$key|wash()}" {*
                            *}{foreach $values as $locale => $value}data-{$locale}="{$value|wash()}" {if $locale|eq($current_locale_code)}{set $current = $value}{/if}{/foreach}>
                        {$current|wash()}
                    </option>
                    {undef $current}
                {/foreach}
            </select>
        </div>
    </div>

    <form id="addCustomForm" method="post" action="{'/sensor/config/translations'|ezurl(no)}">
        <table class="list">
            <tr>
                <th><label for="Key">Key</label></th>
                <td>
                    <input id="Key" class="form-control" name="Key" value="" type="text" data-key placeholder="Key"/>
                </td>
            </tr>
            {foreach $available_languages as $language}
                <tr>
                    <th><label for="{$language}">{$language}</label></th>
                    <td>
                        <input id="{$language}" class="form-control" name="Languages[{$language}]" data-{$language}
                               value="" type="text" placeholder="{$language}"/>
                    </td>
                </tr>
            {/foreach}
            <tr>
                <td colspan="2">
                    <input type="submit" class="btn btn-success pull-right" name="AddCustom"
                           value="{sensor_translate('Store')}"/>
                    <input type="reset" class="btn btn-danger pull-left" name="AddCustom"
                           value="{sensor_translate('Cancel')}"/>
                </td>
            </tr>
        </table>
    </form>

    {if $custom_translations|count()}
        <h4>Custom or override translations</h4>
        <form id="removeCustomForm" method="post" action="{'/sensor/config/translations'|ezurl(no)}">
            <table class="list">
            {foreach $custom_translations as $key => $values}
                <tr>
                    <td width="1"><input type="checkbox" name="RemoveKeys[{$key}]"></td>
                    <th>{$key|wash()}</th>
                    {foreach $values as $locale => $value}
                        <td><strong>{$locale}:</strong> {$value|wash()}</td>
                    {/foreach}
                </tr>
            {/foreach}
            <tr>
                <td>
                    <button type="submit" class="btn btn-danger" name="RemoveCustom"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
            </table>
        </form>
    {/if}

</section>


{literal}
    <script>
        $(document).ready(function () {
            var selectOverride = $('#select-override');
            var form = $('#addCustomForm')
            form[0].reset();
            selectOverride.select2({
                placeholder: "Select string to override"
            }).on('select2:select', function (e) {
                var element = $(e.params.data.element);
                if (element.length > 0) {
                    var data = element.data();
                    $.each(data, function (index, value) {
                        index = index.replace(/[A-Z]/g, m => "-" + m.toLowerCase());
                        $(form).find('[data-' + index + ']').val(value);
                    })
                    console.log(data);
                } else {
                    form[0].reset();
                }
            });
        });
    </script>
{/literal}
