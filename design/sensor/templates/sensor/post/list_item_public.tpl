{def $sensor_post = $node.object|sensor_post()}
{if $sensor_post}
    <div class="row">
        <div class="col-md-12">
            <section class="hgroup">
                <h2 class="section_header skincolored" style="margin-bottom: 0;border: none">
                    <a href={concat('sensor/public_posts/',$sensor_post.id)|ezurl()}>
                        <span class="label label-primary">{$sensor_post.id}</span>
                        {$sensor_post.object.name|wash()}
                    </a>
                </h2>
                <ul class="breadcrumb pull-right">
                    <li>
                        <span class="label label-{$sensor_post.type.css_class}">{$sensor_post.type.name|wash()}</span>
                    </li>
                </ul>
            </section>
        </div>
    </div>
    <div class="row service_teaser" style="margin-bottom: 10px;">
        {if $sensor_post.object|has_attribute('image')}
            <div class="service_photo col-sm-4 col-md-4">
                <figure style="background-image:url({$sensor_post.object|attribute('image').content.large.full_path|ezroot(no)})"></figure>
            </div>
        {/if}
        <div class="service_details {if $sensor_post.object|has_attribute('image')}col-sm-8 col-md-8{else}col-sm-12 col-md-12{/if}">
            <div class="clearfix">
                <p class="pull-left">
                    {if $sensor_post.object|has_attribute('geo')}
                        <i class="fa fa-map-marker"></i>
                        {$sensor_post.object|attribute('geo').content.address}
                    {elseif $sensor_post.object|has_attribute('area')}
                        {attribute_view_gui attribute=$sensor_post.object|attribute('area')}
                    {/if}
                </p>
            </div>
            <p>
                {attribute_view_gui attribute=$sensor_post.object|attribute('description')}
            </p>
            {if $sensor_post.object|has_attribute('attachment')}
                <p>{attribute_view_gui attribute=$sensor_post.object|attribute('attachment')}</p>
            {/if}
            <a href={concat('sensor/public_posts/',$sensor_post.object.id)|ezurl()} class="btn btn-info
               btn-sm">{"Dettagli"|i18n('sensor/dashboard')}</a>
        </div>
    </div>
{/if}
{undef $sensor_post}