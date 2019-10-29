{literal}
<script id="tpl-post-actions" type="text/x-jsrender">
    {{if capabilities.can_add_area || capabilities.can_add_category || capabilities.can_set_expiry || capabilities.can_assign || capabilities.can_add_observer
         || capabilities.can_fix || capabilities.can_force_fix || capabilities.can_close || capabilities.can_reopen || capabilities.can_change_privacy
         || capabilities.can_moderate || privateMessages.length > 0 || capabilities.can_send_private_message}}
         <aside class="widget well well-sm" id="current-post-action">
             {{include tmpl="#tpl-post-add_area"/}}
             {{include tmpl="#tpl-post-add_category"/}}
             {{include tmpl="#tpl-post-set_expiry"/}}

             {{if capabilities.can_assign || capabilities.can_add_observer || capabilities.can_fix || capabilities.can_force_fix || capabilities.can_close || capabilities.can_reopen || capabilities.can_change_privacy || capabilities.can_moderate}}
                <strong>{/literal}{'Azioni'|i18n('sensor/post')}{literal}</strong>
             {{/if}}

             {{include tmpl="#tpl-post-add_approver"/}}
             {{include tmpl="#tpl-post-assign"/}}
             {{include tmpl="#tpl-post-add_observer"/}}
             {{include tmpl="#tpl-post-autoassign"/}}
             {{include tmpl="#tpl-post-fix"/}}
             {{include tmpl="#tpl-post-force_fix"/}}
             {{include tmpl="#tpl-post-close"/}}
             {{include tmpl="#tpl-post-reopen"/}}
             {{include tmpl="#tpl-post-change_privacy"/}}
             {{include tmpl="#tpl-post-moderate"/}}

             {{include tmpl="#tpl-post-private-messages"/}}

         </aside>
    {{/if}}
