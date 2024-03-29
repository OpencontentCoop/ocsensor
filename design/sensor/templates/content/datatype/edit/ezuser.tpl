{default attribute_base=ContentObjectAttribute html_class='full' placeholder=false()}
{if $placeholder}<label>{$placeholder}</label>{/if}
<!-- {$attribute.content.contentobject_id} {$attribute.content.is_enabled} -->

{if ne( $attribute_base, 'ContentObjectAttribute' )}
    {def $id_base = concat( 'ezcoa-', $attribute_base, '-', $attribute.contentclassattribute_id, '_', $attribute.contentclass_attribute_identifier )}
{else}
    {def $id_base = concat( 'ezcoa-', $attribute.contentclassattribute_id, '_', $attribute.contentclass_attribute_identifier )}
{/if}


{* Username. *}

{if $attribute.content.has_stored_login}
    <p style="display: none"><input id="{$id_base}_login" autocomplete="off" type="text" name="{$attribute_base}_data_user_login_{$attribute.id}_stored_login" class="{$html_class}" value="{$attribute.content.login|wash()}" disabled="disabled" /></p>
    <input id="{$id_base}_login_hidden" type="hidden" name="{$attribute_base}_data_user_login_{$attribute.id}" value="{$attribute.content.login|wash()}" />
{else}
    <input autocomplete="off" placeholder="{'Username'|i18n( 'design/standard/content/datatype' )}" id="{$id_base}_login" class="{$html_class} ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier}" type="text" name="{$attribute_base}_data_user_login_{$attribute.id}" value="{$attribute.content.login|wash()}" />
{/if}

{* Email. *}
<p><input autocomplete="off" placeholder="{'Email'|i18n( 'design/standard/content/datatype' )}" id="{$id_base}_email" class="{$html_class} ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier}" type="text" name="{$attribute_base}_data_user_email_{$attribute.id}" value="{$attribute.content.email|wash( xhtml )}" /></p>

{* Email #2. Require e-mail confirmation *}
{if ezini( 'UserSettings', 'RequireConfirmEmail' )|eq( 'true' )}
<p><input autocomplete="off" placeholder="{'Confirm email'|i18n( 'design/standard/content/datatype' )}" id="{$id_base}_email_confirm" class="{$html_class} ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier}" type="text" name="{$attribute_base}_data_user_email_confirm_{$attribute.id}" value="{cond( ezhttp_hasvariable( concat( $attribute_base, '_data_user_email_confirm_', $attribute.id ), 'post' ), ezhttp( concat( $attribute_base, '_data_user_email_confirm_', $attribute.id ), 'post')|wash( xhtml ), $attribute.content.email )}" /></p>
{/if}

<input id="{$id_base}_password"  type="hidden" name="{$attribute_base}_data_user_password_{$attribute.id}" value="_ezpassword" />
<input id="{$id_base}_password_confirm" type="hidden" name="{$attribute_base}_data_user_password_confirm_{$attribute.id}" value="_ezpassword" />


{/default}
