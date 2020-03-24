{literal}
<script id="tpl-tree-option" type="text/x-jsrender">
{{for children}}
    <option value="{{:id}}" style="padding-left:calc(10px*{{:level}});{{if level == 0 && type != 'sensor_group' && type != 'sensor_operator'}}font-weight: bold;{{/if}};" disabled="disabled">{{:name}}</option>
    {{include tmpl="#tpl-tree-option"/}}
{{/for}}
</script>
{/literal}