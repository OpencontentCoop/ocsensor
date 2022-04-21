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

{pdf(text, concat('<b>', sensor_translate('Issue number'), ' ', $post_id, '</b>')|wash(pdf), hash(size, 14))}
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

{if count($files)}
	{pdf(text, concat('<b>', sensor_translate('Files'), '</b>')|wash(pdf))}
	{pdf(newline)}

	{foreach $files as $file}
		{pdf(text, $file['Name']|wash(pdf))}
		{pdf(text, concat(' (', $file['Mime'], ' - ',  $file['Size'] , ')')|wash(pdf), hash(size, 8))}
		{pdf(newline)}
	{/foreach}
{/if}

{if count($images)}
	{pdf(text, concat('<b>', sensor_translate('Issue images'), '</b>')|wash(pdf))}
	{pdf(newline)}

	{foreach $images as $image}
		{pdf(image,$image)}
	{/foreach}
{/if}

{if or(count($comments), count($notes), count($timelines), count($responses), count($attachments))}
{pdf( 'new_page' )}
{/if}

{if count($timelines)}
	{pdf(text, concat('<b>', sensor_translate('Timeline'), '</b>')|wash(pdf), hash(size, 14))}
	{pdf(newline)}

	{foreach $timelines as $comment}
		{pdf(text, concat('<b>', $comment['Autore'], ' - ', $comment['Data'], '</b>')|wash(pdf))}
		{pdf(newline)}
		{pdf(text, $comment['Testo']|wash(pdf))}
		{pdf(newline)}
		{pdf(newline)}
	{/foreach}
	{pdf(newline)}
	{pdf(newline)}
{/if}

{if count($comments)}
	{pdf(text, concat('<b>', sensor_translate('Issue comments'), '</b>')|wash(pdf), hash(size, 14))}
	{pdf(newline)}

	{foreach $comments as $comment}
		{pdf(text, concat('<b>', $comment['Autore'], ' - ', $comment['Data'], '</b>')|wash(pdf))}
		{pdf(newline)}
		{pdf(text, $comment['Testo']|wash(pdf))}
		{pdf(newline)}
		{pdf(newline)}
	{/foreach}
	{pdf(newline)}
	{pdf(newline)}
{/if}

{if count($notes)}
	{pdf(text, concat('<b>', sensor_translate('Issue private notes'), '</b>')|wash(pdf), hash(size, 14))}
	{pdf(newline)}

	{foreach $notes as $comment}
		{pdf(text, concat('<b>', $comment['Autore'], ' - ', $comment['Data'], '</b>')|wash(pdf))}
		{pdf(newline)}
		{pdf(text, $comment['Testo']|wash(pdf))}
		{pdf(newline)}
		{pdf(newline)}
	{/foreach}
	{pdf(newline)}
	{pdf(newline)}
{/if}

{if count($responses)}
	{pdf(text, concat('<b>', sensor_translate('Responses'), '</b>')|wash(pdf), hash(size, 14))}
	{pdf(newline)}

	{foreach $responses as $comment}
		{pdf(text, concat('<b>', $comment['Autore'], ' - ', $comment['Data'], '</b>')|wash(pdf))}
		{pdf(newline)}
		{pdf(text, $comment['Testo']|wash(pdf))}
		{pdf(newline)}
		{pdf(newline)}
	{/foreach}
	{pdf(newline)}
	{pdf(newline)}
{/if}

{if count($attachments)}
	{pdf(text, concat('<b>', sensor_translate('Attachments'), '</b>')|wash(pdf), hash(size, 14))}
	{pdf(newline)}

	{foreach $attachments as $file}
		{pdf(text, $file['Name']|wash(pdf))}
		{pdf(text, concat(' (', $file['Mime'], ' - ',  $file['Size'] , ')')|wash(pdf), hash(size, 8))}
		{pdf(newline)}
	{/foreach}
{/if}

{pdf(footer, hash( text, concat(sensor_translate('Print'), ' ', currentdate()|l10n( datetime ), ' - ', fetch('user', 'current_user').contentobject.name)|wash(pdf), size, 7, align, "right" ) ) }
