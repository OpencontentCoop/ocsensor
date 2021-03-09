<form action={concat($module.functions.edit.uri,"/",$userID)|ezurl} method="post" name="Edit">


    <section class="hgroup">
        <h1>{"User profile"|i18n("social_user/user_edit")}</h1>
    </section>

    <dl class="dl-horizontal">

        <dt>{"Username"|i18n("social_user/setting")}</dt>
        <dd>{$userAccount.login|wash}</dd>

        <dt>{'Email address'|i18n('social_user/signin')}</dt>
        <dd>{$userAccount.email|wash()}</dd>

        <dt>{"Name and surname"|i18n("social_user/signin")}</dt>
        <dd>{$userAccount.contentobject.name|wash}</dd>

    </dl>

    <input class="button btn btn-info" type="submit" name="EditButton"
           value="{'Edit profile'|i18n('social_user/user_edit')}"/>
    {if ezmodule( 'userpaex' )}
        {if $userAccount.password_hash|eq('')}
            <a class="button btn btn-info"
               href="{'sensor/home/?p'|ezurl(no)}">{'Crea password'|i18n('social_user/user_edit')}</a>
        {else}
            <a class="button btn btn-info"
               href="{concat("userpaex/password/",$userID)|ezurl(no)}">{'Change password'|i18n('social_user/user_edit')}</a>
        {/if}
    {else}
        <input class="button btn btn-info" type="submit" name="ChangePasswordButton"
               value="{'Change password'|i18n('social_user/user_edit')}"/>
    {/if}
    {if fetch( 'user', 'has_access_to', hash( 'module', 'content', 'function', 'dashboard' ) )}
        <a class="button btn btn-info" href="{"/content/dashboard/"|ezurl(no)}" title="Dashboard">Dashboard</a>
    {/if}

</form>
