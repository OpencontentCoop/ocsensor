{literal}
<script id="tpl-post-messages" type="text/x-jsrender">
<div class="row">
    <div class="col-md-12" id="current-post-messages">
        <div id="post_comments">
            {{if comments.length > 0}}
                <div class="comment">
                    <h4>{/literal}{'Commenti'|i18n('sensor/messages')}{literal}</h4>
                    {{for comments ~currentUserId=currentUserId ~capabilities=capabilities}}
                        <div class="row">
                            <figure class="col-xs-2 col-md-2">
                                <img src="/sensor/avatar/{{:creator.id}}" class="img-circle" />
                            </figure>
                            <div class="col-xs-10 col-md-10">
                                <div class="comment_name">
                                    {{:creator.name}}
                                </div>
                                <div class="comment_date"><i class="fa-time"></i>
                                    {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}
                                    {{if ~currentUserId == creator.id && ~capabilities.can_comment}}
                                        <a class="btn btn-warning btn-sm edit-message" href="#" data-message-id="{{:id}}"><i class="fa fa-edit"></i></a>
                                    {{/if}}
                                </div>
                                <div class="the_comment">
                                    {{if  ~currentUserId == creator.id && ~capabilities.can_comment}}
                                        <div id="edit-message-{{:id}}" style="display: none;" data-action-wrapper>
                                            <input type="hidden" data-value="id" value="{{:id}}" />
                                            <textarea data-value="text" class="form-control" rows="3">{{:text}}</textarea>
                                            <input class="btn btn-info btn-sm btn-block" type="submit" data-action="edit_comment" data-parameters="id,text" value="{/literal}{'Salva'|i18n('sensor/messages')}{literal}" />
                                        </div>
                                    {{/if}}
                                    <div id="view-message-{{:id}}">
                                        <p>{{:richText}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {{/for}}
                </div>
            {{/if}}
            {{if capabilities.can_comment}}
            <div class="new_comment" data-action-wrapper>
                <h4>{/literal}{'Aggiungi un commento'|i18n('sensor/messages')}{literal}</h4>
                {{if capabilities.can_send_private_message}}
                <small class="text-muted"><i class="fa fa-warning"></i> {/literal}{'Attenzione il commento sarà visibile a tutti. Per inviare un messaggio visibile solo al team usa il bottone \"Messaggi privati\".'|i18n('sensor/messages')}{literal}</small>
                {{/if}}
                <div class="row">
                    <div class="col-sm-8 col-md-8"><br>
                        <textarea data-value="text" class="form-control" placeholder="{/literal}{'Testo del commento'|i18n('sensor/messages')}{literal}" rows="7"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-8 col-md-8">
                        <input class="btn send btn-primary btn-lg btn-block"
                               type="submit"
                               data-action="add_comment" data-parameters="text"
                               value="{/literal}{'Pubblica il commento'|i18n('sensor/messages')}{literal}"
                               {{if capabilities.can_send_private_message}}
                                   data-confirmation="{/literal}{'Sei sicuro di voler aggiungere un commento visibile a tutti?'|i18n( 'sensor/messages' )|wash(javascript)}{literal}"
                               {{/if}} />
                    </div>
                </div>
            </div>
            {{/if}}
        </div>
        <div id="post_messages">
            {{if responses.length > 0}}
                <div class="comment">
                    <h4>{/literal}{'Risposte ufficiali'|i18n('sensor/messages')}{literal}</h4>
                    {{for responses ~currentUserId=currentUserId ~capabilities=capabilities}}
                        <div class="row">
                            <div class="col-xs-12 col-md-12">
                              <div class="well">
                                <div class="comment_name"> <small>{/literal}{'RISPOSTA DEL RESPONSABILE'|i18n('sensor/messages')}{literal}</small></div>
                                <div class="comment_date"><i class="fa-time"></i>
                                    {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}
                                    {{if ~currentUserId == creator.id}}
                                        <a class="btn btn-warning btn-sm edit-message" href="#" data-message-id="{{:id}}"><i class="fa fa-edit"></i></a>
                                    {{/if}}
                                </div>
                                <div class="the_comment">
                                    {{if  ~currentUserId == creator.id}}
                                        <div id="edit-message-{{:id}}" style="display: none;" data-action-wrapper>
                                            <input type="hidden" data-value="id" value="{{:id}}" />
                                            <textarea data-value="text" class="form-control" rows="3">{{:text}}</textarea>
                                            <input class="btn btn-info btn-sm btn-block" type="submit" data-action="edit_response" data-parameters="id,text" value="{/literal}{'Salva'|i18n('sensor/messages')}{literal}" />
                                        </div>
                                    {{/if}}
                                    <div id="view-message-{{:id}}">
                                        <p>{{:richText}}</p>
                                    </div>
                                </div>
                              </div>
                            </div>
                        </div>
                    {{/for}}
                </div>
            {{/if}}
            {{if capabilities.can_respond}}
            <div class="new_comment" data-action-wrapper style="margin-bottom: 20px;">
                <h4>{/literal}{'Aggiungi risposta ufficiale'|i18n('sensor/messages')}{literal}</h4>
                <div class="row">
                    <div class="col-sm-8 col-md-8"><br>
                        <textarea data-value="text" class="form-control" placeholder="{/literal}{'Risposta ufficiale'|i18n('sensor/messages')}{literal}" rows="7"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-8 col-md-8">
                        <input class="btn send btn-success btn-lg btn-block" type="submit" data-action="add_response" data-parameters="text" value="{/literal}{'Pubblica la risposta ufficiale'|i18n('sensor/messages')}{literal}" />
                    </div>
                </div>
            </div>
            {{/if}}
            {{if attachments.length > 0}}
                <h4>{/literal}{'Allegati'|i18n('sensor/messages')}{literal}</h4>
                <div class="well" data-action-wrapper>
                    <ul class="list-unstyled" style="margin:0">
                    {{for attachments ~capabilities=capabilities}}
                        <li>
                            {{if ~capabilities.can_remove_attachment}}
                                <input type="checkbox"
                                       style="margin-right: 10px;"
                                       data-value="files"
                                       value="{{:filename}}" />
                            {{/if}}
                            <a href="{{:downloadUrl}}" target="_blank"><i class="fa fa-download"></i> {{:filename}}</a>
                        </li>
                    {{/for}}
                    </ul>
                    {{if capabilities.can_remove_attachment}}
                        <button class="btn btn-link text-danger" type="submit" style="margin-left: -10px;"
                                data-action="remove_attachment" data-parameters="files">
                            <i class="fa fa-trash"></i> {/literal}{'Rimuovi file selezionati'|i18n('sensor/messages')}{literal}
                        </button>
                    {{/if}}
                </div>
            {{/if}}
            {{if capabilities.can_add_attachment}}
                <form data-upload="add_attachment">
                    <div class="clearfix upload-button-container">
                        <span class="btn btn-success btn-lg fileinput-button">
                            <i class="fa fa-plus"></i>
                            <span>{/literal}{'Aggiungi allegato'|i18n('sensor/messages')}{literal}</span>
                            <input class="upload" name="files" type="file" />
                        </span>
                    </div>
                    <div class="clearfix upload-button-spinner" style="display: none">
                        <i class="fa fa-cog fa-spin fa-3x"></i>
                    </div>
                </form>
            {{/if}}
        </div>
    </div>
</div>
</script>
{/literal}