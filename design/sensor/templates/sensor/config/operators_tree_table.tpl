{if $operators|count()|gt(0)}
    <table class="table table-hover">
        {foreach $operators as $operator}
            {include name=usertree
            uri='design:sensor/config/walk_item_operators_table.tpl'
            item=$operator recursion=0
            redirect_if_discarded='/sensor/config/operators'
            redirect_after_publish='/sensor/config/operators'
            redirect_if_cancel='/sensor/config/operators'
            redirect_after_remove='/sensor/config/operators'
            operator_class=$operator_class}
        {/foreach}
    </table>

{literal}
    <script>
        $(document).ready(function(){

            var baseUrl = "{/literal}{'sensor/notifications'|ezurl(no)}/{literal}";

            var onOptionClick = function( event ) {
                var $target = $( event.currentTarget );
                var identifier = $target.data('identifier');
                var user = $target.data('user');
                var menu = $target.parents('.notification-dropdown-container .notification-dropdown-menu');

                $(event.target).blur();
                var enable = $(event.target).prop('checked');
                if ($(event.target).attr('type') == 'checkbox') {
                    jQuery.ajax({
                        url: baseUrl + user + '/' + identifier,
                        type: enable ? 'post' : 'delete',
                        success: function (response) {
                            buildNotificationMenu(user, menu);
                        }
                    });
                }

                event.stopPropagation();
                event.preventDefault();
            };

            var buildNotificationMenu = function(user, menu){
                menu.html('<li style="padding: 50px; text-align: center; font-size: 2em;"><i class="fa fa-gear fa-spin fa2x"></i></li>');
                $.get(baseUrl+user, function(response){
                    if (response.result && response.result == 'success'){
                        menu.html('');
                        var header = $('<li class="dropdown-header">Impostazini notifiche</>');
                        menu.append(header);
                        var add = $('<li><a href="#" class="small" data-user="'+user+'" data-identifier="all" tabIndex="-1"><input type="checkbox"/><b> Attiva tutto</b></a></li>');
                        add.find('a').on('click', function(e){onOptionClick(e)});
                        menu.append(add);
                        var remove = $('<li><a href="#" class="small" data-user="'+user+'" data-identifier="none" tabIndex="-1"><input type="checkbox"/><b> Disattiva tutto</b></a></li>');
                        remove.find('a').on('click', function(e){onOptionClick(e)});
                        menu.append(remove);
                        $.each(response.data, function(){
                            var item = $('<li><a href="#" class="small" data-user="'+user+'" data-identifier="'+this.identifier+'" tabIndex="-1"><input type="checkbox"/>&nbsp;'+this.name+'</a></li>');
                            if (this.enabled){
                                item.find('input').attr( 'checked', true );
                            }
                            item.find('a').on('click', function(e){onOptionClick(e)});
                            menu.append(item);
                        })
                    }else{
                        console.log(response);
                    }
                });
            };

            $('.notification-dropdown-container').on('show.bs.dropdown', function () {
                var user = $(this).data('user');
                var menu = $(this).find('.notification-dropdown-menu');
                buildNotificationMenu(user, menu);
            });

        })
    </script>
{/literal}
{/if}