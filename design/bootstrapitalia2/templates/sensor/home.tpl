{ezpagedata_set( 'has_container', true() )}
{if is_set($social_pagedata)|not()}{def $social_pagedata = social_pagedata()}{/if}
{def $show_homepage_blocks = cond(
    and(
        ezini('SensorConfig', 'CustomHomepageDashboard', 'ocsensor.ini')|eq('enabled'),
        $current_user.is_logged_in,
        $current_user.contentobject.class_identifier|eq('user')
    ),
    true(),
    false()
)}

{if $social_pagedata.banner_path}
<div class="it-hero-wrapper it-overlay it-bottom-overlapping-content rounded shadow">
    <div class="img-responsive-wrapper">
        <div class="img-responsive">
            <div class="img-wrapper"><img src="{$social_pagedata.banner_path|ezroot(no)}" alt="{$social_pagedata.site_title}"></div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="it-hero-text-wrapper bg-dark" style="padding-right: 20%;">
                    <h1 class="py-1 px-2 d-inline-block bg-secondary text-white m-0">{$social_pagedata.banner_title}</h1>
                    <h2 class="text-white bg-primary d-inline-block py-1 px-2">{$social_pagedata.banner_subtitle}</h2>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}
<div class="container mb-5{if $social_pagedata.banner_path|not()} mt-5{/if}">
    {if $show_homepage_blocks}
        {include uri='design:sensor/home_blocks.tpl'}
    {else}
        {def $post_container = sensor_postcontainer()}
        <div class="row">
            <div class="col-12">
                <div class="card-wrapper card-space">
                    <div class="card card-bg no-after rounded shadow">
                        <div class="card-body p-5">
                            <h3 class="text-primary h1">{$post_container.data_map.name.content|wash()}</h3>
                            {attribute_view_gui attribute=$post_container.data_map.short_description}
                            <div class="text-center">
                            {if fetch(user, current_user).is_logged_in|not()}
                                <a href="{'/user/login'|ezurl(no)}"
                                   class="btn btn-primary btn-lg">{sensor_translate('Login', 'menu')}</a>
                            {else}
                                <a href="{'sensor/add'|ezurl(no)}"
                                   class="btn btn-primary btn-lg">{sensor_translate('Create issue', 'report')}</a>
                            {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>