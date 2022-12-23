{def $_redirect = '/'}

{def $tab = ''}
{if and( ezhttp_hasvariable( 'tab', 'get' ), is_set( $view_parameters.tab )|not() )}
    {set $_redirect = concat( $_redirect, '/(tab)/', ezhttp( 'tab', 'get' ) )}
{/if}

<form class="edit" enctype="multipart/form-data" method="post" action={concat("/content/edit/",$object.id,"/",$edit_version,"/",$edit_language|not|choose(concat($edit_language,"/"),''))|ezurl}>

    {if ezhttp_hasvariable( 'openpauserprofile', 'get' )}
        <div class="alert alert-warning">
            <h2>{'Complete your profile'|i18n( 'openpa_userprofile' )}</h2>
        </div>
    {/if}

    <h1>{$object.name|wash}</h1>

    {include uri="design:content/edit_validation.tpl"}

    <div class="mb-3">
          {if ezini_hasvariable( 'EditSettings', 'AdditionalTemplates', 'content.ini' )}
            {foreach ezini( 'EditSettings', 'AdditionalTemplates', 'content.ini' ) as $additional_tpl}
              {include uri=concat( 'design:', $additional_tpl )}
            {/foreach}
          {/if}

          {include uri="design:ocbootstrap/user_edit_attribute.tpl"}

          <div class="clearfix">
              <input class="btn btn-lg btn-success pull-right" type="submit" name="PublishButton" value="{'Store'|i18n('ocbootstrap')}" />
              <input class="btn btn-lg btn-dark" type="submit" name="DiscardButton" value="{'Discard'|i18n('ocbootstrap')}" />
              <input type="hidden" name="DiscardConfirm" value="0" />
              <input type="hidden" name="RedirectIfDiscarded" value="{$_redirect}" />
              <input type="hidden" name="RedirectURIAfterPublish" value="{$_redirect}" />
          </div>
    </div>
</form>

{undef $_redirect}

{run-once}
{literal}
<script>
var select_to_string = function(element) {
  var newValue = $(element).val();
  var $input = $(element).next();
  if (newValue.length > 0) {
    var current = $input.val().length > 0 ? $input.val().split(',') : [];
    current.push(newValue);
    function unique(array){
      return array.filter(function(el, index, arr) {
          return index === arr.indexOf(el);
      });
    }
    $input.val(unique(current).join(','));
  }else{
    $input.val('');
  }
}
</script>
{/literal}
{/run-once}
