<div class="row">
    <div class="col-xs-12 col-md-12">
      <div class="well">
        <div class="comment_name"> <small>{'RISPOSTA DEL RESPONSABILE'|i18n('sensor/messages')}</small></div>
        <div class="comment_date"><i class="fa-time"></i>
            {if $is_read|not}<strong>{/if}{$item.created|l10n(shortdatetime)}{if $is_read|not}</strong>{/if}
            {if and( $message.creator_id|eq(fetch(user,current_user).contentobject_id), $sensor_post.can_respond )}
                <a class="btn btn-link text-warning btn-sm edit-message" href="#" data-message-id="{$message.id}"><i class="fa fa-edit"></i></a>
            {/if}
        </div>
        <div class="the_comment">
            {if and($message.creator_id|eq(fetch(user,current_user).contentobject_id), $sensor_post.can_respond )}
                <div id="edit-message-{$message.id}" style="display: none;">
                    <textarea name="Collaboration_SensorEditResponse[{$message.id}]" class="form-control" rows="3">{$message.data_text1}</textarea>
                    <input class="btn btn-info btn-sm btn-block" type="submit" name="CollaborationAction_EditResponse" value="{'Salva'|i18n('sensor/messages')}" />
                </div>
            {/if}
            <div id="view-message-{$message.id}">
                <p>{$message.data_text1|wash(xhtml)|break|autolink}</p>
            </div>
        </div>
      </div>
    </div>
</div>
