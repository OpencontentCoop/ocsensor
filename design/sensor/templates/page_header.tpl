<header>
    <div class="container">
        <div class="navbar navbar-default" role="navigation" style="position: relative; z-index: 1300;">
            <div class="navbar-header">
                <a class="btn btn-navbar btn-default navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="nb_left pull-left">
                        <span class="fa fa-reorder"></span>
                    </span>
                    <span class="nb_right pull-right hidden-xs">Menu</span>
                </a>
                <a class="navbar-brand" href="{'/'|ezurl(no)}">
                    <img class="hidden-xs" src="{$social_pagedata.logo_path|ezroot(no)}" alt="{$social_pagedata.site_title}" height="90" width="90">
                    <span class="logo_title">{$social_pagedata.logo_title}</span>
                    <span class="logo_subtitle">{$social_pagedata.logo_subtitle}</span>
                </a>
            </div>
            {include uri='design:page_menu.tpl'}
        </div>
    </div>
</header>
{*<img alt="SensorCivico" src={"sensor_border.png"|ezimage()} style="position: absolute; top: 0; right: 0; border: 0;">*}
