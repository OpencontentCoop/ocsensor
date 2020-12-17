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
                                    <img src="/sensor/avatar/{{:creator.id}}" class="img-circle" style="width: 50px; height: 50px; object-fit: cover;" />
                                </div>
                                <div class="media-body">
                                    <p class="comment_name">
                                        {{if _type == 'system'}}
                                            <strong>{{:richText}}</strong>
                                        {{else _type == 'private'}}
                                            {{if ~capabilities.is_approver && isResponseProposal && settings.ShowResponseProposal}}
                                                <a href="#" data-message="{{:id}}" class="create-response-draft btn button-icon btn-primary pull-right"
                                                   style="margin-left:5px"
                                                   title="{/literal}{"Crea risposta a partire da questa nota"|i18n('sensor/messages')}{literal}"><i class="fa fa-edit"></i></a>
                                            {{/if}}
                                            {{if ~currentUserId == creator.id && ~capabilities.can_send_private_message}}
                                                <a class="btn btn-warning button-icon edit-message pull-right" href="#" data-message-id="{{:id}}" title="{/literal}{"Modifica"|i18n('sensor/messages')}{literal}"><i class="fa fa-pencil"></i></a>
                                            {{/if}}
                                            <strong>{{:creator.name}}</strong> ha aggiunto una nota privata
                                            {{if receivers.length > 0}}
                                                  <p>{/literal}{"All'attenzione di:"|i18n('sensor/messages')}{literal} {{for receivers}}<span class="label label-warning">{{:name}}</span> {{/for}}</p>
                                            {{/if}}
                                        {{else _type == 'public'}}
                                            {{if ~capabilities.can_moderate_comment && needModeration}}
                                                <div class="pull-right" data-action-wrapper>
                                                    <a href="#" data-message="{{:id}}" class="create-response-draft btn button-icon btn-danger"
                                                       data-action="moderate_comment" data-parameters="comment_id"
                                                       style="margin-left:5px"
                                                       title="{/literal}{"Rendi pubblico"|i18n('sensor/messages')}{literal}"><i class="fa fa-unlock"></i></a>
                                                   <input type="hidden" data-value="comment_id" value="{{:id}}" />
                                               </div>
                                            {{/if}}
                                            {{if ~currentUserId == creator.id && ~capabilities.can_comment}}
                                                <a class="btn btn-success button-icon edit-message pull-right" href="#" data-message-id="{{:id}}" title="{/literal}{"Modifica"|i18n('sensor/messages')}{literal}"><i class="fa fa-pencil"></i></a>
                                            {{/if}}
                                            <strong>{{:creator.name}}</strong> ha aggiunto un commento {{if !needModeration}}pubblico{{/if}}
                                        {{else}}
                                            {{if ~currentUserId == creator.id && ~capabilities.can_respond}}
                                                <a class="btn btn-default button-icon edit-message pull-right" href="#" data-message-id="{{:id}}" title="{/literal}{"Modifica"|i18n('sensor/messages')}{literal}"><i class="fa fa-pencil"></i></a>
                                            {{/if}}
                                            <strong>{{:creator.name}}</strong> ha risposto alla segnalazione
                                        {{/if}}
                                    </p>
                                    {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}
                                    {{if _type == 'private' && isResponseProposal && settings.ShowResponseProposal}}- <strong>Proposta di risposta</strong>{{/if}}
                                    {{if _type == 'public' && needModeration}} <strong class="label label-danger">In attesa di moderazione</strong>{{/if}}
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
                    {{if capabilities.has_moderation}}
                        <small class="text-muted">{/literal}{'Il commento sarà sottoposto a moderazione.'|i18n('sensor/messages')|wash()}{literal}</small>
                    {{/if}}
                    {{if capabilities.is_a == 'sensor_operator'}}
                        <small class="text-muted">
                            {{if capabilities.has_moderation}}
                                {{if privacy.identifier == 'public' && moderation.identifier != 'waiting'}}
                                    {/literal}{'Quando verrà approvato il commento sarà pubblico.'|i18n('sensor/messages')|wash()}{literal}
                                {{else}}
                                    {/literal}{'Quando verrà approvato il commento sarà visibile anche all\'autore della segnalazione.'|i18n('sensor/messages')|wash()}{literal}
                                {{/if}}
                            {{else}}
                                {{if privacy.identifier == 'public' && moderation.identifier != 'waiting'}}
                                    {/literal}{'Il commento sarà pubblico.'|i18n('sensor/messages')}{literal}
                                {{else}}
                                    {/literal}{'Il commento sarà visibile anche all\'autore della segnalazione.'|i18n('sensor/messages')|wash()}{literal}
                                {{/if}}
                            {{/if}}
                        </small>
                    {{/if}}
                    <textarea data-value="text" class="form-control" placeholder="{/literal}{'Testo del commento'|i18n('sensor/messages')|wash()}{literal}" rows="7"></textarea>
                    <div class="clearfix">
                        <a href="#" class="reset-message-form btn btn-default pull-left">Annulla</a>
                        <input class="btn send btn-bold pull-right"
                               type="submit"
                               data-action="add_comment" data-parameters="text"
                               value="{/literal}{'Pubblica il commento'|i18n('sensor/messages')}{literal}"
                               {{if capabilities.is_a == 'sensor_operator' && !capabilities.has_moderation}}
                                   data-confirmation="{{if privacy.identifier == 'public' && moderation.identifier != 'waiting'}}{/literal}{'Sei sicuro di voler aggiungere un commento pubblico?'|i18n( 'sensor/messages' )|wash()}{literal}{{else}}{/literal}{"Sei sicuro di voler aggiungere un commento visibile anche all'autore della segnalazione?"|i18n( 'sensor/messages' )|wash()}{literal}{{/if}}"
                               {{/if}} />
                    </div>
                </div>
            {{/if}}
            {{if capabilities.can_send_private_message}}
                <div class="new_message action-form hide" data-action-wrapper>
                    <div class="alert alert-warning" style="margin-bottom:0">
                        <strong>{/literal}{"Poni all'attenzione di:"|i18n('sensor/messages')}{literal}</strong>
                        <br /><small class="text-muted">{/literal}{"Tutto il gruppo di lavoro può leggere la nota; verrà inviata una notifica solo ai partecipanti selezionati"|i18n('sensor/messages')}{literal}</small>
                        <ul class="list-inline private_message_receivers">
                            <li style="vertical-align: top;">
                                <div class="checkbox" style="display: inline-block;margin-bottom: 0;">
                                    <label>
                                        <input type="checkbox" class="group_select" data-toggle_group="approvers" />
                                        <span>{/literal}{"Riferimenti per il cittadino"|i18n('sensor/dashboard')}{literal}</span>
                                    </label>
                                </div>
                                <ul class="list-unstyled group_receivers hide" data-group="approvers" style="margin-left: 15px;">
                                {{for approvers ~currentUserId=currentUserId}}
                                {{if ~currentUserId != id}}
                                    <li>
                                    <div class="checkbox" style="margin-bottom: 3px;margin-top: 0;">
                                        <label>
                                            <input data-value="participant_ids" type="checkbox" value="{{:id}}" />
                                            <small>{{:name}}</small>
                                        </label>
                                    </div>
                                    </li>
                                {{/if}}
                                {{/for}}
                                </ul>
                            </li>
                            <li style="vertical-align: top;">
                                <div class="checkbox" style="display: inline-block;margin-bottom: 0;">
                                    <label>
                                        <input type="checkbox" class="group_select" data-toggle_group="owners" />
                                        <span>{/literal}{"Incaricati"|i18n('sensor/dashboard')}{literal}</span>
                                    </label>
                                </div>
                                <ul class="list-unstyled group_receivers hide" data-group="owners" style="margin-left: 15px;">
                                {{for owners ~currentUserId=currentUserId}}
                                {{if ~currentUserId != id}}
                                    <li>
                                    <div class="checkbox" style="margin-bottom: 3px;margin-top: 0;">
                                        <label>
                                            <input data-value="participant_ids" type="checkbox" value="{{:id}}" />
                                            <small>{{:name}}</small>
                                        </label>
                                    </div>
                                    </li>
                                {{/if}}
                                {{/for}}
                                </ul>
                            </li>
                            <li style="vertical-align: top;">
                                <div class="checkbox" style="display: inline-block;margin-bottom: 0;">
                                    <label>
                                        <input type="checkbox" class="group_select" data-toggle_group="observers" />
                                        <span>{/literal}{"Osservatori"|i18n('sensor/dashboard')}{literal}</span>
                                    </label>
                                </div>
                                <ul class="list-unstyled group_receivers hide" data-group="observers" style="margin-left: 15px;">
                                {{for observers ~currentUserId=currentUserId}}
                                {{if ~currentUserId != id}}
                                    <li>
                                    <div class="checkbox" style="margin-bottom: 3px;margin-top: 0;">
                                        <label>
                                            <input data-value="participant_ids" type="checkbox" value="{{:id}}" />
                                            <small>{{:name}}</small>
                                        </label>
                                    </div>
                                    </li>
                                {{/if}}
                                {{/for}}
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <textarea data-value="text" class="form-control" placeholder="{/literal}{'Aggiungi messaggio'|i18n('sensor/messages')}{literal}" rows="4"></textarea>
                    {{if settings.ShowResponseProposal}}
                    <div class="checkbox">
                        <label>
                            <input data-value="is_response_proposal" type="checkbox" value="1" />
                            <small>{/literal}{"Proponi come risposta ufficiale"|i18n('sensor/dashboard')}{literal}</small>
                        </label>
                    </div>
                    {{/if}}
                    <div class="clearfix">
                        <a href="#" class="reset-message-form btn btn-default  pull-left">Annulla</a>
                        <input class="btn send btn-bold pull-right" type="submit" data-action="send_private_message" data-parameters="text,participant_ids,is_response_proposal" value="{/literal}{'Aggiungi nota'|i18n('sensor/messages')}{literal}" />
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
            {{if capabilities.can_add_image}}
                <div data-action-wrapper style="display: inline-block">
                    <form class="form-group" data-upload="add_image" style="display: inline-block;margin-right: 1px">
                        <div class="upload-button-container">
                            <span class="btn btn-default fileinput-button" style="cursor:pointer">
                                <strong>{/literal}{'Aggiungi immagine'|i18n('sensor/messages')}{literal}</strong>
                                <input class="upload" name="files" type="file">
                            </span>
                        </div>
                        <div class="upload-button-spinner btn btn-default" style="display: none">
                            <i class="fa fa-cog fa-spin"></i>
                        </div>
                    </form>
                </div>
                {{else capabilities.is_author}}
                    <div class="btn btn-default fileinput-button button-disabled">
                        <strong class="text-muted">{/literal}{'Aggiungi immagine'|i18n('sensor/messages')}{literal}</strong>
                    </div>
                {{/if}}
        </div>
    </div>
</div>
</script>
{/literal}