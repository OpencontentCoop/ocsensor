{def $dimensions = ''}
{if is_set( $height )}
  {set $dimensions = concat( $dimensions, 'height="', $height, '" ' )}
{/if}
{if is_set( $width )}
  {set $dimensions = concat( $dimensions, 'width="', $width, '" ' )}
{/if}

{if and( is_set( $object.data_map.image ), $object.data_map.image.has_content )}
    <img src="{$object.data_map.image.content['small'].full_path|ezroot(no)}" class="img-circle" {$dimensions} style="max-width: 90px; max-height: 90px"/>
{else}
    <img src={"user_placeholder.jpg"|ezimage()} class="img-circle" {$dimensions} />
{/if}