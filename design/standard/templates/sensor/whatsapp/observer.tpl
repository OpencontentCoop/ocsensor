{if $collaboration_item_status|eq(4)} {* FIXED *}
{'La segnalazione #%post_id% è stata chiusa da operatore; per ulteriori informazioni, clicca sul seguente link: %post_url%'|i18n('sensor/whatsapp/post',, hash( '%post_id%', $node.contentobject_id, '%post_url%', $post_url ) )}

{elseif $collaboration_item_status|eq(3)} {* CLOSED *}
{'La segnalazione #%post_id% è stata risolta; per ulteriori informazioni, clicca sul seguente link: %post_url%'|i18n('sensor/whatsapp/post',, hash( '%post_id%', $node.contentobject_id, '%post_url%', $post_url ) )}

{/if}
