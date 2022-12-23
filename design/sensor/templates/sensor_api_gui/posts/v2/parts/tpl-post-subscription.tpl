{literal}
<script id="tpl-post-subscription" type="text/x-jsrender">
    {{if capabilities.can_subscribe}}
        {{if capabilities.is_subscriber}}
        <div class="panel panel-default" style="margin-top:20px">
            <div class="panel-heading">
                <div class="media">
                    <div class="pull-right" data-action-wrapper>
                        <a href="#" class="btn button-icon btn-danger"
                           data-action="unsubscribe"
                           style="margin-left:5px"
                           title="{{:~sensorTranslate('Remove subscription')}}"><i class="fa fa-times"></i></a>
                    </div>
                    <div class="media-body">
                        <p class="comment_name">
                            <strong>{{:~sensorTranslate('You have subscribed')}}</strong>
                        </p>
                        <p>{{:~sensorTranslate('Remove subscription help text')}}</p>
                    </div>
                </div>
            </div>
        </div>
        {{else}}
        <div class="panel panel-info" style="margin-top:20px">
            <div class="panel-heading">
                <div class="media">
                    <div class="pull-right" data-action-wrapper>
                        <a href="#" class="btn button-icon btn-info"
                           data-action="subscribe"
                           style="margin-left:5px"
                           title="{{:~sensorTranslate('Add subscription')}}">
                           <i class="fa fa-handshake-o"></i>
                       </a>
                    </div>
                    <div class="media-body">
                        <p class="comment_name">
                            <strong>{{:~sensorTranslate('Add subscription')}}</strong>
                        </p>
                        <p>{{:~sensorTranslate('Add subscription help text')}}</p>
                    </div>
                </div>
            </div>
        </div>
        {{/if}}
    {{else capabilities.is_subscriber}}
        <div class="panel panel-info" style="margin-top:20px">
            <div class="panel-heading">
                <div class="media">
                    <div class="pull-right" data-action-wrapper>
                        <span class="btn button-icon btn-default"
                           style="margin-left:5px">
                           <i class="fa fa-thumbs-up"></i>
                       </a>
                    </div>
                    <div class="media-body">
                        <p class="comment_name">
                            <strong>{{:~sensorTranslate('You have subscribed')}}</strong>
                        </p>
                        <p>{{:~sensorTranslate('Subscription completed help text')}}</p>
                    </div>
                </div>
            </div>
        </div>
    {{/if}}
</script>
{/literal}
