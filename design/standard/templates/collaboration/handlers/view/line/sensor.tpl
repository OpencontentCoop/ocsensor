{def $content_object=fetch("content","object",hash("object_id",$item.content.content_object_id))}

{if $content_object}

    {$content_object.name|wash()}

{else}

  <p>La segnalazione {$item.content.content_object_id} non &egrave; accessibile o &egrave; stata rimossa.</p>

{/if}