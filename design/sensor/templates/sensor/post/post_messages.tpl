
<div id="post_comments">
    {if $sensor_post.comment_count}
    <div class="comment">
        <h4>{'Commenti'|i18n('sensor/messages')}</h4>
        {foreach $sensor_post.comment_items as $item}
            {include uri='design:sensor/post/post_message/public.tpl' is_read=cond( $sensor_post.current_participant, $sensor_post.current_participant.last_read|gt($item.modified), true()) item_link=$item message=$item.simple_message}
        {/foreach}
    </div>
    {/if}
    {if $sensor_post.can_comment}
        <div class="new_comment">
            <h4>{'Aggiungi un commento'|i18n('sensor/messages')}</h4>
            {if $sensor_post.can_send_private_message}
            <small class="text-muted"><i class="fa fa-warning"></i> {'Attenzione il commento sarà visibile a tutti. Per inviare un messaggio visibile solo al team usa il bottone \"Messaggi privati\".'|i18n('sensor/messages')}</small>
            {/if}
            <div class="row">
                <div class="col-sm-8 col-md-8"><br>
                    <textarea name="Collaboration_SensorItemComment" class="form-control" placeholder="{'Commenti'|i18n('sensor/messages')}" rows="7"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-8 col-md-8">
                    <input class="btn send btn-primary btn-lg btn-block"
                           type="submit" name="CollaborationAction_Comment"
                           value="{'Pubblica il commento'|i18n('sensor/messages')}"
                           {if $sensor_post.can_send_private_message}
                               data-confirmation="{'Sei sicuro di voler aggiungere un commento visibile a tutti?'|i18n( 'sensor/messages' )|wash(javascript)}"
                           {/if} />
                </div>
            </div>
        </div>
    {/if}
</div>

<div id="post_messages">
    {if $sensor_post.response_count}
    <div class="comment">
        <h4>{'Risposte ufficiali'|i18n('sensor/messages')}</h4>
        {foreach $sensor_post.response_items as $item}
            {include uri='design:sensor/post/post_message/response.tpl' is_read=cond( $sensor_post.current_participant, $sensor_post.current_participant.last_read|gt($item.modified), true()) item_link=$item message=$item.simple_message}
        {/foreach}
        {if $sensor_post.object|has_attribute('attachment')}
            <h4>{'Allegati'|i18n('sensor/messages')}</h4>
            <div class="well">
                {attribute_view_gui attribute=$sensor_post.object|attribute('attachment') sensor_post=$sensor_post}
            </div>
        {/if}
    </div>
    {/if}
    {if $sensor_post.can_respond}
    <div class="new_comment">
        <h4>{'Aggiungi risposta ufficiale'|i18n('sensor/messages')}</h4>
        <div class="row">
            <div class="col-sm-8 col-md-8"><br>
                <textarea name="Collaboration_SensorItemResponse" class="form-control" placeholder="{'Risposta ufficiale'|i18n('sensor/messages')}" rows="7"></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8 col-md-8">
                <input class="btn send btn-success btn-lg btn-block" type="submit" name="CollaborationAction_Respond" value="{'Pubblica la risposta ufficiale'|i18n('sensor/messages')}" />
            </div>
        </div>
        <h4>{'Aggiungi allegato'|i18n('sensor/messages')}</h4>
        <div class="row">
            <div class="col-sm-4 col-md-6">
                <input type="file" name="Collaboration_SensorItemAttach" value="" />
            </div>
            <div class="col-sm-4 col-md-2">
                <input class="btn send btn-success btn-md btn-block" type="submit" name="CollaborationAction_Attach" value="{'Allega'|i18n('sensor/messages')}" />
            </div>
        </div>
    </div>
    {/if}
</div>