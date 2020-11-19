{if eq($current_user.contentobject_id,$anonymous_user_id)}

    {if $embed_content}
        {if is_set($parameters.post_id)}
            <div class="alert">
                <p class="lead">Per accedere alla segnalazione devi essere autenticato.</p>
            </div>
        {/if}
        {$embed_content}
    {else}
        <form method="post" action={"/user/login/"|ezurl}>
            <p>{"Click the Login button to login."|i18n("design/standard/error/kernel")}</p>
            <div class="buttonblock">
                <input class="button" type="submit" name="LoginButton"
                       value="{'Login'|i18n('design/standard/error/kernel','Button')}"/>
            </div>

            <input type="hidden" name="Login" value=""/>
            <input type="hidden" name="Password" value=""/>
            <input type="hidden" name="RedirectURI" value="{$redirect_uri}"/>
        </form>
    {/if}
{else}
    <div class="alert alert-danger">
        <p><strong>{"Access denied"|i18n("design/standard/error/kernel")}</strong></p>
        <p>{"You do not have permission to access this area."|i18n("design/standard/error/kernel")}</p>
        <ul class="list-unstyled">
            {if ne($current_user.contentobject_id,$anonymous_user_id)}
                <li>{"Your current user does not have the proper privileges to access this page."|i18n("design/standard/error/kernel")}</li>
            {else}
                <li>{"You are currently not logged in to the site, to get proper access create a new user or login with an existing user."|i18n("design/standard/error/kernel")}</li>
            {/if}
        </ul>
    </div>
{/if}
