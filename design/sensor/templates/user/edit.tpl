<div class="row">
    <div class="col-sm-12 col-md-6 col-md-offset-3">
        <form action={concat($module.functions.edit.uri,"/",$userID)|ezurl} method="post" name="Edit">
            <section class="hgroup">
                <h1>{"User profile"|i18n("social_user/user_edit")}</h1>
            </section>
            <dl class="dl-horizontal">
                <dt>{"Username"|i18n("social_user/setting")}</dt>
                <dd style="margin-bottom: 10px">{$userAccount.login|wash}</dd>

                <dt>{'Email address'|i18n('social_user/signin')}</dt>
                <dd style="margin-bottom: 10px">{$userAccount.email|wash()}</dd>

                <dt>{sensor_translate('Name')}</dt>
                <dd style="margin-bottom: 10px">{$userAccount.contentobject.name|wash}</dd>

                {def $enabled_languages = sensor_settings('SiteLanguages')}
                {if count($enabled_languages)|gt(1)}
                    <dt>{sensor_translate('Favourite language')}</dt>
                    <dd style="margin-bottom: 10px">
                        <div class="input-group d-flex" id="select-locale">
                            <select class="form-control" style="max-width: 300px;display: none">
                                <option value=""></option>
                                {foreach $enabled_languages as $language}
                                    <option value="{$language|wash()}">{$language|wash()}</option>
                                {/foreach}
                            </select>
                            <span class="input-group-btn">
                                <a href="#" id="change-locale" class="btn btn-default">{sensor_translate('Store')}</a>
                            </span>
                        </div>
                    </dd>
                {/if}
                {undef $enabled_languages}
            </dl>
            <input class="button btn btn-info" type="submit" name="EditButton" value="{'Edit profile'|i18n('social_user/user_edit')}"/>
            {if fetch(user, current_user).contentobject.class_identifier|ne('user')}
            {if ezmodule( 'userpaex' )}
                {if $userAccount.password_hash|eq('')}
                    <a class="button btn btn-info" href="{'sensor/home/?p'|ezurl(no)}">{'Crea password'|i18n('social_user/user_edit')}</a>
                {else}
                    <a class="button btn btn-info" href="{concat("userpaex/password/",$userID)|ezurl(no)}">{'Change password'|i18n('social_user/user_edit')}</a>
                {/if}
            {else}
                <input class="button btn btn-info" type="submit" name="ChangePasswordButton" value="{'Change password'|i18n('social_user/user_edit')}"/>
            {/if}
            {if fetch( 'user', 'has_access_to', hash( 'module', 'content', 'function', 'dashboard' ) )}
                <a class="button btn btn-info" href="{"/content/dashboard/"|ezurl(no)}" title="Dashboard">Dashboard</a>
            {/if}
            {/if}
        </form>
    </div>
</div>

{literal}
    <script>
        $(document).ready(function (){
            var selectLocale = $('#select-locale');
            $.getJSON('/api/sensor_gui/users/current/locale', function (data){
               var locale = data.locale;
                selectLocale.find('select').val(locale).show();
           })
           $('#change-locale').on('click', function (e){
               var self = $(this);
               self.removeClass('btn-danger').removeClass('btn-success').find('i').remove();
               var locale = selectLocale.find('select').val();
               if (locale.length > 0) {
                   var csrfToken;
                   var tokenNode = document.getElementById('ezxform_token_js');
                   if ( tokenNode ){
                       csrfToken = tokenNode.getAttribute('title');
                   }
                   $.ajax({
                       type: "POST",
                       url: '/api/sensor_gui/users/current/locale/' + locale,
                       contentType: "application/json; charset=utf-8",
                       headers: {'X-CSRF-TOKEN': csrfToken},
                       dataType: "json",
                       success: function (data, textStatus, jqXHR) {
                           self.addClass('btn-success');
                           self.prepend('<i class="fa fa-check"></i> ');
                           window.setTimeout(function(){
                               self.removeClass('btn-danger').removeClass('btn-success').find('i').remove();
                           }, 1000);
                       },
                       error: function (jqXHR) {
                           var error = jqXHR.responseJSON;
                           alert(error.error_message);
                           self.addClass('btn-danger');
                           self.prepend('<i class="fa fa-times"></i> ');
                           window.setTimeout(function(){
                               self.removeClass('btn-danger').removeClass('btn-success').find('i').remove();
                           }, 1000);
                       }
                   });
               }
               e.preventDefault();
           })
        });
    </script>
{/literal}
