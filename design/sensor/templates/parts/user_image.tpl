{def $dimensions = ''}
{if is_set( $height )}
  {set $dimensions = concat( $dimensions, 'height="', $height, '" ' )}
{/if}
{if is_set( $width )}
  {set $dimensions = concat( $dimensions, 'width="', $width, '" ' )}
{/if}

<img src="/sensor/avatar/{$object.id}" class="img-circle" {$dimensions} style="max-width: 90px; max-height: 90px" />