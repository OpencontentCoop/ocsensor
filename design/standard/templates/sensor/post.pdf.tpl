{attribute_pdf_gui attribute=$logo attribute_parameters=hash('align', 'center') image_class=$logo_image_alias}
{pdf(header, hash( level, 1,
                   text, $title|wash(pdf),
		   size, 20,
		   align, center ) )}
{pdf(header, hash( level, 2,
                   text, $subtitle|wash(pdf),
		   size, 10,
		   align, center ) )}
{pdf(newline)}

{pdf(text, concat('<b>Segnalazione numero ', $post_id, '</b>')|wash(pdf), hash(size, 14))}
{pdf(newline)}

{foreach $attributes as $name => $value}
	{pdf(text, concat('<b>', $name, '</b>')|wash(pdf))}
	{pdf(newline)}
	{foreach $value as $item}
		{pdf(text, $item|wash(pdf))}
		{pdf(newline)}
	{/foreach}
	{pdf(newline)}
{/foreach}

{if or(count($comments), count($notes))}
{pdf( 'new_page' )}
{/if}

{if count($comments)}
	{pdf(text, '<b>Commenti alla segnalazione</b>'|wash(pdf), hash(size, 14))}
	{pdf(newline)}

	{foreach $comments as $comment}
		{pdf(text, concat('<b>', $comment['Autore'], ' - ', $comment['Data'], '</b>')|wash(pdf))}
		{pdf(newline)}
		{pdf(text, $comment['Testo']|wash(pdf))}
		{pdf(newline)}
		{pdf(newline)}
	{/foreach}
	{pdf(newline)}
{/if}

{if count($notes)}
	{pdf(text, '<b>Note private alla segnalazione</b>'|wash(pdf), hash(size, 14))}
	{pdf(newline)}

	{foreach $notes as $comment}
		{pdf(text, concat('<b>', $comment['Autore'], ' - ', $comment['Data'], '</b>')|wash(pdf))}
		{pdf(newline)}
		{pdf(text, $comment['Testo']|wash(pdf))}
		{pdf(newline)}
		{pdf(newline)}
	{/foreach}
	{pdf(newline)}
{/if}

{if count($images)}
	{pdf( 'new_page' )}
	{pdf(text, '<b>Immagini allegate alla segnalazione</b>'|wash(pdf), hash(size, 14))}
	{pdf(newline)}

	{foreach $images as $image}
		{pdf(image,$image)}
	{/foreach}
{/if}

{pdf(footer, hash( text, concat('Stampato ', currentdate()|l10n( datetime ))|wash(pdf), size, 7, align, "right" ) ) }
