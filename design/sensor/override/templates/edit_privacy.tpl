<style>
{literal}
label.btn span {
  font-size: 1.5em ;
}
label input[type="radio"] ~ i.fa.fa-circle-o{
    display: inline;
}
label input[type="radio"] ~ i.fa.fa-check-circle-o{
    display: none;
}
label input[type="radio"]:checked ~ i.fa.fa-circle-o{
    display: none;
}
label input[type="radio"]:checked ~ i.fa.fa-check-circle-o{
    display: inline;
}
div[data-toggle="buttons"] label {
	display: inline-block;
	padding: 6px 12px;
	margin-bottom: 0;
	font-size: 11px;
	font-weight: normal;
	line-height: 2em;
	text-align: left;
	white-space: nowrap;
	vertical-align: top;
	cursor: pointer;
	background-color: none;
	border: 0px solid
	#c8c8c8;
	border-radius: 3px;
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	-o-user-select: none;
	user-select: none;
}

div[data-toggle="buttons"] label:active, div[data-toggle="buttons"] label.active {
	-webkit-box-shadow: none;
	box-shadow: none;
}
{/literal}
</style>

{default attribute_base=ContentObjectAttribute html_class='full'}
{let selected_id_array=$attribute.content}
<p>
    <strong style="margin-bottom: 10px">{$attribute.contentclass_attribute.name}:</strong>
</p>
{* Always set the .._selected_array_.. variable, this circumvents the problem when nothing is selected. *}
<input type="hidden" name="{$attribute_base}_ezselect_selected_array_{$attribute.id}" value="" />
<div class="btn-group" data-toggle="buttons">
    {section var=Options loop=$attribute.class_content.options}
    <label class="btn"><input type="radio" name="{$attribute_base}_ezselect_selected_array_{$attribute.id}[]" {if $selected_id_array|contains( $Options.item.name )}checked="checked"{/if} value="{$Options.item.name|wash( xhtml )}"><i class="fa fa-circle-o fa-2x"></i><i class="fa fa-check-circle-o fa-2x"></i><span> {$Options.item.name|wash( xhtml )}</span></label>
    {/section}
</div>

{/let}
{/default}
