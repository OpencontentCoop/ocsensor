{if $collaboration_item_status|eq(2)} {* ASSIGNED *}
{'Ti è stata assegnata la segnalazione #%post_id%, clicca sul seguente link: %post_url%'|i18n('sensor/whatsapp/post',, hash( '%post_id%', $node.contentobject_id, '%post_url%', $post_url ) )}
{/if}
