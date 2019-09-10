{literal}
<script id="tpl-post-participants" type="text/x-jsrender">
<aside class="widget" id="current-post-participants">
    <h4>{/literal}{'Soggetti coinvolti'|i18n('sensor/post')}{literal}</h4>
    <dl class="dl">
        <dt>{{if author.roleName}}{{:author.roleName}}{{else}}{/literal}{'Autore'|i18n('sensor/post')}{literal}{{/if}}:</dt>
        <dd>
            <ul class="list-unstyled">
                <li><small>{{:author.name}}</small></li>
            </ul>
        </dd>
        {{if approvers.length}}
        {{for approvers}}
            {{if #index == 0}}<dt>{{:roleName}}:</dt>{{/if}}
        {{/for}}
        <dd>
            <ul class="list-unstyled">
            {{for approvers}}
                {{if type == 'user'}}
                    <li><small>{{:name}}</small></li>
                {{/if}}
            {{/for}}
            {{for approvers}}
                {{if type == 'group'}}
                    <li><small><em>{{:name}}</em></small></li>
                {{/if}}
            {{/for}}
            </ul>
        </dd>
        {{/if}}

        {{for owners ~count=owners.length}}
            {{if #index == 0}}<dt>{{:roleName}}:</dt>
            <dd>
                <ul class="list-unstyled">{{/if}}
                    <li><small>{{:name}}</small></li>
                {{if #index == (~count - 1)}}</ul>
            </dd>{{/if}}
        {{/for}}
        {{for observers ~count=observers.length}}
            {{if #index == 0}}<dt>{{:roleName}}:</dt>
            <dd>
                <ul class="list-unstyled">{{/if}}
                    <li><small>{{:name}}</small></li>
                {{if #index == (~count - 1)}}</ul>
            </dd>{{/if}}
        {{/for}}
    </dl>
</aside>
</script>
{/literal}