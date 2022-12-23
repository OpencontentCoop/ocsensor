{literal}
<script id="tpl-post-sidebar" type="text/x-jsrender">

    <div class="widget">
        <strong class="widget-title">{{:~sensorTranslate('Subscriptions')}}</strong>
        <ul class="list-unstyled widget-content" data-subscriptions="" style="max-height: 200px;overflow-y: auto;">
            <li><i class="fa fa-circle-o-notch fa-spin fa-fw"></i></li>
        </ul>
    </div>

    <div class="widget">
        {{if capabilities.can_add_approver && groupsTree.children.length > 0}}
            <a href="#" class="pull-right action-trigger{{if !settings.AllowChangeApprover}} hide{{/if}}" data-reverse="{{:~sensorTranslate('Cancel')}}">{{:~sensorTranslate('Edit')}}</a>
        {{/if}}
        <strong class="widget-title">{{:~sensorTranslate('Reference for the citizen')}}</strong>
        <ul class="list-unstyled widget-content">
        {{for approvers}}
            <li>
                <img src="/sensor/avatar/{{:id}}" class="img-circle" style="width: 20px; height: 20px; object-fit: cover; margin-right:5px" />
                {{:name}}
            </li>
        {{else}}
            <li>
                <em class="text-muted">{{:~sensorTranslate('Undefined')}}</em>
            </li>
        {{/for}}
        </ul>
        {{if groupsTree.children.length > 0}}
        {{include tmpl="#tpl-post-add_approver"/}}
        {{/if}}
    </div>

    {{if capabilities.can_send_private_message || !settings.HideTimelineDetails}}
        {{if capabilities.can_assign || owners.length || latestOwner || latestOwnerGroup}}
        <div class="widget">
            {{if capabilities.can_assign}}
                <a href="#" class="pull-right action-trigger" data-reverse="{{:~sensorTranslate('Cancel')}}">{{:~sensorTranslate('Edit')}}</a>
            {{/if}}
            <strong class="widget-title">{{:~sensorTranslate('Group and operator in charge')}}</strong>
            <ul class="list-unstyled widget-content">
            {{if owners.length}}
                {{for owners}}
                    <li>
                        <img src="/sensor/avatar/{{:id}}" class="img-circle" style="width: 20px; height: 20px; object-fit: cover; margin-right:5px" />
                        {{:name}}
                    </li>
                {{/for}}
            {{else}}
                {{if capabilities.can_assign}}
                <li>
                    <em class="text-muted">{{:~sensorTranslate('Undefined')}}</em>
                </li>
                {{/if}}
                {{if latestOwner || latestOwnerGroup}}
                    <li style="opacity:.5;margin-top: 5px;"><strong style="margin-bottom: 5px;" class="widget-title"><small>{{:~sensorTranslate('Last assignment')}}</small></strong></li>
                    {{if latestOwner}}
                    <li style="opacity:.5;font-size: .875em;">
                        <img src="/sensor/avatar/{{:latestOwner.id}}" class="img-circle" style="width: 20px; height: 20px; object-fit: cover; margin-right:5px" />
                        <em>{{:latestOwner.name}}</em>
                    </li>
                    {{/if}}
                    {{if latestOwnerGroup}}
                    <li style="opacity:.5;font-size: .875em;">
                        <img src="/sensor/avatar/{{:latestOwnerGroup.id}}" class="img-circle" style="width: 20px; height: 20px; object-fit: cover; margin-right:5px" />
                        <em>{{:latestOwnerGroup.name}}</em>
                    </li>
                    {{/if}}
                {{/if}}
            {{/if}}
            </ul>
            {{include tmpl="#tpl-post-assign"/}}
        </div>
        {{/if}}

        {{if capabilities.can_add_observer || observers.length}}
        <div class="widget">
            {{if capabilities.can_add_observer}}
                <a href="#" class="pull-right action-trigger" data-reverse="{{:~sensorTranslate('Cancel')}}">{{:~sensorTranslate('Edit')}}</a>
            {{/if}}
            <strong class="widget-title">{{:~sensorTranslate('Observers')}}</strong>
            <ul class="list-unstyled widget-content">
            {{for observers ~capabilities=capabilities}}
                <li data-action-wrapper>
                    <img src="/sensor/avatar/{{:id}}" class="img-circle" style="width: 20px; height: 20px; object-fit: cover; margin-right:5px" />
                    {{:name}}
                    {{if ~capabilities.can_remove_observer}}
                    <a href="#"
                       data-action="remove_observer" data-parameters="participant_id"
                       title="{{:~sensorTranslate('Remove observer')}}"><i class="fa fa-times"></i></a>
                    <input type="hidden" value="{{:id}}" data-value="participant_id" />
                    {{/if}}
                </li>
            {{else}}
                <li>
                    <em class="text-muted">{{:~sensorTranslate('Undefined')}}</em>
                </li>
            {{/for}}
            </ul>
            {{include tmpl="#tpl-post-add_observer"/}}
        </div>
        {{/if}}
    {{/if}}

    <div class="widget hide">
        {{if capabilities.can_set_type}}
            <a href="#" class="pull-right action-trigger" data-reverse="{{:~sensorTranslate('Cancel')}}">{{:~sensorTranslate('Edit')}}</a>
        {{/if}}
        <strong class="widget-title">{{:~sensorTranslate('Type')}}</strong>
        <p class="widget-content">{{if type}}{{:type.name}}{{else}}<em class="text-muted">{{:~sensorTranslate('Undefined')}}</em>{{/if}}</p>
        {{include tmpl="#tpl-post-set_type"/}}
    </div>

    <div class="widget">
        {{if capabilities.can_add_area}}
            <a href="#" class="pull-right action-trigger" data-reverse="{{:~sensorTranslate('Cancel')}}">{{:~sensorTranslate('Edit')}}</a>
        {{/if}}
        <strong class="widget-title">{{:~sensorTranslate('Area')}}</strong>
        <p class="widget-content">{{for areas}}{{:name}}{{else}}<em class="text-muted">{{:~sensorTranslate('Undefined')}}</em>{{/for}}</p>
        {{include tmpl="#tpl-post-add_area"/}}
    </div>

    <div class="widget">
        {{if capabilities.can_add_category}}
            <a href="#" class="pull-right action-trigger" data-reverse="{{:~sensorTranslate('Cancel')}}">{{:~sensorTranslate('Edit')}}</a>
        {{/if}}
        <strong class="widget-title">{{:~sensorTranslate('Category')}}</strong>
        <p class="widget-content">{{for categories}}{{:name}}{{else}}<em class="text-muted">{{:~sensorTranslate('Undefined')}}</em>{{/for}}</p>
        {{include tmpl="#tpl-post-add_category"/}}
    </div>

    {{if capabilities.can_send_private_message}}
        <div class="widget">
            {{if capabilities.can_set_expiry}}
                <a href="#" class="pull-right action-trigger" data-reverse="{{:~sensorTranslate('Cancel')}}">{{:~sensorTranslate('Edit')}}</a>
            {{/if}}
            <strong class="widget-title">{{:~sensorTranslate('Expiry')}}</strong>
            <p class="widget-content">{{:~formatDate(expirationInfo.expirationDateTime, 'DD/MM/YYYY HH:mm')}}</p>
            {{include tmpl="#tpl-post-set_expiry"/}}
        </div>
    {{/if}}

    {{if capabilities.can_send_private_message}}
        <div class="widget">
            {{if capabilities.can_set_protocol}}
                <a href="#" class="pull-right action-trigger" data-reverse="{{:~sensorTranslate('Cancel')}}">{{:~sensorTranslate('Edit')}}</a>
            {{/if}}
            <strong class="widget-title">{{:~sensorTranslate('Protocollo')}}</strong>
            <p class="widget-content">
                {{if protocols && (protocols[0] || protocols[1] || protocols[2])}}
                    {{if protocols[0]}}<strong>{{:~sensorTranslate('Protocollo1')}}</strong>: {{:protocols[0]}}<br />{{/if}}
                    {{if protocols[1]}}<strong>{{:~sensorTranslate('Protocollo2')}}</strong>: {{:protocols[1]}}<br />{{/if}}
                {{else}}<em class="text-muted">{{:~sensorTranslate('Undefined')}}</em>
                {{/if}}
            </p>
            {{include tmpl="#tpl-post-set_protocol"/}}
        </div>
    {{/if}}

    <div class="widget{{if capabilities.can_send_private_message}} bg-danger" style="padding:10px{{/if}}">
        {{if capabilities.can_moderate && privacy.identifier == 'public'}}
            <a href="#" class="pull-right action-trigger" data-reverse="{{:~sensorTranslate('Cancel')}}">{{:~sensorTranslate('Edit')}}</a>
        {{/if}}
        <strong class="widget-title">{{:~sensorTranslate('Visibility')}}</strong>
        <p class="widget-content">{{if privacy.identifier == 'public' && moderation.identifier != 'waiting'}}<i class="fa fa-globe"></i> {{:~sensorTranslate('public', 'privacy')}}{{else}}<i class="fa fa-lock"></i> {{:~sensorTranslate('private', 'privacy')}}{{/if}}</p>
        {{if capabilities.can_moderate && privacy.identifier == 'public'}}
            <div class="form-group hide" data-action-wrapper>
                <input type="hidden" data-value="status" value="{{if moderation.identifier == 'waiting'}}accepted{{else}}waiting{{/if}}" />
                <input class="btn btn-sm btn-default" data-parameters="status" type="submit" data-action="moderate" value="{{if moderation.identifier == 'waiting'}}{{:~sensorTranslate('Make public')}}{{else}}{{:~sensorTranslate('Make private')}}{{/if}}" />
            </div>
        {{/if}}
    </div>


    <div class="widget">
        {{if capabilities.can_add_attachment}}
            <a href="#" class="pull-right action-trigger" data-reverse="{{:~sensorTranslate('Cancel')}}">{{:~sensorTranslate('Edit')}}</a>
        {{/if}}
        <strong class="widget-title">{{:~sensorTranslate('Attachments')}}</strong>
        <div class="widget-content" data-action-wrapper>
            {{if attachments.length > 0}}
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
                        <i class="fa fa-trash"></i> {{:~sensorTranslate('Remove selected attachmentes')}}
                    </button>
                {{/if}}
            {{else}}
            <em class="text-muted">{{:~sensorTranslate('No attachments')}}</em>
            {{/if}}
        </div>
        {{if capabilities.can_add_attachment}}
            <form class="form-group hide" data-upload="add_attachment">
                <div class="clearfix upload-button-container">
                    <span class="btn btn-sm btn-default fileinput-button">
                        <i class="fa fa-plus"></i>
                        <span>{{:~sensorTranslate('Add attachment')}}</span>
                        <input class="upload" name="files" type="file" />
                    </span>
                </div>
                <div class="clearfix upload-button-spinner" style="display: none">
                    <i class="fa fa-cog fa-spin fa-3x"></i>
                </div>
            </form>
        {{/if}}
    </div>

    {{if status.identifier == 'deployed'}}
    <div class="widget">
        <strong class="widget-title">{{:~sensorTranslate('Informazioni Patto')}}</strong>
        <ul class="list-unstyled widget-content">
            <li><b>{{:~sensorTranslate('Data inizio validità')}}:</b> {{:~formatDate(deployInfo.validFrom, 'DD/MM/YYYY')}}</li>
            <li><b>{{:~sensorTranslate('Data fine validità')}}:</b> {{:~formatDate(deployInfo.validTo, 'DD/MM/YYYY')}}</li>
            <li><b>{{:~sensorTranslate('Numero determina')}}:</b> {{:deployInfo.documentNumber}}</li>
        </ul>
    </div>
    {{/if}}

    {{if capabilities.can_auto_assign || capabilities.can_respond}}
    <div class="widget">
        <strong class="widget-title">{{:~sensorTranslate('Actions')}}</strong>
        <div class="row">
            <div class="text-right col-md-10 col-md-offset-2">
                
                {{include tmpl="#tpl-post-autoassign"/}}
                
                {{!--include tmpl="#tpl-post-fix"--}}
                
                {{!--include tmpl="#tpl-post-force_fix" --}}
                
                {{include tmpl="#tpl-post-close"/}}

                {{!--include tmpl="#tpl-post-reopen"--}}
            </div>
        </div>
    </div>
    {{/if}}

    {{if comments.length > 0 || responses.length > 0 || timelineItems.length > 0 || privateMessages.length > 0 || audits.length > 0}}
    <div class="widget">
        <strong class="widget-title">{{:~sensorTranslate('Show')}}</strong>
        <div>
            {{if comments.length > 0}}<a href="#" class="message-visibility label label-default" style="padding: 5px 7px 6px;margin: 0 1px 5px 0;display: inline-block;" data-type="public">{{:~sensorTranslate('Comments')}}</a>{{/if}}
            {{if responses.length > 0}}<a href="#" class="message-visibility label label-default" style="padding: 5px 7px 6px;margin: 0 1px 5px 0;display: inline-block;" data-type="response">{{:~sensorTranslate('Responses')}}</a>{{/if}}
            {{if timelineItems.length > 0}}<a href="#" class="message-visibility label label-default" style="padding: 5px 7px 6px;margin: 0 1px 5px 0;display: inline-block;" data-type="system">{{:~sensorTranslate('Timeline')}}</a>{{/if}}
            {{if privateMessages.length > 0}}<a href="#" class="message-visibility label label-default" style="padding: 5px 7px 6px;margin: 0 1px 5px 0;display: inline-block;" data-type="private">{{:~sensorTranslate('Notes')}}</a>{{/if}}
            {{if audits && audits.length > 0}}<a href="#" class="message-visibility label label-simple" style="padding: 5px 7px 6px;margin: 0 1px 5px 0;display: inline-block;" data-type="audit">{{:~sensorTranslate('Audit')}}</a>{{/if}}
        </div>
    </div>
    {{/if}}

    {{if settings.MinimumIntervalFromLastPrivateMessageToFix > 0}}
    <div id="addNoteThenFix" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="clearfix" data-action-wrapper>
                        <label for="noteForFix">{{:~sensorTranslate('Add a note on processing to facilitate the drafting of the official response')}}</label>
                        <textarea id="noteForFix" data-value="text" name="noteForFix" class="form-control" rows="4" required="required"></textarea>
                        <input data-value="is_response_proposal" type="hidden" value="1" />
                        {{for approvers}}<input data-value="participant_ids" type="hidden" value="{{:id}}" />{{/for}}
                        <input class="btn send btn-bold pull-right"
                               type="submit"
                               style="margin-top:10px"
                               data-actions="send_private_message,fix"
                               data-parameters="text,participant_ids,is_response_proposal"
                               value="{{:~sensorTranslate('Add note and set as fixed')}}" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{/if}}

    {{if settings.AddPrivateMessageBeforeReassign}}
    <div id="addNoteThenAssign" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="clearfix" data-action-wrapper>
                        <div data-single-action-wrapper="send_private_message" data-parameters="text,participant_ids">
                            <label for="noteForAssign">{{:~sensorTranslate('Specify the reason for the reassignment')}}</label>
                            <textarea id="noteForAssign" data-value="text" name="noteForAssign" class="form-control" rows="4" required="required"></textarea>
                            {{for approvers}}<input data-value="participant_ids" type="hidden" value="{{:id}}" />{{/for}}
                        </div>
                        <div data-single-action-wrapper="assign" data-parameters="group_ids,participant_ids">
                            <input data-value="group_ids" type="hidden" value="" />
                            <input data-value="participant_ids" type="hidden" value="" />
                        </div>
                        <input class="btn send btn-bold pull-right"
                               type="submit"
                               style="margin-top:10px"
                               data-actions="send_private_message,assign"
                               value="{{:~sensorTranslate('Add note and reassign')}}" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{/if}}

