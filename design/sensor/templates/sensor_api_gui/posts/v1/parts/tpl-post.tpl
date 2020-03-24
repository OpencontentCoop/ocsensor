{literal}
<script id="tpl-post" type="text/x-jsrender">
{{include tmpl="#tpl-post-title"/}}
<div class="row">
    <div class="col-md-8">
        {{include tmpl="#tpl-post-detail"/}}
        {{include tmpl="#tpl-post-messages"/}}
    </div>
    <div class="col-md-4 sidebar">
        {{include tmpl="#tpl-post-actions"/}}
        {{include tmpl="#tpl-post-participants"/}}
        {{include tmpl="#tpl-post-timeline"/}}
    </div>
</div>
</script>
{/literal}