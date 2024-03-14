<!DOCTYPE html>
<!--[if lt IE 9 ]><html class="unsupported-ie ie" lang="{$site.http_equiv.Content-language|wash}"><![endif]-->
<!--[if IE 9 ]><html class="ie ie9" lang="{$site.http_equiv.Content-language|wash}"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->
<html lang="{$site.http_equiv.Content-language|wash}"><!--<![endif]-->
<head>

    {cache-block keys=array( $module_result.uri )}

    {def $social_pagedata = social_pagedata()}
    {def $pagedata = ezpagedata()}

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {include uri='design:page_head.tpl'}

    {ezcss_load( array(
    'opensans.css',
    'animate.css',
    'style.css',
    'custom.css',
    'font-awesome.min.css',
    'font-awesome-animation.min.css'
    ))}

</head>

<body style="padding:0;display:flex; align-items:center;overflow-x: hidden;height: 100%;position: absolute;width: 100%;">
<div style="margin: auto !important;">
    <div class="container">
      <h1 class="text-center" style="margin-bottom: 20px"><img src="{$social_pagedata.logo_path|ezroot(no)}" alt="{$social_pagedata.site_title}" height="90" width="90"> {$social_pagedata.logo_title}</h1>
{/cache-block}
        {$module_result.content}
{cache-block keys=array( $module_result.uri )}
    </div>
    {include uri='design:page_footer_script.tpl'}

{/cache-block}
{* This comment will be replaced with actual debug report (if debug is on). *}
<!--DEBUG_REPORT-->
</div>
</body>
</html>
