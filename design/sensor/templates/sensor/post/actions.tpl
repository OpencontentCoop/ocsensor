{if or(
    $sensor_post.can_do_something,
    or(
        $sensor_post.message_count,
        and( $sensor_post.can_comment, $sensor_post.can_send_private_message )
    )
)}
    <aside class="widget well well-sm" id="current-post-action">

        {if $sensor_post.can_add_area}
            <strong>{'Quartiere/Zona'|i18n('sensor/post')}</strong>
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-8">
                        <select data-placeholder="{'Seleziona Quartiere/Zona'|i18n('sensor/post')}" name="Collaboration_SensorItemArea[]" class="chosen form-control">
                            <option></option>
                            {foreach $sensor_post.areas.tree as $area}
                                {include name=area uri='design:tools/walk_item_option.tpl' item=$area recursion=0 attribute=$sensor_post.object.data_map.area}
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-xs-4">
                        <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_AddArea" value="{'Associa'|i18n('sensor/post')}" />
                    </div>
                </div>
            </div>
        {/if}

        {if $sensor_post.can_add_category}
            <strong>{'Area tematica'|i18n('sensor/post')}</strong>
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-8">
                        <select data-placeholder="{'Seleziona area tematica'|i18n('sensor/post')}" name="Collaboration_SensorItemCategory[]" class="chosen form-control">
                            <option></option>
                            {foreach $sensor_post.categories.tree as $category}
                                {include name=cattree uri='design:tools/walk_item_option.tpl' item=$category recursion=0 attribute=$sensor_post.object.data_map.category}
                            {/foreach}
                        </select>
                        {if ezini( 'SensorConfig', 'CategoryAutomaticAssign', 'ocsensor.ini' )|eq( 'enabled' )}
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="Collaboration_SensorItemAssignToCategoryApprover"> {"Assegna al responsabile dell'area selezionata"|i18n('sensor/post')}
                                </label>
                            </div>
                        {/if}
                    </div>
                    <div class="col-xs-4">
                        <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_AddCategory" value="{'Associa'|i18n('sensor/post')}" />
                    </div>
                </div>
            </div>
        {/if}

        {if $sensor_post.can_set_expiry}
            <strong>{'Scadenza'|i18n('sensor/post')} <small>{'in giorni'|i18n('sensor/post')}</small></strong>
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-8">
                        <input type="text" class="form-control" name="Collaboration_SensorItemExpiry" value="{$sensor_post.expiration_days|wash()}" />
                    </div>
                    <div class="col-xs-4">
                        <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_SetExpiry" value="{'Imposta'|i18n('sensor/post')}" />
                    </div>
                </div>
            </div>
        {/if}

        {if or(
            $sensor_post.can_assign,
            $sensor_post.can_add_observer,
            $sensor_post.can_fix,
            $sensor_post.can_close,
            and( $sensor_post.current_privacy_state.identifier|ne('private'), $sensor_post.can_change_privacy ),
            and( $sensor_post.current_moderation_state.identifier|eq('waiting'), $sensor_post.can_moderate )
        )}
            <strong>{'Azioni'|i18n('sensor/post')}</strong>
        {/if}

        {if $sensor_post.can_assign}
            {def $post_operators = $sensor_post.post_operators}
            {if count( $post_operators )|gt(0)}
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-8">
                        <select data-placeholder="{'Seleziona operatore'|i18n('sensor/post')}" name="Collaboration_SensorItemAssignTo[]" class="chosen form-control">
                            <option></option>
                            {foreach $post_operators as $user}
                                <option value="{$user.contentobject_id}">{include uri='design:content/view/sensor_person.tpl' sensor_person=$user.object}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-xs-4">
                        <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_Assign" value="{if $sensor_post.has_owner|not()}{'Assegna'|i18n('sensor/post')}{else}{'Riassegna'|i18n('sensor/post')}{/if}" />
                    </div>
                </div>
            </div>
            {/if}
            {undef $post_operators}
        {/if}

        {if $sensor_post.can_add_observer}
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-8">
                        <select data-placeholder="{'Seleziona operatore'|i18n('sensor/post')}" name="Collaboration_SensorItemAddObserver" class="chosen form-control">
                            <option></option>
                            {foreach $sensor_post.operators as $user}
                                <option value="{$user.contentobject_id}">{include uri='design:content/view/sensor_person.tpl' sensor_person=$user.object}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-xs-4">
                        <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_AddObserver" value="{'Aggiungi cc'|i18n('sensor/post')}" />
                    </div>
                </div>
            </div>
        {/if}

        {if $sensor_post.can_fix}
            <div class="form-group">
                <input class="btn btn-success btn-lg btn-block" type="submit" name="CollaborationAction_Fix" value="{'Intervento terminato'|i18n('sensor/post')}" />
            </div>
        {/if}

        {if $sensor_post.can_force_fix}
            <div class="form-group">
                <input class="btn btn-danger btn-lg btn-block" type="submit" name="CollaborationAction_ForceFix" value="{'Forza chiusura'|i18n('sensor/post')}" />
            </div>
        {/if}

        {if $sensor_post.can_close}
            <div class="form-group">
                <input class="btn btn-success btn-lg btn-block" type="submit" name="CollaborationAction_Close" value="{'Chiudi'|i18n('sensor/post')}" />
            </div>
        {/if}

        {if $sensor_post.can_change_privacy}
            {if $sensor_post.current_privacy_state.identifier|eq('public')}
                <div class="form-group">
                    <input class="btn btn-danger btn-lg btn-block" type="submit" name="CollaborationAction_MakePrivate" value="{'Rendi la segnalazione privata'|i18n('sensor/post')}" />
                </div>
            {elseif $sensor_post.current_privacy_state.identifier|eq('private')}
                <div class="form-group">
                    <input class="btn btn-danger btn-lg btn-block" type="submit" name="CollaborationAction_MakePublic" value="{'Rendi la segnalazione pubblica'|i18n('sensor/post')}" />
                </div>
            {/if}
        {/if}

        {if and( $sensor_post.current_moderation_state.identifier|eq('waiting'), $sensor_post.can_moderate )}
            <div class="form-group">
                {*
                <select name="Collaboration_SensorItemModerationIdentifier" class="form-control">
                  <option value="approved">{'Approva'|i18n('sensor/post')}</option>
                  <option value="refused">{'Rifiuta'|i18n('sensor/post')}</option>
                </select>
                *}
                <input class="btn btn-default btn-lg btn-block" type="submit" name="CollaborationAction_Moderate" value="{'Elimina moderazione'|i18n('sensor/post')}" />
            </div>
        {/if}

        {include uri='design:sensor/post/private_conversation.tpl'}

    </aside>
{/if}