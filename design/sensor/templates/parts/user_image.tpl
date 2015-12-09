{def $dimensions = ''}
{if is_set( $height )}
  {set $dimensions = concat( $dimensions, 'height="', $height, '" ' )}
{/if}
{if is_set( $width )}
  {set $dimensions = concat( $dimensions, 'width="', $width, '" ' )}
{/if}

{if $user.image}
    <img src="{$user.image.small.url|ezroot(no)}" class="img-circle" {$dimensions} style="max-width: 90px; max-height: 90px"/>
{else}
    <img src="{"user_placeholder.jpg"|ezimage(no)}" class="img-circle" {$dimensions} />
{/if}