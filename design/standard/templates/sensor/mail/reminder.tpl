{def $social_pagedata = social_pagedata('sensor')}

{set-block scope=root variable=subject}[{$social_pagedata.site_title}] {$subject|wash()}{/set-block}
{set-block scope=root variable=body}
    <table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
        <tr>
            <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
                <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                    <tr>
                        <td align='center' valign='top'>
                            {include uri='design:sensor/mail/parts/text.tpl'}

                            <div style="text-align: left;background: #eee;padding: 5px 20px;margin: 15px 0;">
                                {if $new_user_count|gt(0)}
                                    {if $new_user_count|eq(1)}
                                        <p>
                                            {"Si è registrato un nuovo utente"|i18n('sensor/mail/post')}
                                        </p>
                                    {else}
                                        <p>
                                            {"Si sono registrati %count nuovi utenti"|i18n('sensor/mail/post', '', hash('%count', $new_user_count))}
                                        </p>
                                    {/if}
                                {/if}
                                {if $last_closed_post_count|gt(1)}
                                    <p>
                                        {"In generale sono state risolte %count segnalazioni"|i18n('sensor/mail/post', '', hash('%count', $last_closed_post_count))}
                                    </p>
                                {/if}
                                {if count($categories_with_more_posts)|gt(0)}
                                    <p>
                                        {"Le aree maggiormente interessate da nuove seganalazioni sono:"|i18n('sensor/mail/post', '', hash('%count', $last_closed_post_count))}
                                    </p>
                                    <ul>
                                        {foreach $categories_with_more_posts as $name => $count}
                                            <li style="padding: 5px 0;">{$name|wash()} ({"%count segnalazioni"|i18n('sensor/mail/post', '', hash('%count', $count))})</li>
                                        {/foreach}
                                    </ul>

                                {/if}
                                {if $last_closed_near_user}
                                    <p>
                                        {"E' stata chiusa una segnalazione vicino al tuo domicilio:"|i18n('sensor/mail/post')}
                                        <a href="https://{$social_pagedata.site_url}/sensor/posts/{$last_closed_near_user.id}{$campain}">{$last_closed_near_user.subject}</a>
                                    </p>
                                {/if}
                                {if $last_closed_user_post.totalCount > 0}
                                    <p>
                                        {"Sono state risolte le tue segnalazioni:"|i18n('sensor/mail/post')}
                                    </p>
                                    <ul>
                                        {foreach $last_closed_user_post.searchHits as $post}
                                            <li style="padding: 5px 0;"><a href="https://{$social_pagedata.site_url}/sensor/posts/{$post.id}{$campain}">{$post.subject}</a></li>
                                        {/foreach}
                                    </ul>
                                {/if}
                            </div>

                        </td>
                    </tr>
                    <tr>
                        <td align='center' bgcolor='#f90f00' valign='top'>
                            <h3><a href="https://{$social_pagedata.site_url}/sensor/posts/{$campain}" style="color: #ffffff !important">{"Vedi tutte le segnalazioni"|i18n('sensor/mail/post')}</a></h3>
                        </td>
                    </tr>
                    <tr><td><br /></td></tr>
                    <tr>
                        <td align='center' bgcolor='#f90f00' valign='top'>
                            <h3><a href="https://{$social_pagedata.site_url}/sensor/add/{$campain}" style="color: #ffffff !important">{"Fai la tua segnalazione"|i18n('sensor/mail/post')}</a></h3>
                        </td>
                    </tr>
                    <tr>
                        <td align='center' valign='top'>
                            {include uri='design:sensor/mail/parts/sensor_links.tpl'}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
{/set-block}