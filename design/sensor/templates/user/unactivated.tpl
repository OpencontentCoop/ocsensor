<section class="hgroup">
    <h1>{'Settings'|i18n('sensor/menu')}</h1>
</section>

<div class="row">
    <div class="col-md-3">
        <ul class="nav nav-pills nav-stacked">
            {def $current_part = 'users'}
            {foreach sensor_config_menu() as $suffix => $item}
                <li role="presentation" {if $current_part|eq($suffix)}class="active"{/if}><a href="{$item.uri|ezurl(no)}">{$item.label|wash()}</a></li>
            {/foreach}
        </ul>
    </div>
    <div class="col-md-9">

        {* DO NOT EDIT THIS FILE! Use an override template instead. *}
        {def $uri = $module.functions.unactivated.uri}
        <form name="activations" method="post" action={$uri|ezurl}>
            {if and( is_set( $success_activate ), is_set( $errors_activate ) )}
                {if $success_activate}
                    <div class="alert alert-success">
                        <p>{'The following users have been successfully activated:'|i18n( 'design/admin/user/activations' )}</p>
                        <ul class="list-unstyled">
                            {foreach $success_activate as $userid}
                                {def $object = fetch( content, object, hash( 'object_id', $userid ) )}
                                <li>{$object.name|wash}</li>
                                {undef $object}
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                {if $errors_activate}
                    <div class="alert alert-danger">
                        <p>{'Some users have not been activated'|i18n( 'design/admin/user/activations' )}</p>
                    </div>
                {/if}
            {elseif and( is_set( $success_remove ), is_set( $errors_remove ) )}
                {if $success_remove}
                    <div class="alert alert-success">
                        <h2>{'The following unactivated users have been successfully removed:'|i18n( 'design/admin/user/activations' )}</h2>
                        <ul class="list-unstyled">
                            {foreach $success_remove as $name}
                                <li>{$name|wash}</li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                {if $errors_remove}
                    <div class="alert alert-danger">
                        <p>{'Some users have not been removed'|i18n( 'design/admin/user/activations' )}</p>
                    </div>
                {/if}
            {/if}

            <h3 >{'Unactivated users (%users_count)'|i18n( 'design/admin/user',, hash( '%users_count', $unactivated_count ) )}</h3>

            <p class="table-preferences">
                {switch match=$number_of_items}
                {case match=25}
                    <a href={concat( '/user/preferences/set/', $limit_preference, '/1' )|ezurl}>10</a>
                    <span class="current">25</span>
                    <a href={concat( '/user/preferences/set/', $limit_preference, '/3' )|ezurl}>50</a>
                {/case}

                {case match=50}
                    <a href={concat( '/user/preferences/set/', $limit_preference, '/1' )|ezurl}>10</a>
                    <a href={concat( '/user/preferences/set/', $limit_preference, '/2' )|ezurl}>25</a>
                    <span class="current">50</span>
                {/case}

                {case}
                    <span class="current">10</span>
                    <a href={concat( '/user/preferences/set/', $limit_preference, '/2' )|ezurl}>25</a>
                    <a href={concat( '/user/preferences/set/', $limit_preference, '/3' )|ezurl}>50</a>
                {/case}

                {/switch}
            </p>

            {if $unactivated_count}
                <table class="table table-striped">
                    <tr>
                        <th class="tight"></th>
                        <th{cond( $sort_field|eq( 'time' ), concat( ' class="sort-', $sort_order, '"' ), '' )}>
                            <a href={concat($uri, '/time/', cond( and( $sort_field|eq( 'time' ), $sort_order|eq( 'asc' ) ), 'desc', 'asc' ) )|ezurl}>{'Registration date'|i18n( 'design/admin/user' )}</a>
                        </th>
                        <th>{'Name'|i18n( 'design/admin/user' )}</th>
                        <th{cond( $sort_field|eq( 'login' ), concat( ' class="sort-', $sort_order, '"' ), '' )}>
                            <a href={concat($uri, '/login/', cond( and( $sort_field|eq( 'login' ), $sort_order|eq( 'asc' ) ), 'desc', 'asc' ) )|ezurl}>{'Login'|i18n( 'design/admin/user' )}</a>
                        </th>
                        <th{cond( $sort_field|eq( 'email' ), concat( ' class="sort-', $sort_order, '"' ), '' )}>
                            <a href={concat($uri, '/email/', cond( and( $sort_field|eq( 'email' ), $sort_order|eq( 'asc' ) ), 'desc', 'asc' ) )|ezurl}>{'E-mail'|i18n( 'design/admin/user' )}</a>
                        </th>
                    </tr>
                    {foreach $unactivated_users as $user}
                        <tr>
                            <td><input type="checkbox" name="DeleteIDArray[]" id="delete-{$user.contentobject_id}" value="{$user.contentobject_id}" /></td>
                            <td>{$user.account_key.time|l10n( 'shortdatetime' )}</td>
                            <td>{$user.contentobject.name|wash()}</td>
                            <td>{$user.login|wash()}</td>
                            <td>{$user.email|wash()}</td>
                        </tr>
                    {/foreach}
                </table>

                <div class="context-toolbar">
                    {include name=navigator
                    uri='design:navigator/google.tpl'
                    page_uri=concat( '/user/unactivated/', $sort_field, '/', $sort_order )
                    item_count=$unactivated_count
                    view_parameters=$view_parameters
                    item_limit=$number_of_items}
                </div>
            {else}
                <div class="block">
                    <p>{'There are no unactivated users'|i18n( 'design/admin/user/activations' )}</p>
                </div>
            {/if}


            {if $unactivated_count}
                <div class="clearfix">
                    <input class="btn btn-success" type="submit" name="ActivateButton" value="{'Activate selected users'|i18n( 'design/admin/user' )}" title="{'Activate selected users.'|i18n( 'design/admin/user' )}" />
                    <input class="btn btn-danger" type="submit" name="RemoveButton" value="{'Remove selected users'|i18n( 'design/admin/user' )}" title="{'Remove selected users.'|i18n( 'design/admin/user' )}" />
                </div>
            {/if}

        </form>
        {undef $uri}

    </div>
</div>