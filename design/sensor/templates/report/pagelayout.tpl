<!doctype html>
<html class="no-js" lang="en">

<head>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {ezcss_load( array('style.css'))}
    {ezscript_load(array('ezjsc::jquery'))}

{cache-block expiry=86400 ignore_content_expiry keys=array( $current_user.contentobject_id, $access_type.name )}
    {def $social_pagedata = social_pagedata()}

    <title>{$social_pagedata.site_title}</title>

    {if $social_pagedata.head_images["apple-touch-icon-114x114-precomposed"]}
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{$social_pagedata.head_images["apple-touch-icon-114x114-precomposed"]}" />
    {/if}
    {if $social_pagedata.head_images["apple-touch-icon-72x72-precomposed"]}
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{$social_pagedata.head_images["apple-touch-icon-72x72-precomposed"]}" />
    {/if}
    {if $social_pagedata.head_images["apple-touch-icon-57x57-precomposed"]}
        <link rel="apple-touch-icon-precomposed" href="{$social_pagedata.head_images["apple-touch-icon-57x57-precomposed"]}" />
    {/if}
    {if $social_pagedata.head_images["favicon"]}
        <link rel="icon" href="{$social_pagedata.head_images["favicon"]}">
    {else}
        {def $favicon = openpaini('GeneralSettings','favicon', 'favicon.ico')}
        {def $favicon_src = openpaini('GeneralSettings','favicon_src', 'ezimage')}
        {if $favicon_src|eq('ezimage')}
            <link rel="icon" href="{$favicon|ezimage(no)}" type="image/x-icon" />
        {else}
            <link rel="icon" href="{$favicon}" type="image/x-icon" />
        {/if}
    {/if}
</head>

{include uri='design:page_head_google_tag_manager.tpl'}
{include uri='design:page_head_google-site-verification.tpl'}

<body>

{/cache-block}

{$module_result.content}

{cache-block expiry=86400 ignore_content_expiry keys=array( $access_type.name )}
{if is_set($social_pagedata)|not()}{def $social_pagedata = social_pagedata()}{/if}
{if $social_pagedata.google_analytics_id}
<script type="text/javascript">
    (function(i,s,o,g,r,a,m){ldelim}i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ldelim}
        (i[r].q=i[r].q||[]).push(arguments){rdelim},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        {rdelim})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', '{$social_pagedata.google_analytics_id}', 'auto');
    ga('set', 'anonymizeIp', true);
    ga('set', 'forceSSL', true);
    ga('send', 'pageview');
</script>
{/if}
{/cache-block}

<!--DEBUG_REPORT-->
</body>
</html>