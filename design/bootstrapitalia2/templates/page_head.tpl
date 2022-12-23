{default enable_help=true() enable_link=true() canonical_link=true()}

    <title>{$site.title|wash}</title>

    {if and(is_set($#Header:extra_data),is_array($#Header:extra_data))}
      {section name=ExtraData loop=$#Header:extra_data}
      {$:item}
      {/section}
    {/if}

    {* check if we need a http-equiv refresh *}
    {if $site.redirect}
    <meta http-equiv="Refresh" content="{$site.redirect.timer}; URL={$site.redirect.location}" />
    {/if}

    {foreach $site.http_equiv as $key => $item}
        <meta name="{$key|wash}" content="{$item|wash}" />

    {/foreach}

    {if is_set($module_result.content_info.persistent_variable.opengraph)}
        {foreach $module_result.content_info.persistent_variable.opengraph as $key => $value}
            {if is_array($value)}
                {foreach $value as $v}
                    <meta property="{$key}" content="{$v|wash()}" />
                {/foreach}
            {else}
                <meta property="{$key}" content="{$value|wash()}" />
            {/if}
        {/foreach}
    {/if}


{/default}


{def $favicon = openpaini('GeneralSettings','favicon', 'favicon.ico')}
{def $favicon_src = openpaini('GeneralSettings','favicon_src', 'ezimage')}
<!-- favicon -->
{if $favicon_src|eq('ezimage')}
    <link rel="icon" href="{$favicon|ezimage(no)}" type="image/x-icon" />
{else}
    <link rel="icon" href="{$favicon}" type="image/x-icon" />
{/if}
