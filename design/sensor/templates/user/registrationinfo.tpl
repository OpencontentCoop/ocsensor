{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{let site_url=ezini('SiteSettings','SiteURL')}
{*
    Variables that can be set in a set-block to customize the e-mail sent :
    - subject (default is "Registration info")
    - content_type (like described in site.ini/[MailSettings].ContentType)
    - email_sender (default is site.ini/[MailSettings].AdminEmail)
*}
{set-block scope=root variable=subject}{'%1 registration info'|i18n('design/standard/user/register',,array($site_url))}{/set-block}
{'Thank you for registering at %siteurl.'|i18n('design/standard/user/register',,hash('%siteurl',$site_url))}

{'Your account information'|i18n('design/standard/user/register')}
{'Username'|i18n('design/standard/user/register')}: {$user.login}
{'Email'|i18n('design/standard/user/register')}: {$user.email}

{if and( is_set( $password ), ezini( 'UserSettings', 'PasswordInRegistrationEmail' )|eq( 'enabled' ) )}
{'Password'|i18n('design/standard/user/register')}: {$password}
{/if}


{if and( is_set( $hash ), $hash )}

{'Click the following URL to confirm your account'|i18n('design/standard/user/register')}
https://{$hostname}{concat( 'user/activate/', $hash, '/', $object.main_node_id )|ezurl(no)}

{/if}

{/let}
