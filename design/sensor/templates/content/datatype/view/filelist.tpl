<table class="table" cellpadding="0" cellspacing="0">
    <tbody>
    {if $attribute.has_content}
        {foreach $attribute.content as $key => $file}
            <tr>
                <td>
                    {if $file.mime_type_category|eq('image')}
                        <img src="{concat( 'ocmultibinary/download/', $attribute.contentobject_id, '/', $attribute.id,'/', $attribute.version , '/', $file.filename ,'/file/', $file.original_filename|urlencode )|ezurl(no)}" width="80px" />
                    {/if}
                </td>
                <td>
                    {$file.original_filename|wash( xhtml )}&nbsp;({$file.filesize|si( byte )})
                </td>
                <td>
                    <input type="hidden" value="{$key}" name="{$attribute_base}_sort_{$attribute.id}[{$file.original_filename|wash( xhtml )}]" class="sort" data-filename="{$file.original_filename|wash( xhtml )}" />
                    <button class="ocmultibutton btn btn-danger btn-xs" type="submit"
                            name="CustomActionButton[{$attribute.id}_delete_multibinary][{$file.filename}]"
                            title="{'Remove this file'|i18n( 'extension/ocmultibinary' )}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        {/foreach}
    {/if}
    </tbody>

</table>