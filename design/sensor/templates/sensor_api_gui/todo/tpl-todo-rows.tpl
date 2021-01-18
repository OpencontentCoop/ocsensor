{literal}
<script id="tpl-todo-rows" type="text/x-jsrender">
    <thead>
        <tr>
            <td colspan="5">
                <ul class="list-inline pull-left">
                    <li><a data-reload href="#" title="Ricarica"><i class="fa fa-refresh"></i></a></li>
                </ul>
                <ul class="list-inline pull-right text-right">
                    <li class="text-muted">{{:start}}-{{:end}} di {{:count}}</li>
                    {{if pages > 1}}
                        <li><a data-prev href="#" class="{{if current_page == 1}}disabled text-muted{{/if}}" title="Più recenti"><i class="fa fa-chevron-left"></i></a></li>
                        <li><a data-next href="#" class="{{if current_page == pages}}disabled text-muted{{/if}}" title="Più recenti"><i class="fa fa-chevron-right"></i></a></li>
                    {{/if}}
                </ul>
            </td>
        </tr>
    </thead>
    <tbody>
    {{for items}}
        {{include tmpl="#tpl-todo-item-row"/}}
    {{/for}}
    </tbody>
</script>
{/literal}