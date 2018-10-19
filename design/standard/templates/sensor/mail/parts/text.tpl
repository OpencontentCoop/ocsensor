{if $header}
<h2>{$header|wash()}</h2>
{/if}
{if $text}
<p>{$text|wash()|nl2br}</p>
{/if}