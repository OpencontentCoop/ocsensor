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
        {foreach $post.privateMessages.message as $message}
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
            {foreach $sensor_post.participants as $participant_role}
                {if $participant_role.role_id|eq(5)}{skip}{/if}
                {foreach $participant_role.items as $participant}
                    {if $participant.contentobject}
                        {if fetch(user,current_user).contentobject_id|eq($participant.contentobject.id)}
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" checked="checked" disabled="disabled" />
                                    {*<small>{$participant.contentobject.name|wash()}</small>*}
                                    <small>{'Solo te stesso'|i18n('sensor/messages')}</small>
                                    <input name="Collaboration_SensorItemPrivateMessageReceiver[]" type="hidden" value="{$participant.contentobject.id}" />
                                </label>
                            </div>
                        {else}
                            <div class="checkbox">
                                <label>
                                    <input name="Collaboration_SensorItemPrivateMessageReceiver[]" checked="checked" type="checkbox" value="{$participant.contentobject.id}" />
                                    <small>{$participant.contentobject.name|wash()}</small>
                                </label>
                            </div>
                        {/if}
                    {/if}
                {/foreach}
            {/foreach}

        </div>
    {/if}
</div>
{/if}