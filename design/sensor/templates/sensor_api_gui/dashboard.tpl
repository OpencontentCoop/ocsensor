{if $current_user_has_notifications|not()}
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle"></i>
        {"Attenzione: non hai nessuna notifica attiva e quindi non riceverai alcuna mail. Puoi attivare le notifiche nella pagina dedicata alle <a href='%notification_url'>impostazioni delle notifiche</a>."|i18n('sensor/dashboard', '', hash( '%notification_url', 'notification/settings'|ezurl(no) ))}
    </div>
{/if}
<section class="hgroup">
    <div class="row">
        <div class="col-md-8">
            <h1>
                {"Le mie segnalazioni"|i18n('sensor/dashboard')}
                {if $simplified_dashboard|not()}<br /><small>{"Segnalazioni da leggere, in corso e chiuse"|i18n('sensor/dashboard')}</small>{/if}
            </h1>
        </div>
        <div class="col-md-4">
            <small>
                <strong>{"Legenda:"|i18n('sensor/dashboard')}</strong><br />
                <i class="fa fa-comments-o"></i> {"indica la presenza di commenti"|i18n('sensor/dashboard')} <br />
                {if $simplified_dashboard|not()}
                    <i class="fa fa-commenting-o"></i> {"indica la presenza di commenti in attesa di moderazione"|i18n('sensor/dashboard')} <br />
                    <i class="fa fa-comments"></i> {"indica la presenza di messaggi privati"|i18n('sensor/dashboard')} <br />
                {/if}
                <i class="fa fa-exclamation-triangle"></i> {"indica la presenza di variazioni in cronologia non lette"|i18n('sensor/dashboard')}
            </small>
        </div>
    </div>
</section>

{if $simplified_dashboard}
    {include uri='design:sensor_api_gui/dashboard/parts/simplified_dashboard.tpl'}
{else}
    {include uri='design:sensor_api_gui/dashboard/parts/full_dashboard.tpl'}
{/if}