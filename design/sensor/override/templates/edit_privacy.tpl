{default attribute_base=ContentObjectAttribute html_class='full'}
{let selected_id_array=$attribute.content}
<p><strong>{$attribute.contentclass_attribute.name}:</strong>  
{* Always set the .._selected_array_.. variable, this circumvents the problem when nothing is selected. *} 
<input type="hidden" name="{$attribute_base}_ezselect_selected_array_{$attribute.id}" value="" />
{section var=Options loop=$attribute.class_content.options}
<input type="radio" name="{$attribute_base}_ezselect_selected_array_{$attribute.id}[]" {if $selected_id_array|contains( $Options.item.name )}checked="checked"{/if} value="{$Options.item.name|wash( xhtml )}"> {$Options.item.name|wash( xhtml )} 
{/section}
</p>
{/let}
{/default}
