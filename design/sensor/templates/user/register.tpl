{ezscript_require(array(concat('https://www.google.com/recaptcha/api.js?hl=', fetch( 'content', 'locale' ).country_code|downcase)))}
<div class="row">
    <div class="col-md-6 col-md-offset-3">

        <form enctype="multipart/form-data" action={"/user/register/"|ezurl} method="post" name="Register"
              class="form-signin">

            <h1 class="container-title">{"Register user"|i18n("design/ocbootstrap/user/register")}</h1>


            {if and( and( is_set( $checkErrNodeId ), $checkErrNodeId ), eq( $checkErrNodeId, true() ) )}
                <div class="alert alert-danger">
                    <h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {$errMsg}</h2>
                </div>
            {/if}

            {if $validation.processed}

                {if or($validation.attributes|count|gt(0), $validation.custom_rules|count()|gt(0))}
                    <div class="alert alert-danger text-center">

                        <h4><i class="fa fa-exclamation-triangle"></i>
                            <strong>{"Input did not validate"|i18n("design/ocbootstrap/user/register")}</strong></h4>
                        <ul class="list-unstyled">
                            {foreach $validation.attributes as $attribute}
                                <li>{$attribute.name}: {$attribute.description}</li>
                            {/foreach}
                            {foreach $validation.custom_rules as $item}
                                <li><strong>{$item.text|autolink|nl2br}</strong></li>
                            {/foreach}
                        </ul>
                    </div>
                {else}
                    <div class="alert alert-success">
                        <p><strong>{"Input was stored successfully"|i18n("design/ocbootstrap/user/register")}</strong>
                        </p>
                    </div>
                {/if}

            {/if}

            {if count($content_attributes)|gt(0)}
                {foreach $content_attributes as $attribute}
                    {if $attribute.contentclass_attribute.category|eq('hidden')}<div style="display:none">{else}<div style="margin-bottom: 30px">{/if}
                        {if $attribute.contentclass_attribute.description} <span class="help-block">{first_set( $attribute.contentclass_attribute.descriptionList[ezini('RegionalSettings', 'Locale')], $attribute.contentclass_attribute.description)|wash}</span>{/if}
                        <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}"/>
                        <p>{attribute_edit_gui attribute=$attribute html_class="form-control input-lg" placeholder=$attribute.contentclass_attribute.name}</p>
                    </div>
                {/foreach}
                <div class="buttonblock">
                    <input type="hidden" name="UserID" value="{$content_attributes[0].contentobject_id}"/>
                    {if and( is_set( $checkErrNodeId ), $checkErrNodeId )|not()}
                        <input class="btn btn-lg btn-primary pull-right" type="submit" id="PublishButton"
                               name="PublishButton" value="{'Register'|i18n('design/ocbootstrap/user/register')}"
                               onclick="window.setTimeout( disableButtons, 1 ); return true;"/>
                    {else}
                        <input class="btn btn-lg btn-info pull-right" type="submit" id="PublishButton"
                               name="PublishButton" disabled="disabled"
                               value="{'Register'|i18n('design/ocbootstrap/user/register')}"
                               onclick="window.setTimeout( disableButtons, 1 ); return true;"/>
                    {/if}
                    <input class="btn btn-lg btn-info pull-left" type="submit" id="CancelButton" name="CancelButton"
                           value="{'Discard'|i18n('design/ocbootstrap/user/register')}"
                           onclick="window.setTimeout( disableButtons, 1 ); return true;"/>
                </div>
            {else}
                <div class="alert alert-danger">
                    <p>{"Unable to register new user"|i18n("design/ocbootstrap/user/register")}</p>
                </div>
                <input class="btn btn-primary" type="submit" id="CancelButton" name="CancelButton"
                       value="{'Back'|i18n('design/ocbootstrap/user/register')}"
                       onclick="window.setTimeout( disableButtons, 1 ); return true;"/>
            {/if}
        </form>
    </div>
</div>

{literal}
    <script type="text/javascript">
        function disableButtons() {
            document.getElementById('PublishButton').disabled = true;
            document.getElementById('CancelButton').disabled = true;
        }
    </script>
{/literal}
