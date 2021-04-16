{ezscript_require(array(
    "password-score/password-score.js",
    "password-score/password-score-options.js",
    "password-score/bootstrap-strength-meter.js",
    "password-score/password.js"
))}
{ezcss_require(array('password-score/password.css'))}

<div class="row">

<form action={concat($module.functions.password.uri,"/",$userID)|ezurl} method="post" name="Password">

    <div class="col-md-8 col-md-offset-2">
      
        <h1>Cambia la password</h1>
        
        {if $message}
            {if or($oldPasswordNotValid,$newPasswordNotMatch,$newPasswordNotValidate,$newPasswordMustDiffer)}
                {if $oldPasswordNotValid}
                    <div class="alert alert-warning">
                        Reinserisci la vecchia password
                    </div>
                {/if}
                {if $newPasswordNotMatch}
                    <div class="alert alert-warning">
                        Controlla le nuove password perché non corrispondono
                    </div>
                {/if}
                {if $newPasswordNotValidate}
                    <div class="alert alert-warning">
                        Password non valida
                    </div>
                {/if}
                {if $newPasswordMustDiffer}
                    <div class="alert alert-warning">
                        La nuova password deve essere diversa dalla vecchia
                    </div>
                {/if}                    
            {else}
                <div class="feedback">
                    {'Password successfully updated.'|i18n('mbpaex/userpaex')}
                </div>
            {/if}
        {/if}
    
    
        {if $oldPasswordNotValid}*{/if}
        <label>Vecchia password</label>
        <input type="password" class="form-control" name="oldPassword" size="11" value="{$oldPassword}" />        
        
        <hr />
    
      <div class="row">
          <div class="col-md-6">
              {if or($newPasswordNotMatch,$newPasswordNotValidate)}*{/if}
              <label>Nuova password</label>
              <input id="pwd-input" type="password" class="form-control" name="newPassword" size="11" value="{$newPassword}" />
              {include uri='design:parts/password_meter.tpl'}
          </div>
          <div class="col-md-6">
              {if or($newPasswordNotMatch,$newPasswordNotValidate)}*{/if}
              <label>Ridigita la nuova password</label>
              <input type="password" class="form-control" name="confirmPassword" size="11" value="{$confirmPassword}" />
              
          </div>          
      </div>

      <hr />
    
      <div class="buttonblock">
          <input class="btn btn-default pull-left" type="submit" name="CancelButton" value="Annulla" />
          <input class="btn btn-primary pull-right" type="submit" name="OKButton" value="{'OK'|i18n('mbpaex/userpaex')}" />
      </div>
    
    </div>


</form>


{literal}
<script type="text/javascript">
    $(document).ready(function() {
        $('#pwd-input').password({
            minLength:{/literal}{ezini('UserSettings', 'MinPasswordLength')}{literal},
            message: "{/literal}{'Show/hide password'|i18n('ocbootstrap')}{literal}",
            hierarchy: {
                '0': ['text-danger', "{/literal}{'Evaluation of complexity: bad'|i18n('ocbootstrap')}{literal}"],
                '10': ['text-danger', "{/literal}{'Evaluation of complexity: very weak'|i18n('ocbootstrap')}{literal}"],
                '20': ['text-warning', "{/literal}{'Evaluation of complexity: weak'|i18n('ocbootstrap')}{literal}"],
                '30': ['text-info', "{/literal}{'Evaluation of complexity: good'|i18n('ocbootstrap')}{literal}"],
                '40': ['text-success', "{/literal}{'Evaluation of complexity: very good'|i18n('ocbootstrap')}{literal}"],
                '50': ['text-success', "{/literal}{'Evaluation of complexity: excellent'|i18n('ocbootstrap')}{literal}"]
            }
        });
        $('[name="confirmPassword"]').password({
            strengthMeter:false,
            message: "{/literal}{'Show/hide password'|i18n('ocbootstrap')}{literal}"
        });
    });
</script>
{/literal}


</div>
