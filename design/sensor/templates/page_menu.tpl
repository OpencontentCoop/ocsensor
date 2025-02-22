<div class="collapse navbar-collapse">

    {def $enabled_languages = sensor_settings('SiteLanguages')}
    {def $avail_translation = array()}
    {if count($enabled_languages)|gt(1)}
        {set $avail_translation = language_switcher('/')}
        {def $lang_uri_maps = cond(ezini_hasvariable('SiteAccessSettings', 'LanguageStaticURI'), ezini('SiteAccessSettings', 'LanguageStaticURI'), array())}
        {if $avail_translation|count()|gt(1)}
            <ul class="nav pull-right navbar-nav" style="margin-top: 0">
                {foreach $avail_translation as $siteaccess => $lang}
                    {if and($enabled_languages|contains($lang.locale), is_set($lang_uri_maps[$lang.locale]))}
                    <li>
                        <a href={$lang_uri_maps[$lang.locale]}>
                            {if $siteaccess|eq($access_type.name)}
                                <span class="label label-default" style="font-size: 100%">{$lang.text|wash}</span>
                            {else}
                                {$lang.text|wash}
                            {/if}

                        </a>
                    </li>
                    {/if}
                {/foreach}
            </ul>
        {/if}
    {/if}
    <ul class="nav pull-right navbar-nav"{if and(count($enabled_languages)|gt(1), is_set($avail_translation), count($avail_translation)|gt(1))} style="margin-top: -10px;clear:right;"{/if}>
        {foreach $social_pagedata.menu as $item}
            {if $item.has_children}
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle"
                       data-location="{$item.url|explode('/')|implode('-')}"
                       href="{$item.url|ezurl(no)}">{$item.name|wash()}
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        {foreach $item.children as $child}
                            <li><a data-location="{$child.url|explode('/')|implode('-')}" href="{$child.url|ezurl(no)}">{$child.name|wash()}</a></li>
                        {/foreach}
                    </ul>
                </li>
            {else}
                <li>
                    <a href="{$item.url|ezurl(no)}" data-location="{$item.url|explode('/')|implode('-')}">
                        {if $item.highlight}<span class="label label-primary" style="font-size: 100%{if $item.highlight|begins_with('#')}; background-color:{$item.highlight} !important;border-color:{$item.highlight} !important{/if}">{/if}
                            {$item.name|wash()}
                            {if $item.highlight}</span>{/if}
                    </a>
                </li>
            {/if}
        {/foreach}

        {if and(sensor_settings('AnnounceKitId'), fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'manage' )))}
            <li>
                <a href="#" id="announce-news" style="display: inline-block;text-decoration: none">
                    <span class="badge badge-warning pulsate"
                          style="display: none;top: 3px;right: -6px;font-size:10px;position: absolute"></span>
                    {sensor_translate('News')}
                </a>
            </li>
        {/if}

        {if $current_user.is_logged_in|not()}
            <li>
                <a href="{'user/login'|ezurl(no)}">
          <span class="label label-primary" style="font-size: 100%">
              {sensor_translate('Login')}
          </span>
                </a>
            </li>
        {else}
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    {include uri='design:parts/user_image.tpl' object=$current_user.contentobject height=25 width=25}
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li>
            <span style="text-transform: none;padding: 3px 20px;display: block;background: #eee;"><small>{$current_user.contentobject.name|wash()}
                <br/>{$current_user.email|shorten(40)|wash()}</small></span>
                    </li>
                    {foreach $social_pagedata.user_menu as $item}
                        <li><a href="{$item.url|ezurl(no)}">{$item.name|wash()}</a></li>
                    {/foreach}
                </ul>
            </li>
        {/if}

    </ul>
</div>

{if and(sensor_settings('AnnounceKitId'), fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'manage' )))}
    <script>{literal}
        $(document).ready(function () {
            window.announcekit = (window.announcekit || {
                queue: [], on: function (n, x) {
                    window.announcekit.queue.push([n, x]);
                }, push: function (x) {
                    window.announcekit.queue.push(x);
                }
            });
            window.announcekit.push({
                "widget": "https://announcekit.app/widgets/v2/{/literal}{sensor_settings('AnnounceKitId')}{literal}",
                "selector": ".announcekit-widget",
                "name": "announcekit",
                "lang": "it"
            });
            window.announcekit.on("widget-unread", function ({widget, unread}) {
                var badge = $('#announce-news .badge');
                if (unread === 0) badge.hide();
                else badge.show().html(unread);
            });
            $('#announce-news').on('click', function (e) {
                announcekit.widget$announcekit.open();
                e.preventDefault();
            });
        })
        {/literal}</script>
    <script async src="https://cdn.announcekit.app/widget-v2.js"></script>
{/if}
