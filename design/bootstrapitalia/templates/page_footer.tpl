<footer class="it-footer" id="footer">

    <div class="it-footer-main">
        <div class="container">
            <section>
                <div class="row clearfix">
                    <div class="col-sm-12 my-4">
                        {include uri='design:logo.tpl'}
                    </div>
                </div>
            </section>
            <section>
                <div class="row">
                    <div class="col pb-2">
                        <h3>{'Contacts'|i18n('ocsocialdesign')}</h3>
                        <p>{attribute_view_gui attribute=$social_pagedata.attribute_contacts}</p>
                    </div>
                    <div class="col pb-2">
                        <p>{attribute_view_gui attribute=$social_pagedata.attribute_footer}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <div class="it-footer-small-prints clearfix">
        <h3 class="text-white sr-only d-none">{'Links'|i18n('openpa/footer')}</h3>
        <div class="container">
            {def $footer_links = fetch( 'openpa', 'footer_links' )}
            <ul class="it-footer-small-prints-list list-inline mb-0 d-flex flex-column flex-md-row">
                {foreach $footer_links as $item}
                    <li class="list-inline-item">{node_view_gui content_node=$item view=text_linked}</li>
                {/foreach}
            </ul>
            {undef $footer_links}
        </div>
    </div>
    {include uri='design:footer/copyright.tpl'}
</footer>

<a href="#" aria-hidden="true" data-attribute="back-to-top" class="back-to-top shadow"
   aria-label="{'back to top'|i18n('openpa/footer')}">
    {display_icon('it-arrow-up', 'svg', 'icon icon-light')}
</a>
