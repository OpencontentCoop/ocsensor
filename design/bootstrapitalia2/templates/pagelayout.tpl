{def $user_hash  = concat( $current_user.role_id_list|implode( ',' ), ',', $current_user.limited_assignment_value_list|implode( ',' ) )}
<!doctype html>
<html lang="{$site.http_equiv.Content-language|wash}">
<head>

    {if is_set( $extra_cache_key )|not}
        {def $extra_cache_key = ''}
    {/if}

    {def $theme = current_theme()
         $main_content_class = ''
         $has_container = cond(is_set($module_result.content_info.persistent_variable.has_container), true(), false())
         $has_section_menu = false()
         $has_sidemenu = false()}
    {if $has_container|not()}
        {set $main_content_class = 'container px-4 my-4'}
    {/if}

    {debug-accumulator id=page_head name=page_head}
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    {no_index_if_needed()}
    {include uri='design:page_head.tpl' canonical_url=openpacontext().canonical_url}
    {/debug-accumulator}


    {debug-accumulator id=page_head_style name=page_head_style}
    {include uri='design:page_head_style.tpl'}
    <style>
        b,strong{ldelim}font-weight:bold !important;{rdelim}
        .it-hero-wrapper h1, .it-hero-wrapper h2{ldelim}font-weight:normal !important;{rdelim}
    </style>
    {/debug-accumulator}

    {debug-accumulator id=page_head_script name=page_head_script}
    {def $scripts = array(
        'ezjsc::jquery',
        'ezjsc::jqueryUI',
        'ezjsc::jqueryio',
        'jquery.search-gui.js',
        'popper.js',
        'chosen.jquery.js',
        'jquery.opendataTools.js'
    )}
    {def $current_locale = fetch( 'content', 'locale' , hash( 'locale_code', ezini('RegionalSettings', 'Locale') ))}
    {def $moment_language = $current_locale.http_locale_code|explode('-')[0]|downcase()|extract_left( 2 )}
    {debug-log var=concat('Regional settings: ', ezini('RegionalSettings', 'Locale'), ' Http locale: ', $current_locale.http_locale_code, ' Moment: ', $moment_language) msg='Current language'}
    {if $moment_language|ne('it')}
        {set $scripts = $scripts|append(concat('datepicker/locales/', $moment_language, '.js'))}
    {/if}
    {ezscript_load($scripts)}
    {undef $scripts $current_locale $moment_language}

    {include uri='design:page_head_google_tag_manager.tpl'}
    {include uri='design:page_head_google-site-verification.tpl'}
    {/debug-accumulator}

</head>
<body class="{$theme}" style="overflow-x: hidden;"> {* todo style *}
    <script type="text/javascript">
    //<![CDATA[
    var CurrentLanguage = "{ezini( 'RegionalSettings', 'Locale' )}";
    var CurrentUserIsLoggedIn = {cond(fetch('user','current_user').is_logged_in, 'true', 'false')};
    var UiContext = "{$ui_context}";
    var UriPrefix = {'/'|ezurl()};
    {if and(openpacontext().is_edit|not(),openpacontext().is_browse|not())}
    var PathArray = [{if is_set( openpacontext().path_array[0].node_id )}{foreach openpacontext().path_array|reverse as $path}{$path.node_id}{delimiter},{/delimiter}{/foreach}{/if}];
    {/if}
    var ModuleResultUri = "{$module_result.uri|wash()}";
    $.opendataTools.settings('endpoint',{ldelim}
        'geo': '{'/opendata/api/geo/search/'|ezurl(no)}/',
        'search': '{'/opendata/api/content/search/'|ezurl(no)}/',
        'class': '{'/opendata/api/classes/'|ezurl(no)}/',
        'tags_tree': '{'/opendata/api/tags_tree/'|ezurl(no)}/',
        'fullcalendar': '{'/opendata/api/fullcalendar/search/'|ezurl(no)}/'
    {rdelim});
    var MomentDateFormat = "{'DD/MM/YYYY'|i18n('openpa/moment_date_format')}";
    var MomentDateTimeFormat = "{'DD/MM/YYYY HH:mm'|i18n('openpa/moment_datetime_format')}";
    //]]>
    </script>

    {if and(openpacontext().is_edit|not(),openpacontext().is_browse|not())}
    <header class="it-header-wrapper it-header-sticky" data-bs-toggle="sticky" data-bs-position-type="fixed" data-bs-sticky-class-name="is-sticky" data-bs-target="#header-nav-wrapper">
        {cache-block expiry=86400 ignore_content_expiry keys=array( $access_type.name, $extra_cache_key, $current_user.contentobject_id, openpaini('GeneralSettings','theme', 'default') )}
        {debug-accumulator id=page_header_service name=page_header_service}
            {def $pagedata = openpapagedata() $social_pagedata = social_pagedata()}
            {include uri='design:header/service.tpl'}
            {undef $pagedata $social_pagedata}
        {/debug-accumulator}
        {/cache-block}

        {cache-block expiry=86400 ignore_content_expiry keys=array( $access_type.name, $extra_cache_key, $user_hash, openpaini('GeneralSettings','theme', 'default') )}
        {debug-accumulator id=page_header_and_offcanvas_menu name=page_header_and_offcanvas_menu}
            {def $pagedata = openpapagedata() $social_pagedata = social_pagedata()}
            <div class="it-nav-wrapper">
                <div class="it-header-center-wrapper{if current_theme_has_variation('light_center')} theme-light{/if}">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="it-header-center-content-wrapper">
                                    {include uri='design:logo.tpl'}
                                    <div class="it-right-zone">
                                        {include uri='design:header/social.tpl' css="d-none d-lg-flex"}
                                        {include uri='design:header/search.tpl'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {include uri='design:header/navbar.tpl'}
            </div>
            {undef $pagedata $social_pagedata}
        {/debug-accumulator}
        {/cache-block}
    </header>
    {/if}

    {if $has_container|not()}<div class="{$main_content_class}">{/if}
    {$module_result.content}
    {if $has_container|not()}</div>{/if}

    {if and(openpacontext().is_login_page|not(), openpacontext().is_edit|not())}
    {debug-accumulator id=page_footer name=page_footer}
    {cache-block expiry=86400 ignore_content_expiry keys=array( $access_type.name, $has_valuation )}
        {def $pagedata = openpapagedata() $social_pagedata = social_pagedata()}
        {include uri='design:page_footer.tpl'}
        {include uri='design:page_extra.tpl'}
        {undef $pagedata $social_pagedata}
    {/cache-block}
    {/debug-accumulator}
    {/if}

    {include uri='design:page_footer_script.tpl'}

<!--DEBUG_REPORT-->
</body>
</html>
