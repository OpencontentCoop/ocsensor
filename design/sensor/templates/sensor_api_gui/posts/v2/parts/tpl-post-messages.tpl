{literal}
<script id="tpl-post-messages" type="text/x-jsrender">
<div class="row" style="padding-bottom:20px">
    <div class="col-md-12">
        {{if _messages.length > 0}}

            <div class="message">
                {{for _messages ~currentUserId=currentUserId ~capabilities=capabilities ~settings=settings}}
                    <div id="message-{{:id}}" class="message-{{:_type}} {{if _type == 'system'}}panel panel-default{{else _type == 'private'}}panel panel-warning{{else _type == 'public'}}panel panel-success{{else _type == 'response'}}panel panel-primary{{/if}}"
                        {{if _type == 'audit'}}style="margin-bottom: 20px;display: none;"{{/if}}>
                        <div{{if _type != 'audit'}} class="panel-heading"{{/if}}{{if _type == 'system' || _type == 'audit'}} style="border-bottom: none;"{{/if}}>
                            <div class="media"{{if needModeration || !isRejected}} style="overflow:visible"{{/if}}>
                                {{if _type != 'audit'}}
                                <div class="pull-left">
                                    <img src="/sensor/avatar/{{:creator.id}}" class="img-circle" style="width: 50px; height: 50px; object-fit: cover;" />
                                </div>
                                {{/if}}
                                <div class="media-body"{{if needModeration || !isRejected}} style="overflow:visible"{{/if}}>
                                    {{if _type != 'audit'}}<p class="comment_name">{{/if}}
                                        {{if _type == 'system'}}
                                            <strong>{{:richText}}</strong>
                                        {{else _type == 'audit'}}
                                            <img src="/sensor/avatar/{{:creator.id}}" class="img-circle" style="width: 20px; height: 20px; object-fit: cover;" />
                                            <small>{{:~formatDate(published, 'DD/MM/YYYY HH:mm')}} - {{:richText}}</small>
                                        {{else _type == 'private'}}
                                            {{if ~capabilities.is_approver && isResponseProposal && ~settings.ShowResponseProposal}}
                                                <a href="#" data-message="{{:id}}" class="create-response-draft btn button-icon btn-primary pull-right"
                                                   style="margin-left:5px"
                                                   title="{{:~sensorTranslate('Create response from this note')}}"><i class="fa fa-edit"></i></a>
                                            {{/if}}
                                            {{if ~currentUserId == creator.id && ~capabilities.can_send_private_message}}
                                                <a class="btn btn-warning button-icon edit-message pull-right" href="#" data-message-id="{{:id}}" title="{{:~sensorTranslate('Edit')}}"><i class="fa fa-pencil"></i></a>
                                            {{/if}}
                                            <strong>{{:creator.name}}</strong> ha aggiunto una nota privata
                                            {{if receivers.length > 0}}
                                                  <p>{{:~sensorTranslate('Receivers')}} {{for receivers}}<span class="label label-warning">{{:name}}</span> {{/for}}</p>
                                            {{/if}}
                                        {{else _type == 'public'}}
                                            {{if ~capabilities.can_moderate_comment}}
                                                {{if needModeration || isRejected}}
                                                <div class="pull-right" data-action-wrapper>
                                                    <a href="#" data-message="{{:id}}" class="create-response-draft btn button-icon btn-success"
                                                       data-action="moderate_comment" data-parameters="comment_id,moderation"
                                                       style="margin-left:5px"
                                                       title="{{:~sensorTranslate('Make public')}}"><i class="fa fa-check"></i></a>
                                                   <input type="hidden" data-value="comment_id" value="{{:id}}" />
                                                   <input type="hidden" data-value="moderation" value="approve" />
                                                </div>
                                                {{/if}}
                                                {{if needModeration || !isRejected}}
                                                <div class="btn-group pull-right">
                                                    <button type="button" class="btn button-icon btn-danger btn-bold dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-times"></i>
                                                        <span class="caret"></span>
                                                        <span class="sr-only">{{:~sensorTranslate('Reject')}}</span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li data-action-wrapper>
                                                            <a href="#" data-action="moderate_comment" data-parameters="comment_id,moderation">
                                                                {{:~sensorTranslate('Reject for privacy reasons')}}
                                                            </a>
                                                            <input type="hidden" data-value="comment_id" value="{{:id}}" />
                                                            <input type="hidden" data-value="moderation" value="reject_privacy" />
                                                        </li>
                                                        <li data-action-wrapper>
                                                            <a href="#" data-action="moderate_comment" data-parameters="comment_id,moderation">
                                                                {{:~sensorTranslate('Reject for violation of the terms of use')}}
                                                            </a>
                                                            <input type="hidden" data-value="comment_id" value="{{:id}}" />
                                                            <input type="hidden" data-value="moderation" value="reject_terms" />
                                                        </li>
                                                        <li data-action-wrapper>
                                                            <a href="#" data-action="moderate_comment" data-parameters="comment_id,moderation">
                                                                {{:~sensorTranslate('Reject for other reasons')}}
                                                            </a>
                                                            <input type="hidden" data-value="comment_id" value="{{:id}}" />
                                                            <input type="hidden" data-value="moderation" value="reject_other" />
                                                        </li>
                                                    </ul>
                                                </div>
                                                {{/if}}
                                            {{/if}}
                                            {{!--{{if ~currentUserId == creator.id && ~capabilities.can_comment}}
                                                <a class="btn btn-success button-icon edit-message pull-right" href="#" data-message-id="{{:id}}" title="{{:~sensorTranslate('Edit')}}"><i class="fa fa-pencil"></i></a>
                                            {{/if}}--}}
                                            <strong>{{:creator.name}}</strong> {{:~sensorTranslate('added a comment')}}
                                        {{else _type != 'audit'}}
                                            {{if ~currentUserId == creator.id && ~capabilities.can_respond}}
                                                <a class="btn btn-default button-icon edit-message pull-right" href="#" data-message-id="{{:id}}" title="{{:~sensorTranslate('Edit')}}"><i class="fa fa-pencil"></i></a>
                                            {{/if}}
                                            <strong>{{:creator.name}}</strong> {{:~sensorTranslate('added a reponse')}}
                                        {{/if}}
                                    {{if _type != 'audit'}}</p>{{/if}}
                                    {{if _type != 'audit'}}
                                        {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}
                                    {{/if}}
                                    {{if _type == 'private' && isResponseProposal && ~settings.ShowResponseProposal}}- <strong>{{:~sensorTranslate('Reponse proposal')}}</strong>{{/if}}
                                    {{if _type == 'public' && needModeration}} <strong class="label label-danger">{{:~sensorTranslate('Waiting for moderation')}}</strong>{{/if}}
                                    {{if _type == 'public' && isRejected}} <strong class="label label-danger">{{:~sensorTranslate('Rejected')}}{{if rejectionReason}} {{:~sensorTranslate(rejectionReason)}}{{/if}}</strong>{{/if}}
                                </div>
                            </div>
                        </div>
                      {{if _type != 'system' && _type != 'audit'}}
                      <div class="panel-body">
                          {{if _type == 'private' && ~currentUserId == creator.id}}
                              <div id="edit-message-{{:id}}" style="display: none;" data-action-wrapper>
                                <input type="hidden" data-value="id" value="{{:id}}" />
                                <textarea data-value="text" class="form-control" rows="3">{{:text}}</textarea>
                                <input class="btn btn-sm btn-block" type="submit" data-action="edit_message" data-parameters="id,text" value="{{:~sensorTranslate('Store')}}" />
                              </div>
                          {{else _type == 'response' && ~currentUserId == creator.id}}
                              <div id="edit-message-{{:id}}" style="display: none;" data-action-wrapper>
                                <input type="hidden" data-value="id" value="{{:id}}" />
                                <textarea data-value="text" class="form-control" rows="3">{{:text}}</textarea>
                                <input class="btn btn-sm btn-block" type="submit" data-action="edit_response" data-parameters="id,text" value="{{:~sensorTranslate('Store')}}" />
                              </div>
                          {{!--{{else _type == 'public' &&  ~currentUserId == creator.id && ~capabilities.can_comment}}
                              <div id="edit-message-{{:id}}" style="display: none;" data-action-wrapper>
                                <input type="hidden" data-value="id" value="{{:id}}" />
                                <textarea data-value="text" class="form-control" rows="3">{{:text}}</textarea>
                                <input class="btn btn-sm btn-block" type="submit" data-action="edit_comment" data-parameters="id,text" value="{{:~sensorTranslate('Store')}}" />
                              </div>--}}
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
                        <small class="text-muted">{{:~sensorTranslate('The comment will be moderated.')}}</small>
                    {{/if}}
                    {{if capabilities.is_a == 'sensor_operator'}}
                        <small class="text-muted">
                            {{if capabilities.has_moderation}}
                                {{if privacy.identifier == 'public' && moderation.identifier != 'waiting'}}
                                    {{:~sensorTranslate('When approved, the comment will be public.')}}
                                {{else}}
                                    {{:~sensorTranslate('When approved, the comment will also be visible to the author of the report.')}}
                                {{/if}}
                            {{else}}
                                {{if privacy.identifier == 'public' && moderation.identifier != 'waiting'}}
                                    {{:~sensorTranslate('The comment will be public.')}}
                                {{else}}
                                    {{:~sensorTranslate('The comment will also be visible to the author of the report.')}}
                                {{/if}}
                            {{/if}}
                        </small>
                    {{/if}}
                    <textarea data-value="text" class="form-control" placeholder="{{:~sensorTranslate('Comment text')}}" rows="7"></textarea>
                    <div class="clearfix">
                        <a href="#" class="reset-message-form btn btn-default pull-left">{{:~sensorTranslate('Cancel')}}</a>
                        <input class="btn send btn-bold pull-right"
                               type="submit"
                               data-action="add_comment" data-parameters="text"
                               value="{{:~sensorTranslate('Store comment')}}"
                               {{if capabilities.is_a == 'sensor_operator' && !capabilities.has_moderation}}
                                   data-confirmation="{{if privacy.identifier == 'public' && moderation.identifier != 'waiting'}}{{:~sensorTranslate('Are you sure you want to add a public comment?')}}{{else}}{{:~sensorTranslate('Are you sure you want to add a comment visible to the author of the report?')}}{{/if}}"
                               {{/if}} />
                    </div>
                </div>
            {{/if}}
            {{if capabilities.can_send_private_message}}
                <div class="new_message action-form hide" data-action-wrapper>
                    <div class="alert alert-warning" style="margin-bottom:0">
                        <strong>{{:~sensorTranslate('Bring to the attention of')}}</strong>
                        <br /><small class="text-muted">{{:~sensorTranslate('The whole working group can read the note; a notification will be sent only to the selected participants')}}</small>
                        <ul class="list-inline private_message_receivers">
                            <li style="vertical-align: top;">
                                <div class="checkbox" style="display: inline-block;margin-bottom: 0;">
                                    <label>
                                        <input type="checkbox" class="group_select" data-toggle_group="approvers" />
                                        <span>{{:~sensorTranslate('Reference for the citizen')}}</span>
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
                                        <span>{{:~sensorTranslate('Operators in charge')}}</span>
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
                                        <span>{{:~sensorTranslate('Observers')}}</span>
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
                    <textarea data-value="text" class="form-control" placeholder="{{:~sensorTranslate('Add message')}}" rows="4"></textarea>
                    {{if settings.ShowResponseProposal}}
                    <div class="checkbox">
                        <label>
                            <input data-value="is_response_proposal" type="checkbox" value="1" />
                            <small>{{:~sensorTranslate('Propose as an official response')}}</small>
                        </label>
                    </div>
                    {{/if}}
                    <div class="clearfix">
                        <a href="#" class="reset-message-form btn btn-default  pull-left">{{:~sensorTranslate('Cancel')}}</a>
                        <input class="btn send btn-bold pull-right" type="submit" data-action="send_private_message" data-parameters="text,participant_ids,is_response_proposal" value="{{:~sensorTranslate('Add note')}}" />
                    </div>
                </div>
            {{/if}}
            {{if capabilities.can_respond}}
                <div class="new_response action-form hide" data-action-wrapper>
                    <textarea data-value="text" class="form-control" placeholder="{{:~sensorTranslate('Official response')}}" rows="7"></textarea>
                    <input type="hidden" data-value="label" value="sensor.approved" />
                    <div class="clearfix">
                        <a href="#" class="reset-message-form btn btn-default pull-left">{{:~sensorTranslate('Cancel')}}</a>
                        <div class="btn-group pull-right">
                            <button class="btn send btn-bold" type="submit" data-actions="add_response" data-parameters="text">{{:~sensorTranslate('Store the official response')}}</button>
                            {{if status.identifier !== 'deployed'}}
                            <button type="button" class="btn btn-bold dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">{{:~sensorTranslate('Show other options')}}</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="#" data-action="add_response,close" data-parameters="text">{{:~sensorTranslate('Store the official response and set the issue as rejected')}}</a></li>
                                {{if categories.length > 0 && protocols[0]}}
                                <li><a href="#" data-action="add_response,close" data-parameters="text,label">{{:~sensorTranslate('Store the official response and set the issue as approved')}}</a></li>
                                {{else}}
                                <li><span style="display: block;padding: 3px 20px;color: #ddd">{{:~sensorTranslate('Store the official response and set the issue as approved')}}</span></li>
                                {{/if}}
                            </ul>
                            {{/if}}
                        </div>
                    </div>
                </div>
            {{/if}}
        </div>

        <div class="text-right message-triggers">
            {{if capabilities.can_comment}}
                <a href="#" data-target="new_comment" class="btn btn-default">{{:~sensorTranslate('Add comment')}}</a>
            {{/if}}
            {{if capabilities.can_send_private_message}}
                <a href="#" data-target="new_message" class="btn btn-default">{{:~sensorTranslate('Add private message')}}</a>
            {{/if}}
            {{if capabilities.can_respond}}
                <a href="#" data-target="new_response" class="btn btn-default">{{:~sensorTranslate('Add official response')}}</a>
            {{/if}}
            {{if capabilities.can_add_image}}
                <div data-action-wrapper style="display: inline-block">
                    <form class="form-group" data-upload="add_image" style="display: inline-block;margin-right: 1px">
                        <div class="upload-button-container">
                            <span class="btn btn-default fileinput-button" style="cursor:pointer">
                                <strong>{{:~sensorTranslate('Add image')}}</strong>
                                <input class="upload" name="files" type="file" accept="image/*">
                            </span>
                        </div>
                        <div class="upload-button-spinner btn btn-default" style="display: none">
                            <i class="fa fa-cog fa-spin"></i>
                        </div>
                    </form>
                </div>
            {{/if}}
            {{if capabilities.can_add_file}}
                <div data-action-wrapper style="display: inline-block">
                    <form class="form-group" data-upload="add_file" style="display: inline-block;margin-right: 1px">
                        <div class="upload-button-container">
                            <span class="btn btn-default fileinput-button" style="cursor:pointer">
                                <strong>{{:~sensorTranslate('Add file')}}</strong>
                                <input class="upload" name="files" type="file" accept="application/pdf,.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                            </span>
                        </div>
                        <div class="upload-button-spinner btn btn-default" style="display: none">
                            <i class="fa fa-cog fa-spin"></i>
                        </div>
                    </form>
                </div>
            {{/if}}
        </div>
    </div>
</div>
</script>
{/literal}
