{def $handlers=$handler.collaboration_handlers $selection=$handler.collaboration_selections}


<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            {"Notifiche aggiornamento segnalazioni"|i18n('sensor/settings')}
        </h4>
    </div>

    <input type="hidden" name="CollaborationHandlerSelection" value="1"/>
    {foreach $handlers as $current_handler}
        {if $current_handler.info.type-identifier|eq(sensor_collaboration_identifier())}

            {if is_array($current_handler.info.notification-types)}

                {def $hasLanguages = false()}
                {foreach $current_handler.info.notification-types as $type}
                    {if $type.group|eq( 'language' )}
                        {set $hasLanguages = true()}
                        {break}
                    {/if}
                {/foreach}

                {def $hasTransports = false()
                     $transportNames = array()
                     $countTransports = 0}
                {foreach $current_handler.info.notification-types as $type}
                    {if $type.group|eq( 'transport' )}
                        {set $hasTransports = true()}
                        {set $countTransports = $countTransports|inc()}
                        {if $transportNames|contains($type.name)|not()}
                            {set $transportNames = $transportNames|append($type.name)}
                        {/if}
                    {/if}
                {/foreach}
                <table class="table table-striped">
                    <tr>
                        <th colspan="2">{"Azione"|i18n('sensor/settings')}</th>
                        {if $hasLanguages}
                            {foreach fetch( 'content', 'prioritized_languages' ) as $language}
                                <th width="1" class="text-center">
                                    <img src="{$language.locale|flag_icon()}"
                                         alt="{$language.name|wash}"
                                         title="{$language.name|wash}"/>
                                </th>
                            {/foreach}
                        {/if}
                        {if $hasTransports}
                            {foreach $transportNames as $name}
                                <th width="1" class="text-center">
                                    {if count($countTransports)|gt(1)}
                                        <span>{$name|wash()}</span>
                                    {/if}
                                </th>
                            {/foreach}
                        {/if}
                    </tr>

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

                                {if $hasLanguages}
                                    {foreach $current_handler.info.notification-types as $language_type}
                                        {if and( $language_type.group|eq( 'language' ), $language_type.parent|eq( $type.identifier ))}
                                            <td class="text-center">
                                                <input type="radio"
                                                       name="CollaborationHandlerSelection_{$handler.id_string}[{$language_type.identifier}]"
                                                       value="{$current_handler.info.type-identifier}_{$language_type.identifier}" {if or( $selection|contains(concat($current_handler.info.type-identifier,'_',$language_type.identifier)), $language_type.default_language_code|eq($language_type.language_code) )} checked="checked"{/if} />
                                            </td>
                                        {/if}
                                    {/foreach}
                                {/if}

                                {if $hasTransports}
                                    {def $countTransport = 0}
                                    {foreach $current_handler.info.notification-types as $transport_type}
                                        {if and( $transport_type.group|eq( 'transport' ), $transport_type.parent|eq( $type.identifier ))}
                                            {set $countTransport = $countTransport|inc()}
                                        {/if}
                                    {/foreach}

                                    {foreach $current_handler.info.notification-types as $transport_type}
                                        {if and( $transport_type.group|eq( 'transport' ), $transport_type.parent|eq( $type.identifier ))}
                                            <td class="text-center">
                                              <div>
                                                <input type={if $countTransport|gt(1)}"checkbox"{else}"hidden"{/if}
                                                       {if $transport_type.enabled|not()}disabled="disabled"{/if}
                                                       name="CollaborationHandlerSelection_{$handler.id_string}[{$transport_type.identifier}]"
                                                       value="{$current_handler.info.type-identifier}_{$transport_type.identifier}" {if and( $countTransport|gt(1), $transport_type.enabled, or( $selection|contains(concat($current_handler.info.type-identifier,'_',$transport_type.identifier)), $transport_type.default_transport|eq($transport_type.transport) ) )} checked="checked"{/if} />
                                                </div>
                                            </td>
                                        {/if}
                                    {/foreach}                                    
                                    {undef $countTransport}
                                {/if}

                            </tr>
                        {/if}
                    {/foreach}
                </table>
            {else}
                {*<table class="table">
                    <tr>
                        <td width="1">
                            <input type="checkbox"
                                   name="CollaborationHandlerSelection_{$handler.id_string}[]"
                                   value="{$current_handler.info.type-identifier}"
                                   {if $selection|contains($current_handler.info.type-identifier)}checked="checked"{/if} />
                        </td>
                        <td>
                            {$current_handler.info.type-name|wash}
                        </td>
                    </tr>
                </table>*}
            {/if}

        {/if}
    {/foreach}

    <div class="panel-footer">
        <input class="button btn btn-xs btn-success" type="submit" name="Store"
               value="{'Salva le impostazioni'|i18n('sensor/settings')}"/>
    </div>


</div>

