{def $handlers=$handler.collaboration_handlers $selection=$handler.collaboration_selections}


<div class="panel panel-default">

    <input type="hidden" name="CollaborationHandlerSelection" value="1"/>
    {foreach $handlers as $current_handler}
        {if $current_handler.info.type-identifier|eq(sensor_collaboration_identifier())}

            {if is_array($current_handler.info.notification-types)}

                <table class="table table-striped">

                    {foreach $current_handler.info.notification-types as $type}
                        {if $type.group|eq( 'standard' )}
                            <tr>
                                <td width="1">
                                    <input type="checkbox"
                                           name="CollaborationHandlerSelection_{$handler.id_string}[]"
                                           value="{$current_handler.info.type-identifier}_{$type.identifier}" {if $selection|contains(concat($current_handler.info.type-identifier,'_',$type.identifier))} checked="checked"{/if} />
                                </td>
                                <td>
                                    {$type.name|wash}
                                    {if is_set($type.description)}
                                        <br/>
                                        <small>{$type.description|wash()}</small>{/if}
                                </td>
                            </tr>
                        {/if}
                    {/foreach}
                </table>
            {/if}

        {/if}
    {/foreach}

    <div class="panel-footer">
        <input class="button btn btn-xs btn-success" type="submit" name="Store"
               value="{'Salva le impostazioni'|i18n('sensor/settings')}"/>
    </div>


</div>

