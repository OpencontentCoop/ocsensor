{default attribute_base='ContentObjectAttribute' html_class='full' placeholder=false() }
{let attribute_content=$attribute.content}

{if $attribute_content.original.is_valid}

<table class="table" cellspacing="0">
<tr>
    <td style="vertical-align: middle;width: 100px">
        <div style="background-image: url({$attribute.content[ezini( 'ImageSettings', 'DefaultEditAlias', 'content.ini' )].full_path|ezroot(no)}); background-repeat:no-repeat; background-size: contain; background-position: center; height: 100px; width: 100px"></div>
    </td>
    <td style="vertical-align: middle">
        <p>{$attribute.content.original.original_filename|wash( xhtml )} <br /><small>{$attribute.content.original.mime_type|wash( xhtml )} ({$attribute.content.original.filesize|si( byte )})</small></p>
        {if $attribute_content.original.is_valid}
        <p><button class="button btn" type="submit" name="CustomActionButton[{$attribute.id}_delete_image]" title="{'Remove image'|i18n( 'design/standard/content/datatype' )}"><span class="glyphicon glyphicon-trash"></span> Rimuovi</button></p>
        {/if}
    </td>
</tr>
</table>
{/if}

<input type="hidden" name="MAX_FILE_SIZE" value="{$attribute.contentclass_attribute.data_int1|mul( 1024, 1024 )}" />
<input id="ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}_file" class="ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier}" name="{$attribute_base}_data_imagename_{$attribute.id}" type="file" />
<input placeholder="{'Alternative image text'|i18n( 'design/standard/content/datatype' )}" id="ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}_alttext" class="{$html_class} ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier}" name="{$attribute_base}_data_imagealttext_{$attribute.id}" type="hidden" value="{$attribute_content.alternative_text|wash(xhtml)}" />

{/let}
{/default}
