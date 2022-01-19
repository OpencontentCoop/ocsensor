{cache-block ignore_content_expiry keys=array( $identifier )}
  <section class="hgroup">
    <h1>
      {if $attribute.contentclass_attribute_identifier|eq('faq')}
        {sensor_translate('Faq')}
      {elseif $attribute.contentclass_attribute_identifier|eq('privacy')}
        {sensor_translate('Privacy')}
      {elseif $attribute.contentclass_attribute_identifier|eq('terms')}
        {sensor_translate('Terms of use')}
      {else}
        {$attribute.contentclass_attribute_name|wash()}
      {/if}
    </h1>
  </section>

  <div class="row">
    <div class="col-md-12">
      {attribute_view_gui attribute=$attribute}
    </div>
  </div>
  {if $identifier|eq('faq')}
    {include name="faq_list" uri='design:sensor/faq.tpl'}
  {/if}
{/cache-block}
