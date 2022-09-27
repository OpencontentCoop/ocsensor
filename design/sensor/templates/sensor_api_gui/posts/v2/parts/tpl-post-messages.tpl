{literal}
<script id="tpl-post-messages" type="text/x-jsrender">
<div class="row" style="padding-bottom:20px">
    <div class="col-md-12">
        {{if _messages.length > 0}}

            <div class="message">
                {{for _messages ~currentUserId=currentUserId ~capabilities=capabilities}}
                    <div class="message-{{:_type}} panel panel-{{if _type == 'system'}}default{{else _type == 'private'}}warning{{else _type == 'public'}}success{{else}}primary{{/if}}">
                        <div class="panel-heading"{{if _type == 'system'}} style="border-bottom: none;"{{/if}}>
                            <div class="media">
                                <div class="pull-left">
                                    <img src="{{:accessPath}}/sensor/avatar/{{:creator.id}}" class="img-circle" style="width: 50px; height: 50px; object-fit: cover;" />
                                </div>
                                <div class="media-body">
                                    <p class="comment_name">
                                        {{if _type == 'system'}}
                                            <strong>{{:richText}}</strong>
                                        {{else _type == 'private'}}
                                            {{if ~currentUserId == creator.id && ~capabilities.can_send_private_message}}
                                                <a class="btn btn-warning button-icon edit-message pull-right" href="#" data-message-id="{{:id}}"><i class="fa fa-pencil"></i></a>
                                            {{/if}}
                                            <strong>{{:creator.name}}</strong> ha aggiunto una nota privata
                                            {{if receivers.length > 0}}
                                                  {/literal}{'a'|i18n('sensor/messages')}{literal} {{for receivers}}{{if #index > 0}}, {{/if}}{{:name}}{{/for}}
                                            {{/if}}
                                        {{else _type == 'public'}}
                                            {{if ~currentUserId == creator.id && ~capabilities.can_comment}}
                                                <a class="btn btn-success button-icon edit-message pull-right" href="#" data-message-id="{{:id}}"><i class="fa fa-pencil"></i></a>
                                            {{/if}}
                                            <strong>{{:creator.name}}</strong> ha aggiunto un commento pubblico
                                        {{else}}
                                            {{if ~currentUserId == creator.id && ~capabilities.can_respond}}
                                                <a class="btn btn-default button-icon edit-message pull-right" href="#" data-message-id="{{:id}}"><i class="fa fa-pencil"></i></a>
                                            {{/if}}
                                            <strong>{{:creator.name}}</strong> ha risposto alla segnalazione
                                        {{/if}}
                                    </p>
                                    {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}
                                </div>
                            </div>
                        </div>
                      {{if _type != 'system'}}  
                      <div class="panel-body">
                          {{if _type == 'private' && ~currentUserId == creator.id}}
                              <div id="edit-message-{{:id}}" style="display: none;" data-action-wrapper>
                                <input type="hidden" data-value="id" value="{{:id}}" />
                                <textarea data-value="text" class="form-control" rows="3">{{:text}}</textarea>
                                <input class="btn btn-sm btn-block" type="submit" data-action="edit_message" data-parameters="id,text" value="{/literal}{'Salva'|i18n('sensor/messages')}{literal}" />
                              </div>
                          {{else _type == 'response' && ~currentUserId == creator.id}}
                              <div id="edit-message-{{:id}}" style="display: none;" data-action-wrapper>
                                <input type="hidden" data-value="id" value="{{:id}}" />
                                <textarea data-value="text" class="form-control" rows="3">{{:text}}</textarea>
                                <input class="btn btn-sm btn-block" type="submit" data-action="edit_response" data-parameters="id,text" value="{/literal}{'Salva'|i18n('sensor/messages')}{literal}" />
                              </div>
                          {{else _type == 'public' &&  ~currentUserId == creator.id && ~capabilities.can_comment}}
                              <div id="edit-message-{{:id}}" style="display: none;" data-action-wrapper>
                                <input type="hidden" data-value="id" value="{{:id}}" />
                                <textarea data-value="text" class="form-control" rows="3">{{:text}}</textarea>
                                <input class="btn btn-sm btn-block" type="submit" data-action="edit_comment" data-parameters="id,text" value="{/literal}{'Salva'|i18n('sensor/messages')}{literal}" />
                              </div>
                          {{/if}}                          
                          <div id="view-message-{{:id}}">
                              {{:richText}}
                          </div>                          
                      </div>
                      {{/if}}
                    </div>
                {{/for}}
            </div>
        {{/if}}

        <div class="message message-form">
            {{if capabilities.can_comment}}
                <div class="new_comment action-form hide" data-action-wrapper>
                    {{if capabilities.can_send_private_message}}
                        <small class="text-muted"><i class="fa fa-warning"></i> {/literal}{'Attenzione il commento sarà visibile a tutti. Per inviare un messaggio visibile solo al team usa il bottone \"Messaggi privati\".'|i18n('sensor/messages')}{literal}</small>
                    {{/if}}
                    <textarea data-value="text" class="form-control" placeholder="{/literal}{'Testo del commento'|i18n('sensor/messages')}{literal}" rows="7"></textarea>
                    <div class="clearfix">
                        <a href="#" class="reset-message-form btn btn-default pull-left">Annulla</a>
                        <input class="btn send btn-bold pull-right"
                               type="submit"
                               data-action="add_comment" data-parameters="text"
                               value="{/literal}{'Pubblica il commento'|i18n('sensor/messages')}{literal}"
                               {{if capabilities.can_send_private_message}}
                                   data-confirmation="{/literal}{'Sei sicuro di voler aggiungere un commento visibile a tutti?'|i18n( 'sensor/messages' )|wash(javascript)}{literal}"
                               {{/if}} />
                    </div>
                </div>
            {{/if}}
            {{if capabilities.can_send_private_message}}
                <div class="new_message action-form hide" data-action-wrapper>
                    <strong>{/literal}{'Chi può leggere questo messaggio?'|i18n('sensor/messages')}{literal}</strong>
                    {{for participants ~currentUserId=currentUserId}}
                        {{if roleIdentifier != 5}}
                            {{if ~currentUserId == id}}
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" checked="checked" disabled="disabled" />
                                    <input class="hide" type="checkbox" data-value="participant_ids" value="{{:id}}" checked="checked" />
                                    <small>{/literal}{'Solo te stesso'|i18n('sensor/messages')}{literal}</small>
                                </label>
                            </div>
                            {{/if}}
                        {{/if}}
                    {{/for}}
                    {{for participants ~currentUserId=currentUserId}}
                        {{if roleIdentifier != 5}}
                            {{if ~currentUserId != id}}
                            <div class="checkbox">
                                <label>
                                    <input checked="checked" data-value="participant_ids" type="checkbox" value="{{:id}}" />
                                    <small>{{:name}}</small>
                                </label>
                            </div>
                            {{/if}}
                        {{/if}}
                    {{/for}}
                    <textarea data-value="text" class="form-control" placeholder="{/literal}{'Aggiungi messaggio'|i18n('sensor/messages')}{literal}" rows="4"></textarea>
                    <div class="clearfix">
                        <a href="#" class="reset-message-form btn btn-default  pull-left">Annulla</a>
                        <input class="btn send btn-bold pull-right" type="submit" data-action="send_private_message" data-parameters="text,participant_ids" value="{/literal}{'Aggiungi nota'|i18n('sensor/messages')}{literal}" />
                    </div>
                </div>
            {{/if}}
            {{if capabilities.can_respond}}
                <div class="new_response action-form hide" data-action-wrapper>
                    <textarea data-value="text" class="form-control" placeholder="{/literal}{'Risposta ufficiale'|i18n('sensor/messages')}{literal}" rows="7"></textarea>
                    <div class="clearfix">
                        <a href="#" class="reset-message-form btn btn-default pull-left">Annulla</a>
                        <input class="btn send btn-bold pull-right" type="submit" data-action="add_response" data-parameters="text" value="{/literal}{'Pubblica la risposta ufficiale'|i18n('sensor/messages')}{literal}" />
                    </div>
                </div>
            {{/if}}
        </div>

        <div class="text-right message-triggers">
            {{if capabilities.can_comment}}
                <a href="#" data-target="new_comment" class="btn btn-default">{/literal}{'Aggiungi un commento'|i18n('sensor/messages')}{literal}</a>
            {{/if}}
            {{if capabilities.can_send_private_message}}
                <a href="#" data-target="new_message" class="btn btn-default">{/literal}{'Aggiungi nota privata'|i18n('sensor/messages')}{literal}</a>
            {{/if}}
            {{if capabilities.can_respond}}
                <a href="#" data-target="new_response" class="btn btn-default">{/literal}{'Aggiungi risposta ufficiale'|i18n('sensor/messages')}{literal}</a>
            {{/if}}
        </div>
    </div>
</div>
</script>
{/literal}