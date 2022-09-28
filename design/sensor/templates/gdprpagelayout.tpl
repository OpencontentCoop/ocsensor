{def $social_pagedata = social_pagedata()}
<!DOCTYPE html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {include uri='design:page_head.tpl'}
    {include uri='design:page_head_google_tag_manager.tpl'}
</head>

<body>
<header>
    <div class="container">
        <div class="row">
            <div class="col-md-9 col-md-offset-2 col-lg-9 col-lg-offset-3">
                <div class="navbar navbar-default" role="navigation" style="position: relative; z-index: 1300;">
                    <div class="navbar-header">
                        <a class="navbar-brand" href="{'/'|ezurl(no)}">
                            <img class="hidden-xs" src="{$social_pagedata.logo_path|ezroot(no)}"
                                 alt="{$social_pagedata.site_title}" height="90" width="90">
                            <span class="logo_title">{$social_pagedata.logo_title}</span>
                            <span class="logo_subtitle">{$social_pagedata.logo_subtitle}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<div class="container my-5">
    {$module_result.content}
</div>

{* This comment will be replaced with actual debug report (if debug is on). *}
<!--DEBUG_REPORT-->
</body>
</html>
