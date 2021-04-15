{cache-block ignore_content_expiry keys=array( $identifier )}
  <section class="hgroup">
    <h1>
      {$attribute.contentclass_attribute_name|wash()}
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
