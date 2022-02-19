{literal}
<script id="tpl-todo-item-row" type="text/x-jsrender">
<tr style="cursor:pointer;position:relative" data-previewid="{{:id}}">
    <td width="1" style="vertical-align:middle{{if has_read == 0}};font-weight:bold{{/if}}">
        <i class="fa fa-circle" style="color:{{if status == 'close'}}#5cb85c{{else status == 'pending'}}#d9534f{{else}}#f0ad4e{{/if}}"></i>
    </td>
    <td width="1" style="white-space:nowrap;vertical-align:middle{{if has_read == 0}};font-weight:bold{{/if}}">
        <i data-star="{{:id}}" class="fa fa-star{{if is_special}} text-primary{{else}}-o text-muted{{/if}}"></i>
        {/literal}{if can_set_sensor_tag()}{literal}
        <i style="margin-left:5px" data-special="{{:id}}" class="fa fa-bell{{if is_tagged_special}} text-primary{{else}}-o text-muted{{/if}}"></i>
        {/literal}{/if}{literal}
    </td>
    <td width="1" style="vertical-align:middle;white-space:nowrap{{if has_read == 0}};font-weight:bold{{/if}}">
        {{:id}}
    </td>
    <td style="vertical-align:middle{{if has_read == 0}};font-weight:bold{{/if}}">
        <div class="row">
            <div class="col-md-4">
                {{:people_html}}
                {{if conversations > 1}}<small class="text-muted">{{:conversations}}</small>{{/if}}
            </div>
            <div class="col-md-8">
                <span class="label label-info">{{:category}}</span>
                {{if action}}<em>{{if actions.length > 1}}.. {{/if}}<span class="todo-action">{{:action}}</span>:</em>{{/if}}
                {{:subject}}
                {{if workflowStatus != 'closed' && expirationInfo.label == 'danger'}}
                <br />
                <small class="text-{{:expirationInfo.label}}" title="{{:~formatDate(expirationInfo.expirationDateTime, 'DD/MM/YYYY HH:mm')}}">{{:expirationInfo.text}}</small>
                {{/if}}
            </div>
        </div>
    </td>
    <td width="1" style="position:relative;vertical-align:middle;white-space:nowrap{{if has_read == 0}};font-weight:bold{{/if}}">
        <div{{if contextActions.length > 0}} class="todo-date"{{/if}}>
            {{:~progressiveDate(modified_datetime)}}
        </div>
        {{if contextActions.length > 0}}
        <div class="todo-actions hide">
            {{for contextActions}}
                {{include tmpl="#tpl-todo-item-"+identifier /}}
            {{/for}}
        </div>
        {{/if}}
    </td>
</tr>
</script>
<script id="tpl-todo-item-context-close" type="text/x-jsrender">
    <a href="#" data-todo_action="close" class="todo_action has-tooltip" title="{{:~sensorTranslate('Close')}}">
        <i class="fa fa-close fa-2x todo_action"></i>
    </a>

    {{if data.last_private_note}}
    <a href="#" data-todo_action="close_with_note" class="has-tooltip todo_action" title="{{:~sensorTranslate("Close with operator's note:")}}<br /><em>{{:data.last_private_note.text}}</em>" data-response="{{:data.last_private_note.text}}">
        <span class="fa-stack todo_action">
          <i class="fa fa-comment fa-stack-2x todo_action"></i>
          <i class="fa fa-close fa-stack-1x fa-inverse todo_action"></i>
        </span>
    </a>
    {{/if}}

    <a href="#" data-todo_action="close_with_last" class="{{if !~last_response()}}hide{{/if}} has-tooltip todo_action" data-base_title="{{:~sensorTranslate('Close with last answer:')}}<br />" title="{{:~sensorTranslate('Close with last answer:')}}<br /><em>{{:~last_response()}}</em>">
        <span class="fa-stack todo_action">
          <i class="fa fa-comment-o fa-stack-2x todo_action"></i>
          <i class="fa fa-close fa-stack-1x todo_action"></i>
        </span>
    </a>
</script>
<style>
    .todo-actions{
        position: absolute;
        right: 0;
        top: 0;
        padding: 0 20px;
        height: 100%;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .todo-actions a{
        color: #666;
        margin-left: 8px;
        position: relative;
        padding: 5px;
        border-radius: 100%;
        min-width: 30px;
        text-align: center;
        font-size: .8em;
        display: inline-block;
    }
    .todo-actions a:hover{
        background: #ddd;
    }
    .ui-tooltip {
        padding: 5px 8px;
        position: absolute;
        z-index: 9999;
        max-width: 300px;
        background: #666;
        color: #fff;
        font-size: 1em;
        border-radius: 5px;
    }
</style>
{/literal}

