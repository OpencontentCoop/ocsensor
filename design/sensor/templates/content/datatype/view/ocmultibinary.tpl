{if $attribute.has_content}
    <ul class="list-unstyled">
        {foreach $attribute.content as $file}
            <li>
                {if $sensor_post.can_add_attachment}
                    <input type="checkbox"
                           name="Collaboration_SensorItemRemoveAttach[]"
                           value="{$file.filename}" />
                {/if}
                <a href={concat( 'ocmultibinary/download/', $attribute.contentobject_id, '/', $attribute.id,'/', $attribute.version , '/', $file.filename ,'/file/', $file.original_filename|urlencode )|ezurl}>
                    <span title="{$file.original_filename|wash( xhtml )}"><i class="fa fa-download"></i> Scarica il file</span>
                    <small>{$file.original_filename|wash( xhtml )} (File {$file.mime_type} {$file.filesize|si( byte )})</small>
                </a>
            </li>
        {/foreach}
    </ul>
    {if $sensor_post.can_add_attachment}
        <button class="btn btn-link text-danger" type="submit"
                name="CollaborationAction_RemoveAttach">
            <i class="fa fa-trash"></i> {'Rimuovi file selezionati'|i18n('sensor/messages')}
        </button>
    {/if}
{/if}