</script>
<script id="tpl-post-add_area" type="text/x-jsrender">
{{if capabilities.can_add_area}}
    <strong>{{for sensorPost.fields}}{{if identifier == 'area'}}{{:~i18n(name)}}{{/if}}{{/for}}</strong>
    <div class="form-group" data-action-wrapper>
        <div class="row">
            <div class="col-xs-8">
                <select data-value="area_id" data-placeholder="{/literal}{'Seleziona Quartiere/Zona'|i18n('sensor/post')}{literal}" class="select form-control">
                    <option></option>
                    {{for areasTree.children}}
                        {{include tmpl="#tpl-tree-option"/}}
                    {{/for}}
                </select>
            </div>
            <div class="col-xs-4">
                <input data-action="add_area" data-parameters="area_id" class="btn btn-info btn-block" type="submit" value="{/literal}{'Associa'|i18n('sensor/post')}{literal}" />
            </div>
        </div>
    </div>
{{/if}}
</script>
<script id="tpl-post-add_category" type="text/x-jsrender">
{{if capabilities.can_add_category}}
    <strong>{{for sensorPost.fields}}{{if identifier == 'category'}}{{:~i18n(name)}}{{/if}}{{/for}}</strong>
    <div class="form-group" data-action-wrapper>
        <div class="row">
            <div class="col-xs-8">
                <select data-value="category_id" data-placeholder="{/literal}{'Seleziona area tematica'|i18n('sensor/post')}{literal}" class="select form-control">
                    <option></option>
                    {{for categoriesTree.children}}
                        {{include tmpl="#tpl-tree-option"/}}
                    {{/for}}
                </select>
            </div>
            <div class="col-xs-4">
                <input data-action="add_category" data-parameters="category_id,assign_to_operator,assign_to_group" class="btn btn-info btn-block" type="submit" value="{/literal}{'Associa'|i18n('sensor/post')}{literal}" />
            </div>
            <div class="col-xs-12">
                {{if settings.CategoryAutomaticAssign}}
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" data-value="assign_to_operator" value="1"> {/literal}{"Assegna agli operatori della categoria selezionata"|i18n('sensor/post')}{literal}
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" data-value="assign_to_group" value="1"> {/literal}{"Aggiungi come approvatori i gruppi della categoria selezionata"|i18n('sensor/post')}{literal}
                        </label>
                    </div>
                {{/if}}
            </div>
        </div>
    </div>
{{/if}}
</script>
<script id="tpl-tree-option" type="text/x-jsrender">
<option value="{{:id}}" style="padding-left:calc(10px*{{:level}});{{if level == 0}}font-weight: bold;{{/if}}">{{:name}}</option>
{{for children}}
    {{include tmpl="#tpl-tree-option"/}}
{{/for}}
</script>
<script id="tpl-post-set_expiry" type="text/x-jsrender">
{{if capabilities.can_set_expiry}}
    <strong>{/literal}{'Scadenza'|i18n('sensor/post')} <small>{'in giorni'|i18n('sensor/post')}{literal}</small></strong>
    <div class="form-group" data-action-wrapper>
        <div class="row">
            <div class="col-xs-8">
                <input type="number" data-value="expiry_days" class="form-control" value="{{:expirationInfo.days}}" />
            </div>
            <div class="col-xs-4">
                <input data-action="set_expiry" data-parameters="expiry_days" class="btn btn-info btn-block" type="submit" value="{/literal}{'Imposta'|i18n('sensor/post')}{literal}" />
            </div>
        </div>
    </div>
{{/if}}
</script>
<script id="tpl-post-add_approver" type="text/x-jsrender">
{{if capabilities.can_add_approver}}
    <div class="form-group" data-action-wrapper>
        <div class="row">
            <div class="col-xs-8">
                <select data-value="participant_ids" data-placeholder="{/literal}{'Seleziona gruppo'|i18n('sensor/post')}{literal}" class="form-control remote-select" data-remote="groups">
                    <option></option>
                </select>
            </div>
            <div class="col-xs-4">
                <input class="btn btn-info btn-block" type="submit" data-action="add_approver" data-parameters="participant_ids" value="{/literal}{'Coinvolgi'|i18n('sensor/post')}{literal}" />
            </div>
        </div>
    </div>
{{/if}}
</script>
<script id="tpl-post-assign" type="text/x-jsrender">
{{if capabilities.can_assign}}
    <div class="form-group" data-action-wrapper>
        <div class="row">
            <div class="col-xs-8">
                <select data-value="participant_ids" data-placeholder="{/literal}{'Seleziona operatore'|i18n('sensor/post')}{literal}" class="form-control remote-select" data-remote="operators">
                    <option></option>
                </select>
            </div>
            <div class="col-xs-4">
                <input class="btn btn-info btn-block" type="submit" data-action="assign" data-parameters="participant_ids" value="{{if owners.length == 0}}{/literal}{'Assegna'|i18n('sensor/post')}{literal}{{else}}{/literal}{'Riassegna'|i18n('sensor/post')}{/literal}{{/if}}" />
            </div>
        </div>
    </div>
{{/if}}
</script>
<script id="tpl-post-add_observer" type="text/x-jsrender">
{{if capabilities.can_add_observer}}
    <div class="form-group" data-action-wrapper>
        <div class="row">
            <div class="col-xs-8">
                <select data-value="participant_ids" data-placeholder="{/literal}{'Seleziona operatore'|i18n('sensor/post')}{literal}" class="form-control remote-select" data-remote="operators">
                    <option></option>
                </select>
            </div>
            <div class="col-xs-4">
                <input class="btn btn-info btn-block" type="submit" data-action="add_observer" data-parameters="participant_ids" value="{/literal}{'Aggiungi cc'|i18n('sensor/post')}{literal}" />
            </div>
        </div>
    </div>
{{/if}}
</script>
<script id="tpl-post-autoassign" type="text/x-jsrender">
{{if capabilities.can_auto_assign}}
    <div class="form-group" data-action-wrapper>
        <input type="hidden" data-value="participant_ids" value="{{:currentUserId}}" />
        <input class="btn btn-{{if workflowStatus.identifier == 'assigned'}}danger{{else}}info{{/if}} btn-lg btn-block" type="submit" data-action="assign" data-parameters="participant_ids"
               value="{/literal}{'Prendi in carico'|i18n('sensor/post')}{literal}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-fix" type="text/x-jsrender">
{{if capabilities.can_fix}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-success btn-lg btn-block" type="submit" data-action="fix" value="{/literal}{'Intervento terminato'|i18n('sensor/post')}{literal}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-force_fix" type="text/x-jsrender">
{{if capabilities.can_force_fix}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-danger btn-lg btn-block" type="submit" data-action="force_fix" value="{/literal}{'Forza chiusura'|i18n('sensor/post')}{literal}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-close" type="text/x-jsrender">
{{if capabilities.can_close}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-success btn-lg btn-block"
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
        <input class="btn btn-success btn-lg btn-block"
               type="submit"
               data-action="reopen"
               value="{/literal}{'Riapri'|i18n('sensor/post')}{literal}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-change_privacy" type="text/x-jsrender">
{{if capabilities.can_change_privacy}}
{{if privacy.identifier == 'public'}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-danger btn-lg btn-block" type="submit" data-action="make_private" value="{/literal}{'Rendi la segnalazione privata'|i18n('sensor/post')}{literal}" />
    </div>
{{else privacy.identifier == 'private'}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-danger btn-lg btn-block" type="submit" data-action="make_public" value="{/literal}{'Rendi la segnalazione pubblica'|i18n('sensor/post')}{literal}" />
    </div>
{{/if}}
{{/if}}
</script>
<script id="tpl-post-moderate" type="text/x-jsrender">
{{if capabilities.can_moderate && moderation.identifier == 'waiting'}}
    <div class="form-group" data-action-wrapper>
        <input class="btn btn-default btn-lg btn-block" type="submit" data-action="moderate" value="{/literal}{'Elimina moderazione'|i18n('sensor/post')}{literal}" />
    </div>
{{/if}}
</script>
<script id="tpl-post-private-messages" type="text/x-jsrender">
{{if capabilities.can_send_private_message || privateMessages.length > 0}}
<p>
    <a class="btn btn-info btn-lg btn-block" data-toggle="collapse" href="#collapseConversation" aria-expanded="false" aria-controls="collapseConversation">
        {/literal}{'Messaggi privati'|i18n('sensor/messages')}{literal}{{if privateMessages.length > 0}} <span class="badge">{{:privateMessages.length}}</span>{{/if}}
    </a>
</p>
<div class="collapse {{:privateMessageWrapperClass}}" id="collapseConversation">
    {{if privateMessages.length > 0}}
    <div class="comment">
        {{for privateMessages ~currentUserId=currentUserId}}
        <div class="{{if  ~currentUserId == creator.id}}text-right{{/if}}" style="margin-top:10px;padding-bottom:5px;border-bottom:1px dotted #bbb">
            <small>
                <ul class="list-unstyled">
                    <li>
                        {{if  ~currentUserId == creator.id}}
                            <a class="edit-message" href="#" data-message-id="{{:id}}"><i class="fa fa-edit"></i></a>
                        {{/if}}
                        {{:~formatDate(published, 'DD/MM/YYYY HH:mm')}}
                    </li>
                    {{if  ~currentUserId != creator.id}}
                        <li><small>{/literal}{'Da:'|i18n('sensor/messages')}{literal} {{:creator.name}}</small></li>
                    {{/if}}

                    {{if receivers.length > 0}}
                        <li>
                          <ul class="list-inline">
                                <li><small>{/literal}{'a:'|i18n('sensor/messages')}{literal} {{for receivers}}{{if #index > 0}}, {{/if}}{{:name}}{{/for}}</small></li>
                          </ul>
                        </li>
                    {{/if}}
                </ul>
            </small>
            {{if  ~currentUserId == creator.id}}
                <div id="edit-message-{{:id}}" style="display: none;" data-action-wrapper>
                    <input type="hidden" data-value="id" value="{{:id}}" />
                    <textarea data-value="text" class="form-control" rows="3">{{:text}}</textarea>
                    <input class="btn btn-info btn-sm btn-block" type="submit" data-action="edit_message" data-parameters="id,text" value="{/literal}{'Salva'|i18n('sensor/messages')}{literal}" />
                </div>
            {{/if}}
            <div id="view-message-{{:id}}">
                <p>{{:richText}}</p>
            </div>
        </div>
        {{/for}}
    </div>
    {{/if}}
    {{if capabilities.can_send_private_message}}
        <div class="new_comment" data-action-wrapper>
            <p>
                <textarea data-value="text" class="form-control" placeholder="{/literal}{'Aggiungi messaggio'|i18n('sensor/messages')}{literal}" rows="4"></textarea>
                <input class="btn send btn-info btn-sm btn-block" type="submit" data-action="send_private_message" data-parameters="text,participant_ids" value="{/literal}{'Invia messaggio'|i18n('sensor/messages')}{literal}" />
            </p>
            <strong>{/literal}{'Chi può leggere questo messaggio?'|i18n('sensor/messages')}{literal}</strong>
            {{for participants ~currentUserId=currentUserId}}
                {{if roleIdentifier != 5}}
                    {{if ~currentUserId == id}}
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" data-value="participant_ids" value="{{:id}}" checked="checked" readonly="readonly" />
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
        </div>
    {{/if}}
</div>
{{/if}}
</script>
{/literal}