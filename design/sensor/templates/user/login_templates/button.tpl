{default header_tag='h1'}
<{$header_tag} class='text-center title'>{sensor_translate(ezini(concat('LoginTemplate_', $login_module_setting), 'Title', 'app.ini'), 'login')}</{$header_tag}>

{if ezini_hasvariable(concat('LoginTemplate_', $login_module_setting), 'Text', 'app.ini')}
    <p class="text-center" style="margin-bottom: 20px">
        {sensor_translate(ezini(concat('LoginTemplate_', $login_module_setting), 'Text', 'app.ini'), 'login')}
    </p>
{/if}

<div class="text-center">
    <a href="{ezini(concat('LoginTemplate_', $login_module_setting), 'LinkHref', 'app.ini')|ezurl(no)}" class="btn btn-lg btn-primary">
        {sensor_translate(ezini(concat('LoginTemplate_', $login_module_setting), 'LinkText', 'app.ini'), 'login')}
    </a>
</div>
