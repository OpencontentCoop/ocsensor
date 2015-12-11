{if or( $post.privateMessages.count|gt(0), $user.permissions.can_send_private_message )}
<p>
    <a class="btn btn-info btn-lg btn-block" data-toggle="collapse" href="#collapseConversation" aria-expanded="false" aria-controls="collapseConversation">
        {'Messaggi privati'|i18n('sensor/messages')}
        {if $post.privateMessages.count|gt(0)}<span class="badge">{$post.privateMessages.count}</span>{/if}
    </a>
</p>
<div class="collapse" id="collapseConversation">
    {if $post.privateMessages.count|gt(0)}
    <div class="comment">
        {foreach $post.privateMessages.messages as $message}
            <p>
                {include uri='design:sensor/post/post_message/private.tpl' message=$message}
            </p>
        {/foreach}
    </div>
    {/if}
    {if $user.permissions.can_send_private_message}
        <div class="new_comment">
            <p>
                <textarea name="Collaboration_SensorItemPrivateMessage" class="form-control" placeholder="{'Aggiungi messaggio'|i18n('sensor/messages')}" rows="4"></textarea>
                <input class="btn send btn-info btn-sm btn-block" type="submit" name="CollaborationAction_PrivateMessage" value="{'Invia messaggio'|i18n('sensor/messages')}" />
            </p>
            <strong>{'Chi può leggere questo messaggio?'|i18n('sensor/messages')} </strong>
            {foreach $post.participants.participants as $participant}
                {if $participant.roleIdentifier|eq(5)}{skip}{/if}
                {if $user.id|eq($participant.id)}
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" checked="checked" disabled="disabled" />
                            {*<small>{$participant.contentobject.name|wash()}</small>*}
                            <small>{'Solo te stesso'|i18n('sensor/messages')}</small>
                            <input name="Collaboration_SensorItemPrivateMessageReceiver[]" type="hidden" value="{$participant.id}" />
                        </label>
                    </div>
                {else}
                    <div class="checkbox">
                        <label>
                            <input name="Collaboration_SensorItemPrivateMessageReceiver[]" checked="checked" type="checkbox" value="{$participant.id}" />
                            <small>{$participant.name|wash()}</small>
                        </label>
                    </div>
                {/if}
            {/foreach}

        </div>
    {/if}
</div>
{/if}