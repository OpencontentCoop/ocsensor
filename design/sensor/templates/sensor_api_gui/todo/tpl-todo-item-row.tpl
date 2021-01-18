{literal}
<script id="tpl-todo-item-row" type="text/x-jsrender">
<tr style="cursor:pointer" data-previewid="{{:id}}">
    <td width="1" style="vertical-align:middle{{if has_read == 0}};font-weight:bold{{/if}}">
        <i class="fa fa-circle" style="color:{{if status == 'close'}}#5cb85c{{else status == 'pending'}}#d9534f{{else}}#f0ad4e{{/if}}"></i>
    </td>
    <td width="1" style="vertical-align:middle{{if has_read == 0}};font-weight:bold{{/if}}">
        <i data-star="{{:id}}" class="fa fa-star{{if is_special}} text-primary{{else}}-o text-muted{{/if}}"></i>
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
    <td width="1" style="vertical-align:middle;white-space:nowrap{{if has_read == 0}};font-weight:bold{{/if}}">
        {{:~progressiveDate(modified_datetime)}}
    </td>
</tr>
</script>
{/literal}

