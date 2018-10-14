<form id="notificationForm" class="form" action="{'sensor/config/notifications'|ezurl(no)}" method="post">
	<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">		
	{foreach $all_languages as $language}
		<div class="panel panel-default"{if is_set($languages[$language.locale])|not()} style="display: none;"{/if}>
			<div class="panel-heading" role="tab" id="heading-{$language.locale}">
				<h4 class="panel-title">					
					<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-{$language.locale}" aria-expanded="true" aria-controls="collapse--{$language.locale}" style="display: block;color: #000 !important;cursor: pointer;">
						<i class="more-less glyphicon glyphicon-plus pull-right"></i>
						<img src="{$language.locale|flag_icon()}" /> {$language.name|wash()}
					</a>
				</h4>
			</div>
			<div id="collapse-{$language.locale}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{$language.locale}">						
				{foreach $notification_types as $notification_type}
					<div class="sub-panel" id=collapse-{$language.locale}-group>						
						<a data-toggle="collapse" data-parent="#collapse-{$language.locale}-group" href="#collapse-{$language.locale}-{$notification_type.identifier}" class="panel-body" style="display: block; color: #000;cursor: pointer;">
							<strong>{$notification_type.name|wash()}</strong>
						</a>						
						<div id="collapse-{$language.locale}-{$notification_type.identifier}" class="panel-collapse collapse">
							<table class="table table-striped">
							{foreach $participant_roles as $id => $participant_role}	
							{if $id|gt(1)}
								<tr>
									<td width="200px" valign="middle" style="vertical-align: middle;">
										<a target="_blank" href="#" data-toggle="modal" data-target="#preview" data-load-url="{$languages[$language.locale].url}/sensor/test_mail/post/{$sample_post_id}/{$id}/{$notification_type.identifier}/{$language.locale}">{"Notifica per"|i18n('sensor/settings')}<br /><strong>{$participant_role"|i18n('sensor/settings')}</strong></a>
									</td>
									<td>
										<p>
											<label for="{$notification_type.identifier}_role_{$id}_{$language.locale}_title">{"Titolo"|i18n('sensor/settings')}</label>
											<input id="{$notification_type.identifier}_role_{$id}_{$language.locale}_title" type="text" class="form-control" 
												   name="NotificationsText[{$notification_type.identifier}][role_{$id}][title][{$language.locale}]" value="{$texts[$notification_type.identifier][concat('role_',$id)][title][$language.locale]|wash()}" />				
										</p>
										<p>
											<label for="{$notification_type.identifier}_role_{$id}_{$language.locale}_header">{"Intestazione"|i18n('sensor/settings')}</label>
											<input id="{$notification_type.identifier}_role_{$id}_{$language.locale}_header" type="text" class="form-control" 
												   name="NotificationsText[{$notification_type.identifier}][role_{$id}][header][{$language.locale}]" value="{$texts[$notification_type.identifier][concat('role_',$id)][header][$language.locale]|wash()}" />				
										</p>
										<p>
											<label for="{$notification_type.identifier}_role_{$id}_{$language.locale}_text">{"Testo"|i18n('sensor/settings')}</label>
											<textarea for="{$notification_type.identifier}_role_{$id}_{$language.locale}_text" class="form-control" row="2" 
													  name="NotificationsText[{$notification_type.identifier}][role_{$id}][text][{$language.locale}]">{$texts[$notification_type.identifier][concat('role_',$id)][text][$language.locale]|wash()}</textarea>				
										</p>
									</td>
								</tr>
							{/if}
							{/foreach}
							</table>
						</div>
					</div>	
				{/foreach}					
			</div>
		</div>
	{/foreach}
	</div>
	<div class="clearfix" style="margin: 10px 0">
		<input type="hidden" name="StoreNotificationsText" value="1">
		<button class="btn btn-success btn-lg pull-right" name="StoreNotificationsText" type="submit">{"Salva"|i18n('sensor/settings')}</button>
	</div>
</form>
<form id="notificationFormReset" class="form" action="{'sensor/config/notifications'|ezurl(no)}" method="post">
	<button class="btn btn-danger btn-sm" name="ResetNotificationsText" type="submit">{"Reimposta i valori di default"|i18n('sensor/settings')}</button>
</form>
<div class="modal fade" tabindex="-1" role="dialog" id="preview">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body"></div>      
    </div>
  </div>
</div>

{literal}
<style>
#backgroundTable td{padding: 10px;}
</style>
<script>
$(document).ready(function(){
	function toggleIcon(e) {
    $(e.target)
        .prev('.panel-heading')
        .find(".more-less")
        .toggleClass('glyphicon-plus glyphicon-minus');
	}
	$('.panel-group').on('hidden.bs.collapse', toggleIcon);
	$('.panel-group').on('shown.bs.collapse', toggleIcon);
	$('#preview').on('show.bs.modal', function (e) {
	    var loadurl = $(e.relatedTarget).data('load-url');
	    $(this).find('.modal-body').load(loadurl);
	});
	$("#notificationForm").submit(function(e) {
    	var form = $(this);
    	var url = form.attr('action');
    	form.find('button').data('original_text', form.find('button').text()).html('<i class="glyphicon glyphicon-floppy-save"></i>');
    	$.ajax({
           type: "POST",
           url: url,
           data: form.serialize(),
           success: function(){
           		form.find('button').text(form.find('button').data('original_text'));
           }
        });
    	e.preventDefault();
	});
});
{/literal}</script>