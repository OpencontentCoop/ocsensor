{literal}
<script id="tpl-post-satisfy" type="text/x-jsrender">
{{if ~satisfyEntrypoint(workflowStatus.identifier)}}
<app-widget data-entrypoints="{{:~satisfyEntrypoint(workflowStatus.identifier)}}"></app-widget>
{{/if}}
</script>
{/literal}