{def $social_pagedata = social_pagedata()}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    <meta charset="utf-8">
    {literal}
    <style type="text/css">
        @page {
            size: a4 landscape;
            margin: .5in;
        }
        html {
            font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 16pt;
        }
        h3 {
            text-transform: uppercase;
        }
        section {
            page-break-after: always;
            text-align: center;
        }
        .slide-content{
            text-align: left;
        }
        .object-center {
            margin: 0 auto;
            text-align: center;
        }
        img, video, iframe{
            margin: 0 auto !important;
        }
    </style>
    {/literal}
</head>
<body>
<div class="slides">
    <section>
        <img src="{$social_pagedata.logo_path|ezroot(no)}" alt="{$social_pagedata.site_title}" height="90"
             width="90">
        <h3>{attribute_view_gui attribute=$report|attribute('title')}</h3>
        {attribute_view_gui attribute=$report|attribute('intro')}
    </section>
    {foreach $items as $index => $item}
        <section>
            <h4>{attribute_view_gui attribute=$item|attribute('title')}</h4>
            <div class="slide-content">
                {if $item|has_attribute('text')}
                    {attribute_view_gui attribute=$item|attribute('text')}
                {/if}
                {if $item|has_attribute('images')}
                    {foreach $item|attribute('images').content as $file}
                        {def $attribute = $item|attribute('images')}
                        <p style="text-align: center">
                            <img src="{concat( 'sensor/report/',$report_id,'?image=', $attribute.contentobject_id, '-', $attribute.id,'-', $attribute.version , '-', $file.filename )|ezurl(no)}" />
                        </p>
                        {undef $attribute}
                    {/foreach}
                {/if}
            </div>
        </section>
    {/foreach}
</div>
</body>
</html>
<script>window.print();</script>
