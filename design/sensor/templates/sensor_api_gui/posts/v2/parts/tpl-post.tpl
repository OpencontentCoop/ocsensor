{literal}
<script id="tpl-post" type="text/x-jsrender">
{{include tmpl="#tpl-post-title"/}}
<div class="row post-workflow-{{:workflowStatus.identifier}}">
    <div class="col-md-9">
        <div class="bordered">
            {{include tmpl="#tpl-post-detail"/}}
            {{include tmpl="#tpl-post-messages"/}}
            {{include tmpl="#tpl-post-satisfy"/}}
        </div>
    </div>
    <div class="col-md-3 sidebar">
        {{include tmpl="#tpl-post-sidebar"/}}
    </div>
</div>
</script>
{/literal}