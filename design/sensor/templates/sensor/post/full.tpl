{ezscript_require(array('ezjsc::jquery', 'plugins/chosen.jquery.js', 'js.cookie.js'))}
{ezcss_require('plugins/chosen.css')}
<script>{literal}$(document).ready(function(){$("select.chosen").chosen({width:'100%'});});{/literal}</script>

{if is_set( $error )}
  <div class="alert alert-danger">{$error}</div>
{else}

<section class="hgroup">
  <h1>
    <span class="label label-primary" id="current-post-id">{$sensor_post.object.id}</span>
    {$sensor_post.object.name|wash()} <small> {if $sensor_post.object|has_attribute('on_behalf_of')} {$sensor_post.object|attribute('on_behalf_of').content|wash()}{else}{$sensor_post.object.owner.name|wash()}{/if}</small>
    {if $sensor_post.object.can_edit}
      <a class="btn btn-warning btn-sm" href="{concat('sensor/edit/',$sensor_post.object.id)|ezurl(no)}"><i class="fa fa-edit"></i></a>
    {/if}
    {if $sensor_post.object.can_remove}
    <form method="post" action={"content/action"|ezurl} style="display: inline">
        <input type="hidden" name="ContentObjectID" value="{$sensor_post.object.id}" />
        <input type="hidden" name="ContentNodeID" value="{$sensor_post.object.main_node_id}" />
        <input type="hidden" name="RedirectURIAfterRemove" value="/sensor/dashboard" />
        <input type="hidden" name="RedirectIfCancel" value="/sensor/dashboard" />
        <button type="submit" class="btn btn-danger btn-sm" name="ActionRemove"><i class="fa fa-trash"></i></button>
    </form>
    {/if}
  </h1>
    <ul class="breadcrumb pull-right" id="current-post-breadcrumb">
      <li>
        <span class="label
        label-{$sensor_post.type.css_class}">{$sensor_post.type.name}</span> <span
        class="label
        label-{$sensor_post.current_object_state.css_class}">{$sensor_post.current_object_state.name}</span>
        {if $sensor_post.current_privacy_state.identifier|eq('private')}
          <span class="label
          label-{$sensor_post.current_privacy_state.css_class}">{$sensor_post.current_privacy_state.name}</span>
        {/if}
        {if $sensor_post.current_moderation_state.identifier|eq('waiting')}
          <span class="label label-{$sensor_post.current_moderation_state.css_class}">{$sensor_post.current_moderation_state.name}</span>
        {/if}
      </li>
    </ul>
</section>

