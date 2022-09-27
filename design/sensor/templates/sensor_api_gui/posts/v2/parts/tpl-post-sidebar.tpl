{literal}
<script id="tpl-post-sidebar" type="text/x-jsrender">

    <div class="widget">
        {{if capabilities.can_add_approver}}
            <a href="#" class="pull-right action-trigger">Modifica</a>
        {{/if}}
        <strong class="widget-title">Riferimento</strong>
        <ul class="list-unstyled widget-content">
        {{for approvers ~accessPath=accessPath}}
            <li>
                <img src="{{:~accessPath}}/sensor/avatar/{{:id}}" class="img-circle" style="width: 20px; height: 20px; object-fit: cover; margin-right:5px" />
                {{:name}}
            </li>
        {{else}}
            <li>
                <em class="text-muted">Non definito</em>
            </li>
        {{/for}}
        </ul>
        {{include tmpl="#tpl-post-add_approver"/}}
    </div>

    {{if capabilities.can_send_private_message || !settings.HideTimelineDetails}}
        {{if capabilities.can_assign || owners.length}}
        <div class="widget">
            {{if capabilities.can_assign}}
                <a href="#" class="pull-right action-trigger">Modifica</a>
            {{/if}}
            <strong class="widget-title">Incaricato</strong>
            <ul class="list-unstyled widget-content">
            {{for owners ~accessPath=accessPath}}
                <li>
                    <img src="{{:~accessPath}}/sensor/avatar/{{:id}}" class="img-circle" style="width: 20px; height: 20px; object-fit: cover; margin-right:5px" />
                    {{:name}}
                </li>
            {{else}}
                <li>
                    <em class="text-muted">Non definito</em>
                </li>
            {{/for}}
            </ul>
            {{include tmpl="#tpl-post-assign"/}}
        </div>
        {{/if}}

        {{if capabilities.can_add_observer || observers.length}}
        <div class="widget">
            {{if capabilities.can_add_observer}}
                <a href="#" class="pull-right action-trigger">Modifica</a>
            {{/if}}
            <strong class="widget-title">Osservatori</strong>
            <ul class="list-unstyled widget-content">
            {{for observers ~capabilities=capabilities ~accessPath=accessPath}}
                <li data-action-wrapper>
                    <img src="{{:~accessPath}}/sensor/avatar/{{:id}}" class="img-circle" style="width: 20px; height: 20px; object-fit: cover; margin-right:5px" />
                    {{:name}}
                    {{if ~capabilities.can_remove_observer}}
                    <a href="#"
                       data-action="remove_observer" data-parameters="participant_id"
                       title="{/literal}{'Rimuovi osservatore'|i18n('sensor/messages')}{literal}"><i class="fa fa-times"></i></a>
                    <input type="hidden" value="{{:id}}" data-value="participant_id" />
                    {{/if}}
                </li>
            {{else}}
                <li>
                    <em class="text-muted">Non definito</em>
                </li>
            {{/for}}
            </ul>
            {{include tmpl="#tpl-post-add_observer"/}}
        </div>
        {{/if}}
    {{/if}}

    <div class="widget">
        {{if capabilities.can_add_area}}
            <a href="#" class="pull-right action-trigger">Modifica</a>
        {{/if}}
        <strong class="widget-title">{{for sensorPost.fields}}{{if identifier == 'area'}}{{:~i18n(name)}}{{/if}}{{/for}}</strong>
        <p class="widget-content">{{for areas}}{{:name}}{{else}}<em class="text-muted">Non definito</em>{{/for}}</p>
        {{include tmpl="#tpl-post-add_area"/}}
    </div>

    <div class="widget">
        {{if capabilities.can_add_category}}
            <a href="#" class="pull-right action-trigger">Modifica</a>
        {{/if}}
        <strong class="widget-title">{{for sensorPost.fields}}{{if identifier == 'category'}}{{:~i18n(name)}}{{/if}}{{/for}}</strong>
        <p class="widget-content">{{for categories}}{{:name}}{{else}}<em class="text-muted">Non definito</em>{{/for}}</p>
        {{include tmpl="#tpl-post-add_category"/}}
    </div>

    {{if capabilities.can_send_private_message}}
        <div class="widget">
            {{if capabilities.can_set_expiry}}
                <a href="#" class="pull-right action-trigger">Modifica</a>
            {{/if}}
            <strong class="widget-title">{/literal}{'Scadenza'|i18n('sensor/post')}{literal}</strong>
            <p class="widget-content">{{:~formatDate(expirationInfo.expirationDateTime, 'DD/MM/YYYY HH:mm')}}</p>
            {{include tmpl="#tpl-post-set_expiry"/}}
        </div>
    {{/if}}

    <div class="widget">
        {{if capabilities.can_moderate && privacy.identifier == 'public'}}
            <a href="#" class="pull-right action-trigger">Modifica</a>
        {{/if}}
        <strong class="widget-title">{/literal}{'Visibilità'|i18n('sensor/post')}{literal}</strong>
        <p class="widget-content">{{if privacy.identifier == 'public' && moderation.identifier != 'waiting'}}Pubblico{{else}}<i class="fa fa-lock"></i> Privato{{/if}}</p>
        {{if capabilities.can_moderate && privacy.identifier == 'public'}}
            <div class="form-group hide" data-action-wrapper>
                <input type="hidden" data-value="status" value="{{if moderation.identifier == 'waiting'}}accepted{{else}}waiting{{/if}}" />
                <input class="btn btn-sm btn-default" data-parameters="status" type="submit" data-action="moderate" value="{{if moderation.identifier == 'waiting'}}Rendi pubblico{{else}}Rendi privato{{/if}}" />
            </div>
        {{/if}}
    </div>


    <div class="widget">
        {{if capabilities.can_add_attachment}}
            <a href="#" class="pull-right action-trigger">Modifica</a>
        {{/if}}
        <strong class="widget-title">{/literal}{'Allegati'|i18n('sensor/messages')}{literal}</strong>
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
                        <i class="fa fa-trash"></i> {/literal}{'Rimuovi file selezionati'|i18n('sensor/messages')}{literal}
                    </button>
                {{/if}}
            {{else}}
            <em class="text-muted">Nessun allegato</em>
            {{/if}}
        </div>
        {{if capabilities.can_add_attachment}}
            <form class="form-group hide" data-upload="add_attachment">
                <div class="clearfix upload-button-container">
                    <span class="btn btn-sm btn-default fileinput-button">
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

    {{if capabilities.can_auto_assign || capabilities.can_fix || capabilities.can_force_fix || capabilities.can_close || capabilities.can_reopen}}
    <div class="widget">
        <strong class="widget-title">{/literal}{'Azioni'|i18n('sensor/post')}{literal}</strong>
        <div class="row">
            <div class="text-right col-md-10 col-md-offset-2">
                
                {{include tmpl="#tpl-post-autoassign"/}}
                
                {{include tmpl="#tpl-post-fix"/}}
                
                {{include tmpl="#tpl-post-force_fix"/}}
                
                {{include tmpl="#tpl-post-close"/}}

                {{include tmpl="#tpl-post-reopen"/}}
            </div>
        </div>
    </div>
    {{/if}}

    <div class="widget">
        <strong class="widget-title">{/literal}{'Visualizza'|i18n('sensor/post')}{literal}</strong>
        <div>
            {{if comments.length > 0}}<a href="#" class="message-visibility label label-default" data-type="public">Commenti</a>{{/if}}
            {{if responses.length > 0}}<a href="#" class="message-visibility label label-default" data-type="response">Risposte</a>{{/if}}
            {{if timelineItems.length > 0}}<a href="#" class="message-visibility label label-default" data-type="system">Cronologia</a>{{/if}}
            {{if privateMessages.length > 0}}<a href="#" class="message-visibility label label-default" data-type="private">Note</a>{{/if}}
        </div>
    </div>

