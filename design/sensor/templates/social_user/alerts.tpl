{if $has_alerts}
    <section id="social_user_alerts" class="top_bar animated slideInDown">
        <div class="container">
            <div class="row">
                <div class="col-xs-11 tob_bar_right_col" style="text-align: left;margin: 10px 0;">
                    {foreach $alerts as $message}
                        {$message}{delimiter}<br />{/delimiter}
                    {/foreach}
                </div>
                <div class="col-xs-1 tob_bar_right_col">
                    <a href="#"
                       onClick="(function(){ldelim}var elem = document.querySelector('#social_user_alerts');elem.parentNode.removeChild(elem);return false;{rdelim})();return false;"
                       style="display:block; margin: 10px 0;color: #fff">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
{/if}