</script>

<script id="tpl-post-add_area" type="text/x-jsrender">
{{if capabilities.can_add_area}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group d-flex">
            <select data-value="area_id" data-placeholder="{{:~sensorTranslate('Select area')}}" class="select form-control">
                <option></option>
                {{for areasTree.children}}
                    {{include tmpl="#tpl-tree-option"/}}
                {{/for}}
            </select>
            <span class="input-group-btn">
                <input data-action="add_area" data-parameters="area_id" class="btn btn-sm btn-default" type="submit" value="{{:~sensorTranslate('Select')}}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-category-predictions" type="text/x-jsrender">
<p><em>Suggerimenti</em></p>
<div class="list-group category-predictions">
{{for predictions}}
    <a href="#" class="list-group-item" data-category_prediction="{{:id}}" style="padding:7px 10px">
        {{if score}}<i class="fa fa pull-right {{if score > 75}}text-success fa-circle{{else score > 50}}text-success fa-circle-o{{else}}text-danger fa-circle-o{{/if}}" style="margin-top: 4px;" title="{{:score}}%"></i>{{/if}}
        <b>{{:name}}</b>
    </a>
    {{if children.length}}
    {{for children}}
        <a href="#" class="list-group-item" data-category_prediction="{{:id}}" style="padding: 7px 10px 7px 25px;">
            {{if score}}<i class="fa fa pull-right {{if score > 75}}text-success fa-circle{{else score > 50}}text-success fa-circle-o{{else}}text-danger fa-circle-o{{/if}}" style="margin-top: 4px;" title="{{:score}}%"></i>{{/if}}
            {{:name}}
        </a>
    {{/for}}
    {{/if}}
{{/for}}
</div>
</script>

<script id="tpl-post-add_category" type="text/x-jsrender">
{{if capabilities.can_add_category}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group d-flex">
            <select data-value="category_id" data-placeholder="{{:~sensorTranslate('Select category')}}" class="select form-control">
                <option></option>
                {{for categoriesTree.children}}
                    {{include tmpl="#tpl-tree-option"/}}
                {{/for}}
            </select>
            <span class="input-group-btn">
                <input data-action="add_category" data-parameters="category_id" class="btn btn-sm btn-default" type="submit" value="{{:~sensorTranslate('Select')}}" />
            </span>
        </div>
        {{if settings.HasCategoryPredictor && capabilities.can_add_category}}
        <div class="predictor hide" style="margin-top:10px">
            <div class="text-center spinner" style="margin-top:20px">
                <i class="fa fa-magic fa-2x faa-passing animated"></i>
            </div>
            <div class="predictions" style="font-size: 0.875em;"></div>
        </div>
        {{/if}}
    </div>
{{/if}}
</script>

<script id="tpl-post-set_type" type="text/x-jsrender">
{{if capabilities.can_set_type}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group d-flex">
            <select data-value="type" data-placeholder="{{:~sensorTranslate('Select type')}}" class="select form-control">
                {/literal}{foreach sensor_types() as $type}
                <option value="{$type.identifier|wash()}"{literal}{{if type.identifier == '{/literal}{$type.identifier|wash()}{literal}'}} selected="selected"{{/if}}{/literal}>{$type.name|wash()}</option>
                {/foreach}{literal}
            </select>
            <span class="input-group-btn">
                <input data-action="set_type" data-parameters="type" class="btn btn-sm btn-default" type="submit" value="{{:~sensorTranslate('Select')}}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-tree-option" type="text/x-jsrender">
<option value="{{:id}}" {{if !is_enabled}}disabled="disabled"{{/if}} style="padding-left:calc(10px*{{:level}});{{if level == 0 && type != 'sensor_group' && type != 'sensor_operator'}}font-weight: bold;{{/if}}">
   {{:name}} {{if type == 'sensor_category' && group}}<small>({{:group}})</small>{{/if}}
</option>
{{for children}}
    {{include tmpl="#tpl-tree-option"/}}
{{/for}}
</script>

<script id="tpl-post-set_expiry" type="text/x-jsrender">
{{if capabilities.can_set_expiry}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group">
            <input type="number" data-value="expiry_days" class="form-control" value="{{:expirationInfo.days}}" style="height: 30px;"/>
            <span class="input-group-btn">
                <input data-action="set_expiry" data-parameters="expiry_days" class="btn btn-sm btn-default" type="submit" value="{{:~sensorTranslate('Set days')}}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-post-add_approver" type="text/x-jsrender">
{{if capabilities.can_add_approver}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group d-flex">
            <select data-value="participant_ids" data-placeholder="{{:~sensorTranslate('Select group')}}" class="select form-control">
                <option></option>
                {{if groupsTree.children.length > 0}}
                {{for groupsTree.children}}
                    {{include tmpl="#tpl-tree-option"/}}
                {{/for}}
                {{/if}}
                {/literal}
                    {def $default_approvers = sensor_default_approvers()}
                    {if count($default_approvers)}
                    <option disabled>────────────</option>
                    {foreach $default_approvers  as $default_approver}
                    <option value="{$default_approver.id}">{$default_approver.name|wash()}</option>
                    {/foreach}
                    {/if}
                {literal}
            </select>
            <span class="input-group-btn">
                <input class="btn btn-sm btn-default" type="submit" data-action="add_approver" data-parameters="participant_ids" value="{{:~sensorTranslate('Select')}}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-post-assign" type="text/x-jsrender">
{{if capabilities.can_assign}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group d-flex">
            {{if groupsTree.children.length > 0}}
            <select id="group-assign" data-current="{{:currentOwnerGroupId}}" data-value="group_ids" data-placeholder="{{:~sensorTranslate('Group')}}" class="select form-control reset-on-close">
                <option></option>
            </select>
            {{/if}}
            <select id="user-assign" data-current="{{:currentOwnerUserId}}" data-value="participant_ids" data-placeholder="{{:~sensorTranslate('Operator')}}" class="select form-control reset-on-close">
                <option></option>
            </select>
            <span class="input-group-btn">
                <input class="btn btn-sm btn-default"
                       type="submit"
                       data-action="{{if settings.AddPrivateMessageBeforeReassign && owners.length > 0}}checkNoteAndAssign{{else}}assign{{/if}}"
                       data-parameters="participant_ids,group_ids"
                       value="{{if owners.length == 0}}{{:~sensorTranslate('Assign')}}{{else}}{{:~sensorTranslate('Reassign')}}{{/if}}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-post-add_observer" type="text/x-jsrender">
{{if capabilities.can_add_observer}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group d-flex">
            <select data-value="participant_ids" data-placeholder="{{:~sensorTranslate('Select operator')}}" class="select form-control">
                <option></option>
                {{for operatorsTree.children}}
                    {{include tmpl="#tpl-tree-option"/}}
                {{/for}}
            </select>
            <span class="input-group-btn">
                <input class="btn btn-sm btn-default" type="submit" data-action="add_observer" data-parameters="participant_ids" value="{{:~sensorTranslate('Select')}}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-post-autoassign" type="text/x-jsrender">
{{if capabilities.can_auto_assign}}
    <div class="form-group" data-action-wrapper>
        <input type="hidden" data-value="participant_ids" value="{{:currentUserId}}" />
        <input class="btn {{if workflowStatus.identifier == 'assigned'}}btn-danger{{else}}btn-default{{/if}} btn-md btn-block" type="submit" data-action="auto_assign" data-parameters="participant_ids"
               value="{{:~sensorTranslate('Take charge')}}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-fix" type="text/x-jsrender">
{{if capabilities.can_fix}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-md btn-default btn-block" type="submit" data-action="{{if settings.MinimumIntervalFromLastPrivateMessageToFix > 0}}checkNoteAndFix{{else}}fix{{/if}}" value="{{:~sensorTranslate('Set as fixed')}}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-force_fix" type="text/x-jsrender">
{{if capabilities.can_force_fix}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-md btn-default btn-block" type="submit" data-action="force_fix" value="{{:~sensorTranslate('Force as fixed')}}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-close" type="text/x-jsrender">
{{if capabilities.can_close}}
{{!--
    {{if status.identifier !== 'close'}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-md btn-default btn-block"
               type="submit"
               {{if responses.length == 0}}data-confirmation="{{:~sensorTranslate('There are no official replies entered: are you sure you want to close the report?')}}"{{/if}}
               data-action="close"
               value="{{:~sensorTranslate('Reject')}}" />
    </div>
    {{/if}}
    {{if status.identifier !== 'approved' && categories.length > 0 && protocols[0]}}
    <div class="form-group" data-action-wrapper>
        <input type="hidden" data-value="label" value="sensor.approved" />
        <input class="btn btn-md btn-default btn-block"
               type="submit"
               {{if responses.length == 0}}data-confirmation="{{:~sensorTranslate('There are no official replies entered: are you sure you want to close the report?')}}"{{/if}}
               data-action="close"
               data-parameters="label"
               value="{{:~sensorTranslate('Approve')}}" />
    </div>
    {{/if}}
--}}
    {{if (status.identifier == 'approved' || status.identifier == 'deployed') && capabilities.can_respond}}
    <div class="form-group">
        <a href="#" data-deploy="{{:id}}" type="button" class="btn btn-md btn-block btn-info">{{:~sensorTranslate('Imposta patto')}}</a>
    </div>
    <div id="modal-deploy" class="modal fade text-left">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-body">
            <div class="clearfix">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div id="form-deploy" class="clearfix"></div>
          </div>
        </div>
      </div>
    </div>
    {{/if}}
{{/if}}
</script>
<script id="tpl-post-reopen" type="text/x-jsrender">
{{if capabilities.can_reopen}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-md btn-default btn-block"
               type="submit"
               data-action="reopen"
               value="{{:~sensorTranslate('Reopen issue')}}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-set_protocol" type="text/x-jsrender">
{{if capabilities.can_set_protocol}}
    <div class="form-group hide" data-action-wrapper>
        <ul class="list-unstyled">
            <li>
                <em class="text-muted">{{:~sensorTranslate('Inserire anno e numero')}}</em>
            </li>
            <li>
                <label for="protocol1">{{:~sensorTranslate('Protocollo1')}}</label>
                <input type="text" data-value="protocol1" id="protocol1" class="form-control protocol-mask" value="{{if protocols && protocols[0]}}{{:protocols[0]}}{{/if}}" style="height: 30px;"/>
            </li>
            <li>
                <label for="protocol2">{{:~sensorTranslate('Protocollo2')}}</label>
                <input type="text" data-value="protocol2" id="protocol2" class="form-control protocol-mask" value="{{if protocols && protocols[1]}}{{:protocols[1]}}{{/if}}" style="height: 30px;"/>
            </li>
            <li class="clearfix">
                <input style="margin-top: 5px;" data-action="set_protocol" data-parameters="protocol1,protocol2" class="btn btn-sm btn-default pull-right" type="submit" value="{{:~sensorTranslate('Select')}}" />
            </li>
        </ul>
    </div>
{{/if}}
</script>
<script id="tpl-subscriber" type="text/x-jsrender">
    <li>
        <img src="/sensor/avatar/{{:userId}}" class="img-circle" style="width: 20px; height: 20px; object-fit: cover; margin-right:5px" />
        {{:userName}}
    </li>
</script>
<script id="tpl-no_subscriber" type="text/x-jsrender">
    <li>
        <em class="text-muted">{{:~sensorTranslate('Undefined')}}</em>
    </li>
</script>
{/literal}