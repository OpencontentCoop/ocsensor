<div class="row">
    <figure class="col-xs-2 col-md-2">
        {include uri='design:parts/user_image.tpl' user=$message.creator}
    </figure>
    <div class="col-xs-10 col-md-10">
        <div class="comment_name">
            {$message.creator.name|wash()}
        </div>
        <div class="comment_date"><i class="fa-time"></i>
            {$message.published|sensor_datetime('format', 'shortdatetime')}
            {if and( $message.creator.id|eq($user.id), $user.permissions.can_comment )}
                <a class="btn btn-warning btn-sm edit-message" href="#" data-message-id="{$message.id}"><i class="fa fa-edit"></i></a>
            {/if}
        </div>
        <div class="the_comment">
            {if and( $message.creator.id|eq($user.id), $user.permissions.can_comment )}
                <div id="edit-message-{$message.id}" style="display: none;">
                    <textarea name="Collaboration_SensorEditComment[{$message.id}]" class="form-control" rows="3">{$message.text}</textarea>
                    <input class="btn send btn-primary btn-md pull-right" type="submit" name="CollaborationAction_EditComment" value="{'Salva'|i18n('sensor/messages')}" />
                </div>
            {/if}
            <div id="view-message-{$message.id}">
                <p>{$message.text|nl2br|autolink()}</p>
            </div>
        </div>
    </div>
</div>