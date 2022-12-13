{def $header_service = openpaini('GeneralSettings','header_service', 1)
     $header_service_list = array()
     $is_area_tematica = is_area_tematica()}
{if $header_service|eq(1)}
    {set $header_service_list = $header_service_list|append(hash(
        'url', openpaini('InstanceSettings','UrlAmministrazioneAfferente', '#'),
        'name', openpaini('InstanceSettings','NomeAmministrazioneAfferente', 'OpenContent')
    ))}
{/if}
{* todo header_links *}
{def $header_links = array()}


{def $enabled_languages = sensor_settings('SiteLanguages')}
{def $avail_translations = array()}
{if count($enabled_languages)|gt(1)}
    {set $avail_translations = language_switcher('/')}
    {def $lang_uri_maps = cond(ezini_hasvariable('SiteAccessSettings', 'LanguageStaticURI'), ezini('SiteAccessSettings', 'LanguageStaticURI'), array())}
{/if}


<div class="it-header-slim-wrapper{* theme-light*}">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="it-header-slim-wrapper-content">
                    {if $header_service_list|count()|gt(0)}
                        {foreach $header_service_list as $item}
                            <a class="d-none d-lg-block navbar-brand" href="{$item.url}">{$item.name|wash()}</a>
                        {/foreach}
                        <div class="nav-mobile">
                            <nav>
                                {if $header_links|count()}
                                <a class="d-lg-none navbar-brand"
                                   data-toggle="collapse"
                                   href="#service-menu"
                                   role="button"
                                   aria-expanded="false"
                                   aria-controls="service-menu">
                                    <span>{$header_service_list[0].name|wash()}</span>
                                    {display_icon('it-expand', 'svg', 'icon icon-white')}
                                </a>
                                <div class="link-list-wrapper collapse" id="service-menu">
                                    <ul class="link-list">
                                        {foreach $header_links as $header_link max 3}
                                            <li>{node_view_gui content_node=$header_link view=text_linked}</li>
                                        {/foreach}
                                    </ul>
                                </div>
                                {else}
                                    <a class="d-lg-none navbar-brand" href="{$header_service_list[0].url}"><span>{$header_service_list[0].name|wash()}</span></a>
                                {/if}
                            </nav>
                        </div>
                    {/if}
                    <div class="header-slim-right-zone">
                        {if count($enabled_languages)|gt(1)}
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle py-0" href="#" data-toggle="dropdown" aria-expanded="false">
                                <span>
                                    {foreach $avail_translations as $siteaccess => $lang}
                                        {if and($enabled_languages|contains($lang.locale), is_set($lang_uri_maps[$lang.locale]))}
                                            {if $siteaccess|eq($access_type.name)}
                                                {$lang.text|wash|upcase}
                                            {/if}
                                        {/if}
                                    {/foreach}
                                </span>
                                {display_icon('it-expand', 'svg', 'icon d-none d-lg-block')}
                            </a>
                            <div class="dropdown-menu">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="link-list-wrapper">
                                            <ul class="link-list">
                                                {foreach $avail_translations as $siteaccess => $lang}
                                                    {if and($enabled_languages|contains($lang.locale), is_set($lang_uri_maps[$lang.locale]))}
                                                        <li>
                                                            <a class="list-item" href={$lang_uri_maps[$lang.locale]}>
                                                                <span lang="{fetch(content, locale, hash(locale_code, $lang.locale)).http_locale_code|explode('-')[0]}">{$lang.text|wash|upcase}</span>
                                                            </a>
                                                        </li>
                                                    {/if}
                                                {/foreach}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/if}
                        <div class="it-access-top-wrapper">
                            {if $current_user.is_logged_in|not()}
                            <a data-login-top-button class="btn btn-primary btn-icon btn-full" href="{"/user/login"|ezurl(no)}">
                                 <span class="rounded-icon">
                                     {display_icon('it-user', 'svg', 'icon icon-primary')}
                                </span>
                                <span class="d-none d-lg-block">
                                    {sensor_translate('Login')}
                                </span>
                            </a>
                            {else}
                                <div class="dropdown">
                                    <a href="#" class="btn btn-primary btn-icon btn-full dropdown-toggle" id="dropdown-user" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="rounded-icon">
                                            {display_icon('it-user', 'svg', 'icon icon-primary notrasform')}
                                        </span>
                                        <span  class="d-none d-lg-block text-nowrap">{$current_user.contentobject.name|wash()}</span>
                                        {display_icon('it-expand', 'svg', 'icon-expand icon icon-white')}
                                    </a>
                                    <div class="dropdown-user dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-user">
                                        <div class="link-list-wrapper">
                                            <ul class="link-list">
                                                {foreach $social_pagedata.user_menu as $item}
                                                    <li><a class="list-item"  href="{$item.url|ezurl(no)}"><span>{$item.name|wash()|upcase()}</span></a></li>
                                                {/foreach}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
