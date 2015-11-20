{set-block variable=$ruolo}{if $sensor_person|has_attribute( 'ruolo' )}{if $sensor_person|has_attribute( 'ruolo' )}{attribute_view_gui attribute=$sensor_person|attribute( 'ruolo' )}{/if}{/if}{/set-block}
{set-block variable=$struttura}
{if $sensor_person|has_attribute( 'struttura_di_competenza' )}{if $sensor_person|has_attribute( 'struttura_di_competenza' )}{foreach $sensor_person|attribute( 'struttura_di_competenza' ).content.relation_list as $related}{fetch(content,object,hash(object_id,$related.contentobject_id)).name|wash()}{/foreach}{/if}
{elseif $sensor_person.class_identifier|eq('dipendente')}{def $openpa = object_handler($sensor_person)}{if $openpa.content_ruoli_comune.ruoli.dipendente}{foreach $openpa.content_ruoli_comune.ruoli.dipendente as $ruolo_dipendente}{node_view_gui content_node=$ruolo_dipendente view=ruolo show_link=false()}{delimiter} - {/delimiter}{/foreach}{/if}{undef $openpa}
{/if}
{/set-block}
{$sensor_person.name|wash()} {if or( $ruolo, $struttura )}({if $ruolo}{$ruolo|trim()} {/if}{if $struttura}{$struttura|trim()}{/if}){/if}