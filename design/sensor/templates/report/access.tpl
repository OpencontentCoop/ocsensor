{def $social_pagedata = social_pagedata()}
{ezcss_require(array('reveal.css', 'reveal-white.css'))}
{ezscript_require(array('highstock/highstock.js', 'highcharts/pareto.js', 'reveal.js'))}

<div class="reveal">
    <div class="slides">
        <section>
            <img src="{$social_pagedata.logo_path|ezroot(no)}" alt="{$social_pagedata.site_title}" height="90" width="90">
            <h4>{$report.name|wash()}</h4>
            <form class="form" method="post" autocomplete="off" style="max-width: 300px;margin: 0 auto;">
                <div class="form-group">
                    <label class="hide" for="{concat('ReportAccess-', $report.remote_id)}">{sensor_translate('Password', 'report')}</label>
                    <input class="form-control input-lg" autocomplete="new-password" type="password"
                           id="{concat('ReportAccess-', $report.remote_id)}"
                           name="{concat('ReportAccess-', $report.remote_id)}"
                           placeholder="{sensor_translate('Enter the report password', 'report')}" />
                </div>
                <button class="btn btn-lg btn-success">{sensor_translate('View the report', 'report')}</button>
            </form>
        </section>
    </div>
</div>
{literal}
<script>
    $(document).ready(function (){
        Reveal.initialize({
            center: true,
            history: false
        });
    });
</script>
{/literal}
