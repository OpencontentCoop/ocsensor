{literal}
<script id="tpl-alerts" type="text/x-jsrender">
<section id="social_user_alerts" class="top_bar animated slideInDown">
    <div class="container">
        <div class="row">
            <div class="col-xs-11 tob_bar_right_col errorList" style="text-align: left">
                <p style="line-height: 1.7;padding: 11px 0;">{{:error_message}}</p>
            </div>
            <div class="col-xs-1 tob_bar_right_col">
                <a href="#" onclick="(function(){var elem = document.querySelector('#social_user_alerts');elem.parentNode.removeChild(elem);return false;})();return false;" style="display:block; margin: 10px 0;color: #fff">
                    <i class="fa fa-times"></i>
                </a>
            </div>
        </div>
    </div>
</section>
</script>
{/literal}