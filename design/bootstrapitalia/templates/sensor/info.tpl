{cache-block ignore_content_expiry keys=array( $identifier )}
    <section class="container mt-5">
        <div class="row">
            <div class="col-lg-12 px-lg-4 py-lg-2">
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
                <div class="mb-md-4 mb-lg-5 row"><div class="col"><hr class="d-none d-lg-block mt-30 mb-2"></div></div>
                <div class="lead">
                    {attribute_view_gui attribute=$attribute}
                </div>
                {if $identifier|eq('faq')}
                    {include name="faq_list" uri='design:sensor/faq.tpl'}
                {/if}
            </div>
        </div>
    </section>
{/cache-block}