<form id="sensor-post" method="post" action={"collaboration/action/"|ezurl} xmlns="http://www.w3.org/1999/html">
  <div class="row">
    <div class="col-md-8">
    
      <div class="row">
        <div class="col-md-4">
          <aside class="widget">            
            {include uri='design:sensor/post/map.tpl'}
          </aside>
        </div>
        <div class="col-md-8" id="current-post-detail">
          <p>{attribute_view_gui attribute=$sensor_post.object|attribute('description')}</p>
          {if $sensor_post.object|has_attribute('attachment')}
            <p>{attribute_view_gui attribute=$sensor_post.object|attribute('attachment')}</p>
          {/if}
          {if $sensor_post.object|has_attribute('image')}
            <figure>{attribute_view_gui attribute=$sensor_post.object|attribute('image') image_class='large' alignment=center}</figure>
          {/if}
          <ul class="list-inline">
            <li><small><i class="fa fa-clock-o"></i> {'Pubblicata il'|i18n('sensor/post')} {$sensor_post.object.published|l10n(shortdatetime)}</small></li>
            {if $sensor_post.object.modified|gt($sensor_post.object.published)}
                <li><small><i class="fa fa-clock-o"></i> {'Ultima modifica del'|i18n('sensor/post')} {$sensor_post.object.modified|l10n(shortdatetime)}</small></li>
            {/if}
          </ul>
          <ul class="list-inline">
            {if $sensor_post.current_owner}
              <li><small><i class="fa fa-user"></i> {'In carico a'|i18n('sensor/post')} {$sensor_post.current_owner}</small></li>
            {/if}
            <li><small><i class="fa fa-comments"></i> {$sensor_post.comment_count} {'commenti'|i18n('sensor/post')}</small></li>
            <li><small><i class="fa fa-comment"></i> {$sensor_post.response_count} {'risposte ufficiali'|i18n('sensor/post')}</small></li>
            {if $sensor_post.object|has_attribute( 'category' )}
              <li><small><i class="fa fa-tags"></i> {attribute_view_gui attribute=$sensor_post.object.data_map.category href=no-link}</small></li>
            {/if}
          </ul>              
        </div>        
      </div>
      
      <div class="row">
        <div class="col-md-12" id="current-post-messages">
          {include uri='design:sensor/post/post_messages.tpl'}
        </div>
      </div>

    </div>
    <div class="col-md-4" id="sidebar">

      {include uri='design:sensor/post/actions.tpl'}

      {include uri='design:sensor/post/participants.tpl'}

      {include uri='design:sensor/post/timeline.tpl'}

    
    </div>
  </div>
  <input type="hidden" name="CollaborationActionCustom" value="custom" />
  <input type="hidden" name="CollaborationTypeIdentifier" value="{sensor_collaboration_identifier()}" />
  <input type="hidden" name="CollaborationItemID" value="{$sensor_post.collaboration_item.id}" />
</form>

{literal}<script type="application/javascript">
$(document).ready(function() {
    $(document).on('show.bs.collapse','#collapseConversation',function(e){Cookies.set('collapseConversation',1);});
    $(document).on('hide.bs.collapse','#collapseConversation',function(e){Cookies.set('collapseConversation',0);});
    if(Cookies.get('collapseConversation') == 1) $('#collapseConversation').addClass( 'in' );
    $(document).on( 'click', "a.edit-message", function(e){
        var id = $(this).data('message-id');
        $('#edit-message-'+id).toggle();
        $('#view-message-'+id).toggle();
        e.preventDefault();
    });
});
var actionStarted = false;
$(document).on("click", ":submit", function(e){
    var currentFormId = $(this).parents('form').attr('id');
    if ( currentFormId == 'sensor-post' ) {
        var confirmation = true;
        if($(this).data('confirmation')){
            confirmation = confirm( $(this).data('confirmation') );
        }
        if( actionStarted == false && confirmation ) {
            actionStarted = true;
            var currentAction = $(this).attr('name');
            var currentPostId = $('#current-post-id').html();
            var form =  $('#sensor-post');
            var data = form.serializeArray();
            data.push({name:currentAction,value:''});
            $('body').css({'opacity':'0.3'});
            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: data,
                dataType: "html",
                success: function (response) {
                    $.get("{/literal}{'sensor/posts'|ezurl(no)}{literal}/"+currentPostId, function(post){
                        var $post = $(post);
                        $('#current-post-detail').html($post.find('#current-post-detail').html());
                        $('#current-post-breadcrumb').html($post.find('#current-post-breadcrumb').html());
                        $('#current-post-messages').html($post.find('#current-post-messages').html());
                        $('#sidebar').html($post.find('#sidebar').html()).find('select.chosen').chosen({width:'100%'});
                        if(Cookies.get('collapseConversation') == 1) $('#collapseConversation').addClass( 'in' );
                        $('body').css({'opacity':'1' });
                        actionStarted = false
                    });
                    $.get({/literal}{'social_user/alert'|ezurl()}{literal}, function(data){
                        var header = $('header');
                        header.find('#social_user_alerts').remove();
                        header.prepend(data);
                    });
                }
            });
        }
        e.preventDefault();
    }
});
</script>{/literal}

{/if} {* if error *}
