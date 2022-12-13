<div class="it-header-navbar-wrapper{if current_theme_has_variation('light_center')} theme-light{/if} {if current_theme_has_variation('light_navbar')} theme-light-desk border-bottom{/if}">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="navbar navbar-expand-lg has-megamenu">
                    <button class="custom-navbar-toggler"
                            type="button"
                            aria-controls="main-menu"
                            aria-expanded="false"
                            aria-label="Toggle navigation"
                            data-target="#main-menu">
                        {display_icon('it-burger', 'svg', 'icon')}
                    </button>
                    <div class="navbar-collapsable" id="main-menu">
                        <div class="overlay"></div>
                        <div class="menu-wrapper">
                            <div class="close-div">
                                <button class="btn close-menu" type="button">
                                    {display_icon('it-close-circle', 'svg', 'icon')}
                                </button>
                            </div>
                            <ul class="navbar-nav">
                                {foreach $social_pagedata.menu as $item}
                                    {def $items = array($item)}
                                    {if and(is_set($item.has_children), $item.has_children)}
                                        {set $items = $item.children}
                                    {/if}
                                    {foreach $items as $item}
                                        {if or(is_set($item.highlight)|not(), and(is_set($item.highlight), $item.highlight|not()))}
                                        <li class="nav-item">
                                            <a class="main-nav-link nav-link text-truncate"
                                               href="{$item.url|ezurl(no)}"
                                               title="{$item.name|wash()}">
                                                <span>{$item.name|wash()}</span>
                                            </a>
                                        </li>
                                        {/if}
                                    {/foreach}
                                    {undef $items}
                                {/foreach}
                            </ul>
                            <ul class="navbar-nav navbar-secondary">
                                {foreach $social_pagedata.menu as $item}
                                    {def $items = array($item)}
                                    {if and(is_set($item.has_children), $item.has_children)}
                                        {set $items = $item.children}
                                    {/if}
                                    {foreach $items as $item}
                                        {if and(is_set($item.highlight), $item.highlight)}
                                            <li class="nav-item">
                                                <a class="main-nav-link nav-link text-truncate"
                                                   style="font-size: 1em !important;"
                                                   href="{$item.url|ezurl(no)}"
                                                   title="{$item.name|wash()}">
                                                    <span>{$item.name|wash()}</span>
                                                </a>
                                            </li>
                                        {/if}
                                    {/foreach}
                                    {undef $items}
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</div>