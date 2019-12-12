{default attribute_base='ContentObjectAttribute' html_class='full'}

<select name="{$attribute_base}_data_integer_{$attribute.id}" class="form-control">
    <option {if $attribute.data_int|eq(0)}selected="selected"{/if} value="0">{'Mai (non inviare notifiche)'|i18n('sensor/config')}</option>
    <option {if $attribute.data_int|eq(30)}selected="selected"{/if} value="30">{'1 mese'|i18n('sensor/config')}</option>
    <option {if $attribute.data_int|eq(90)}selected="selected"{/if} value="90">{'3 mesi'|i18n('sensor/config')}</option>
    <option {if $attribute.data_int|eq(180)}selected="selected"{/if} value="180">{'6 mesi'|i18n('sensor/config')}</option>
    <option {if $attribute.data_int|eq(360)}selected="selected"{/if} value="360">{'12 mesi'|i18n('sensor/config')}</option>
    <option {if $attribute.data_int|eq(540)}selected="selected"{/if} value="540">{'18 mesi'|i18n('sensor/config')}</option>
</select>

{/default}