</script>

<script id="tpl-post-add_area" type="text/x-jsrender">
{{if capabilities.can_add_area}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group">
            <select data-value="area_id" data-placeholder="{/literal}{'Seleziona Zona'|i18n('sensor/post')}{literal}" class="select form-control">
                <option></option>
                {{for areasTree.children}}
                    {{include tmpl="#tpl-tree-option"/}}
                {{/for}}
            </select>
            <span class="input-group-btn">
                <input data-action="add_area" data-parameters="area_id" class="btn btn-sm btn-default" type="submit" value="{/literal}{'Associa'|i18n('sensor/post')}{literal}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-post-add_category" type="text/x-jsrender">
{{if capabilities.can_add_category}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group">
            <select data-value="category_id" data-placeholder="{/literal}{'Seleziona categoria'|i18n('sensor/post')}{literal}" class="select form-control">
                <option></option>
                {{for categoriesTree.children}}
                    {{include tmpl="#tpl-tree-option"/}}
                {{/for}}
            </select>
            <span class="input-group-btn">
                <input data-action="add_category" data-parameters="category_id" class="btn btn-sm btn-default" type="submit" value="{/literal}{'Associa'|i18n('sensor/post')}{literal}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-tree-option" type="text/x-jsrender">
<option value="{{:id}}" style="padding-left:calc(10px*{{:level}});{{if level == 0 && type != 'sensor_group' && type != 'sensor_operator'}}font-weight: bold;{{/if}}">
   {{:name}} {{if (type == 'sensor_category' || type == 'sensor_operator') && group}}<small>({{:group}})</small>{{/if}}
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
                <input data-action="set_expiry" data-parameters="expiry_days" class="btn btn-sm btn-default" type="submit" value="{/literal}{'Imposta giorni'|i18n('sensor/post')}{literal}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-post-add_approver" type="text/x-jsrender">
{{if capabilities.can_add_approver}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group">
            <select data-value="participant_ids" data-placeholder="{/literal}{'Seleziona gruppo'|i18n('sensor/post')}{literal}" class="select form-control">
                <option></option>
                {{for groupsTree.children}}
                    {{include tmpl="#tpl-tree-option"/}}
                {{/for}}
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
                <input class="btn btn-sm btn-default" type="submit" data-action="add_approver" data-parameters="participant_ids" value="{/literal}{'Imposta'|i18n('sensor/post')}{literal}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-post-assign" type="text/x-jsrender">
{{if capabilities.can_assign}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group">
            <select data-value="participant_ids" data-placeholder="{/literal}{'Seleziona operatore'|i18n('sensor/post')}{literal}" class="select form-control">
                <option></option>
                {{for operatorsTree.children}}
                    {{include tmpl="#tpl-tree-option"/}}
                {{/for}}
            </select>
            <span class="input-group-btn">
                <input class="btn btn-sm btn-default" type="submit" data-action="assign" data-parameters="participant_ids" value="{{if owners.length == 0}}{/literal}{'Assegna'|i18n('sensor/post')}{literal}{{else}}{/literal}{'Riassegna'|i18n('sensor/post')}{/literal}{{/if}}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-post-add_observer" type="text/x-jsrender">
{{if capabilities.can_add_observer}}
    <div class="form-group hide" data-action-wrapper>
        <div class="input-group">
            <select data-value="participant_ids" data-placeholder="{/literal}{'Seleziona operatore'|i18n('sensor/post')}{literal}" class="select form-control">
                <option></option>
                {{for operatorsTree.children}}
                    {{include tmpl="#tpl-tree-option"/}}
                {{/for}}
            </select>
            <span class="input-group-btn">
                <input class="btn btn-sm btn-default" type="submit" data-action="add_observer" data-parameters="participant_ids" value="{/literal}{'Aggiungi'|i18n('sensor/post')}{literal}" />
            </span>
        </div>
    </div>
{{/if}}
</script>

<script id="tpl-post-autoassign" type="text/x-jsrender">
{{if capabilities.can_auto_assign}}
    <div class="form-group" data-action-wrapper>
        <input type="hidden" data-value="participant_ids" value="{{:currentUserId}}" />
        <input class="btn {{if workflowStatus.identifier == 'assigned'}}btn-danger{{else}}btn-default{{/if}} btn-md btn-block" type="submit" data-action="assign" data-parameters="participant_ids"
               value="{/literal}{'Prendi in carico'|i18n('sensor/post')}{literal}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-fix" type="text/x-jsrender">
{{if capabilities.can_fix}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-md btn-default btn-block" type="submit" data-action="fix" value="{/literal}{'Intervento terminato'|i18n('sensor/post')}{literal}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-force_fix" type="text/x-jsrender">
{{if capabilities.can_force_fix}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-md btn-default btn-block" type="submit" data-action="force_fix" value="{/literal}{'Forza intervento terminato'|i18n('sensor/post')}{literal}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-close" type="text/x-jsrender">
{{if capabilities.can_close}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-md btn-default btn-block"
               type="submit"
               {{if responses.length == 0}}data-confirmation="{/literal}{'Non ci sono risposte ufficiali inserite: sei sicuro di voler chiudere la segnalazione?'|i18n( 'sensor/messages' )|wash(javascript)}{literal}"{{/if}}
               data-action="close"
               value="{/literal}{'Chiudi'|i18n('sensor/post')}{literal}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-reopen" type="text/x-jsrender">
{{if capabilities.can_reopen}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-md btn-default btn-block"
               type="submit"
               data-action="reopen"
               value="{/literal}{'Riapri'|i18n('sensor/post')}{literal}" />
    </div>
{{/if}}
</script>
{/literal}