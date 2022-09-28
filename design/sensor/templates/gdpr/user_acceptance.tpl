<div class="mb-3">
    <form action="{'gdpr/user_acceptance/'|ezurl('no')}" method="post">

        <div class="row">
            <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                <h2 style="margin-bottom: 20px">{sensor_translate('user_acceptance_title', 'sensor', array(), $attribute.contentclass_attribute_name|wash())|wash()}</h2>
                <p class="lead">{sensor_translate('user_acceptance_text', 'sensor', array(), $attribute.contentclass_attribute.content.text|wash())}</p>
                <div class="form-group" style="font-size: 20px;line-height: 1.5;">
                    <div class="checkbox" style="padding-left: 30px;">
                        <label>
                            <input id="ezcoa-{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}"
                                   class="ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier}"
                                   type="checkbox"
                                   style="margin-top: 9px;margin-left: -25px;"
                                   name="ContentObjectAttribute_ocgdpr_data_int_{$attribute.id}"
                                    {$attribute.data_int|choose( '', 'checked="checked"' )}
                                    {if $attribute.content.is_current_user|not()}disabled="disabled" {/if}
                                   required="required"
                                   value="" />
                            <p style="font-weight: normal">
                                {sensor_translate('user_acceptance_pre_link_text', 'sensor', array(), ' ')|wash()}<a target="_blank" href="{$attribute.contentclass_attribute.content[$attribute.language_code].link|wash()}">{sensor_translate('user_acceptance_link_text', 'sensor', array(), $attribute.contentclass_attribute.content.link_text|wash())}</a>
                            </p>
                        </label>
                        {if $attribute.content.is_current_user|not()}
                            <p><input class="" type="submit" name="CustomActionButton[{$attribute.id}_force_reaccept]" value="{'Reset'|i18n('design/admin/user/setting')}" /></p>
                        {/if}
                    </div>
                </div>
                <div class="form-group text-right">
                    <input class="btn btn-success btn-lg" type="submit" value="{sensor_translate('user_acceptance_button_text', 'sensor', array(), 'Save'|i18n( 'design/admin/settings' ))|wash()}" />
                </div>
            </div>
        </div>
    </form>
</div>
