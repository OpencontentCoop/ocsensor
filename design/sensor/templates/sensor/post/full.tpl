{ezscript_require(array('ezjsc::jquery', 'plugins/chosen.jquery.js', 'js.cookie.js'))}
{ezcss_require('plugins/chosen.css')}
<script>{literal}$(document).ready(function () {$("select.chosen").chosen({width: '100%'});});{/literal}</script>

{if is_set( $error )}
    <div class="alert alert-danger">{$error}</div>
{else}
    <section class="hgroup">
        <h1>
            <span class="label label-primary" id="current-post-id">{$post.id}</span>
            {$post.subject|wash()}
            <small> {$post.author.name|wash()}</small>
            {if $user.permissions.can_edit}
                <a class="btn btn-warning btn-sm" href="{concat('sensor/edit/',$post.id)|ezurl(no)}"><i class="fa fa-edit"></i></a>
            {/if}
            {if $user.permissions.can_remove}
                <form method="post" action="{"content/action"|ezurl(no)}" style="display: inline">
                    <input type="hidden" name="ContentObjectID" value="{$post.id}"/>
                    <input type="hidden" name="RedirectURIAfterRemove" value="/sensor/dashboard"/>
                    <input type="hidden" name="RedirectIfCancel" value="/sensor/dashboard"/>
                    <button type="submit" class="btn btn-danger btn-sm" name="ActionRemove"><i class="fa fa-trash"></i></button>
                </form>
            {/if}
        </h1>
        <ul class="breadcrumb pull-right" id="current-post-breadcrumb">
            <li>
                <span class="label label-{$post.type.label}">{$post.type.name|wash()}</span>
                <span class="label label-{$post.status.label}">{$post.status.name|wash()}</span>
                {if $post.privacy.identifier|eq('private')}<span class="label label-{$post.privacy.label}">{$post.privacy.name|wash()}</span>{/if}
                {if $post.moderation.identifier|eq('waiting')}<span class="label label-{$post.moderation.label}">{$post.moderation.name|wash()}</span>{/if}
            </li>
        </ul>
    </section>
    <form id="sensor-post" method="post"
          action={"collaboration/action/"|ezurl} xmlns="http://www.w3.org/1999/html">
        <div class="row">
            <div class="col-md-8">

                <div class="row">
                    <div class="col-md-4">
                        <aside class="widget">
                            {include uri='design:sensor/post/map.tpl'}
                        </aside>
                    </div>
                    <div class="col-md-8" id="current-post-detail">
                        <p>{$post.description|wash()}</p>
                        {if $post.attachments|count()}
                            {foreach $post.attachments as $attachment}
                                <p><a href=""></a></p>
                            {/foreach}
                        {/if}
                        {if $post.images|count()}
                            {foreach $post.images as $image}
                                <figure>
                                    <img title="{$image.fileName}" alt="{$image.fileName}" class="img-responsive center-block" src="{$image.original.url|ezroot(no)}">
                                </figure>
                            {/foreach}
                        {/if}
                        <ul class="list-inline">
                            <li>
                                <small>
                                    <i class="fa fa-clock-o"></i> {'Pubblicata il'|i18n('sensor/post')} {$post.published|sensor_datetime('format', 'shortdatetime')}
                                </small>
                            </li>
                            {if $post.modified|sensor_datetime('gt',$post.published)}
                                <li>
                                    <small>
                                        <i class="fa fa-clock-o"></i> {'Ultima modifica del'|i18n('sensor/post')} {$post.modified|sensor_datetime('format', 'shortdatetime')}
                                    </small>
                                </li>
                            {/if}
                        </ul>
                        <ul class="list-inline">
                            {if $post.owners.participants|count()}
                                <li>
                                    <small>
                                        <i class="fa fa-user"></i> {'In carico a'|i18n('sensor/post')} {foreach $post.owners.participants as $owner}{$owner.name|wash()}{delimiter}, {/delimiter}{/foreach}
                                    </small>
                                </li>
                            {/if}
                            <li>
                                <small>
                                    <i class="fa fa-comments"></i> {$post.comments.count} {'commenti'|i18n('sensor/post')}
                                </small>
                            </li>
                            <li>
                                <small>
                                    <i class="fa fa-comment"></i> {$post.responses.count} {'risposte ufficiali'|i18n('sensor/post')}
                                </small>
                            </li>
                            {if $post.categories|count()}
                                <li>
                                    <small>
                                        <i class="fa fa-tags"></i> {foreach $post.categories as $category}{$category.name|wash()}{delimiter}, {/delimiter}{/foreach}
                                    </small>
                                </li>
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
        <input type="hidden" name="CollaborationActionCustom" value="custom"/>
        <input type="hidden" name="CollaborationTypeIdentifier" value="{sensor_collaboration_identifier()}"/>
        <input type="hidden" name="CollaborationItemID" value="{$post.internalId}"/>
    </form>
{literal}
    <script type="application/javascript">
        $(document).ready(function () {
            $(document).on('show.bs.collapse', '#collapseConversation', function (e) {
                Cookies.set('collapseConversation', 1);
            });
            $(document).on('hide.bs.collapse', '#collapseConversation', function (e) {
                Cookies.set('collapseConversation', 0);
            });
            if (Cookies.get('collapseConversation') == 1) $('#collapseConversation').addClass('in');
            $(document).on('click', "a.edit-message", function (e) {
                var id = $(this).data('message-id');
                $('#edit-message-' + id).toggle();
                $('#view-message-' + id).toggle();
                e.preventDefault();
            });
        });
        var actionStarted = false;
        $(document).on("click", ":submit", function (e) {
            var currentFormId = $(this).parents('form').attr('id');
            if (currentFormId == 'sensor-post') {
                var confirmation = true;
                if ($(this).data('confirmation')) {
                    confirmation = confirm($(this).data('confirmation'));
                }
                if (actionStarted == false && confirmation) {
                    actionStarted = true;
                    var currentAction = $(this).attr('name');
                    var currentPostId = $('#current-post-id').html();
                    var form = $('#sensor-post');
                    var data = form.serializeArray();
                    data.push({name: currentAction, value: ''});
                    $('body').css({'opacity': '0.3'});
                    $.ajax({
                        type: "POST",
                        url: form.attr('action'),
                        data: data,
                        dataType: "html",
                        success: function (response) {
                            $.get("{/literal}{'sensor/posts'|ezurl(no)}{literal}/" + currentPostId, function (post) {
                                var $post = $(post);
                                $('#current-post-detail').html($post.find('#current-post-detail').html());
                                $('#current-post-breadcrumb').html($post.find('#current-post-breadcrumb').html());
                                $('#current-post-messages').html($post.find('#current-post-messages').html());
                                $('#sidebar').html($post.find('#sidebar').html()).find('select.chosen').chosen({width: '100%'});
                                if (Cookies.get('collapseConversation') == 1) $('#collapseConversation').addClass('in');
                                $('body').css({'opacity': '1'});
                                actionStarted = false
                            });
                            $.get({/literal}{'social_user/alert'|ezurl()}{literal}, function (data) {
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
    </script>
{/literal}

{/if} {* if error *}
