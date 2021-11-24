{def $social_pagedata = social_pagedata()}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{attribute_view_gui attribute=$report|attribute('title')}</title>
    <meta http-equiv="Content-language" content="it-IT">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    {literal}
    <style type="text/css">
        @page {
            margin: 0;
            size: A4 landscape;
        }
        body {
            margin: 0;
            counter-reset: section;
        }
        .sheet {
            margin: 0;
            overflow: hidden;
            position: relative;
            box-sizing: border-box;
            page-break-after: always;
        }

        /** Paper sizes **/
        body.A3               .sheet { width: 297mm; height: 419mm }
        body.A3.landscape     .sheet { width: 420mm; height: 296mm }
        body.A4               .sheet { width: 210mm; height: 296mm }
        body.A4.landscape     .sheet { width: 297mm; height: 209mm }
        body.A5               .sheet { width: 148mm; height: 209mm }
        body.A5.landscape     .sheet { width: 210mm; height: 147mm }
        body.letter           .sheet { width: 216mm; height: 279mm }
        body.letter.landscape .sheet { width: 280mm; height: 215mm }
        body.legal            .sheet { width: 216mm; height: 356mm }
        body.legal.landscape  .sheet { width: 357mm; height: 215mm }

        /** Padding area **/
        .sheet.padding-10mm { padding: 10mm }
        .sheet.padding-15mm { padding: 15mm }
        .sheet.padding-20mm { padding: 20mm }
        .sheet.padding-25mm { padding: 25mm }

        /** For screen preview **/
        @media screen {
            body { background: #e0e0e0 }
            .sheet {
                background: white;
                box-shadow: 0 .5mm 2mm rgba(0,0,0,.3);
                margin: 5mm auto;
            }
        }

        /** Fix for Chrome issue #273306 **/
        @media print {
            body.A3.landscape { width: 420mm }
            body.A3, body.A4.landscape { width: 297mm }
            body.A4, body.A5.landscape { width: 210mm }
            body.A5                    { width: 148mm }
            body.letter, body.legal    { width: 216mm }
            body.letter.landscape      { width: 280mm }
            body.legal.landscape       { width: 357mm }
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
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        section:after {
            counter-increment: section;
            content: counter(section);
            white-space: nowrap;
            z-index: 20;
            position: absolute;
            bottom: 8pt;
            right: 12pt;
            width: 100%;
            text-align: right;
            font-size: 12pt;
        }
        section.mainpage:after{
            visibility: hidden;
        }
        .images{
            flex-grow: 1;
            display: flex;
        }
        .image{
            display: flex;
            align-items: center;
        }
        .object-center {
            margin: 0 auto;
            text-align: center;
        }
        img, video, iframe{
            margin: 0 auto !important;
        }
        img{
            max-width: 100%;
        }
        .text{
            font-size: .8em;
            text-align: center;
        }
        ul, ol, dl{
            text-align: left;
        }
        #debug{
            display: none;
        }
    </style>
    {/literal}
</head>
<body class="A4 landscape">
<div class="slides">
    <section class="sheet padding-10mm mainpage">
        <img src="{$social_pagedata.logo_path|ezroot(no)}" alt="{$social_pagedata.site_title}" height="90" width="90">
        <h3>{attribute_view_gui attribute=$report|attribute('title')}</h3>
        {attribute_view_gui attribute=$report|attribute('intro')}
    </section>
    {foreach $items as $index => $item}
        <section class="sheet padding-10mm">
            <h4>{attribute_view_gui attribute=$item|attribute('title')}</h4>
            {if $item|has_attribute('text')}
                <div class="text">
                    {attribute_view_gui attribute=$item|attribute('text')}
                </div>
            {/if}
            {if $item|has_attribute('images')}
                <div class="images">
                    {def $attribute = $item|attribute('images')}
                    {def $images = $item|attribute('images').content}
                    {foreach $images as $file}
                    <div class="image">
                        <img alt="{$item.name|wash()}" src="{concat( 'sensor/report/',$report_id,'?image=', $attribute.contentobject_id, '-', $attribute.id,'-', $attribute.version , '-', $file.filename )|ezurl(no)}" />
                    </div>
                    {/foreach}
                </div>
                {undef $attribute $images}
            {/if}
        </section>
    {/foreach}
</div>
</body>
</html>
<script>window.print();</script>
