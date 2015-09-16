{if $collaboration_item_status|eq(0)} {* WAITING*}
{'Nuova segnalazione'|i18n('sensor/whatsapp/post')} #{$node.contentobject_id} {*$post_url*}


{elseif $collaboration_item_status|eq(4)} {* FIXED *}
{'Segnalazione chiusa da operatore'|i18n('sensor/whatsapp/post')} #{$node.contentobject_id} {*$post_url*}

{/if}
