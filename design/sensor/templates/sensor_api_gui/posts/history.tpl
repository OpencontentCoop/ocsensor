<div id="post" class="post-gui" style="position: relative;min-height: 400px;">
    <section class="hgroup">
        <h1>
            <span class="label label-default">Cronologia</span>
            <a href="{concat('/sensor/posts/', $post.id)|ezurl(no)}" class="label label-primary">{$post.id|wash()}</a>
            {$post.subject|wash()}
        </h1>
        <p class="lead">
            <strong>{$post.type.name|wash()}</strong> di <a href="/sensor/user/{$post.author.id|wash()}">{$post.author.name|wash()}</a>
        </p>
    </section>

    <table class="table">
        <tbody>
            <tr>
                <th></th>
                <td>{$post.published|wash()}</td>
                <td>create</td>
                <td style="white-space: nowrap">{$post.reporter.name|wash()}</td>
                <td><em>(inserimento della segnalazione)</em></td>
            </tr>
            {foreach $messages as $message}
                <tr class="{if $message._type|eq('private')}warning{elseif $message._type|eq('public')}success{elseif $message._type|eq('system')}info{elseif $message._type|eq('audit')}active{/if}">
                    <th>{$message.id|wash()}</th>
                    <td>{$message.published|wash()}</td>
                    <td>{$message._type|wash()}</td>
                    <td style="white-space: nowrap">
                        {$message.creator.name|wash()}
                        {if and(is_set($message.receivers), $message.receivers|count())}
                            <br><em>Destinatari:</em><br>
                            {foreach $message.receivers as $receiver}{$receiver.name|wash()}{delimiter}<br />{/delimiter}{/foreach}
                        {/if}
                    </td>
                    <td>{$message.text|wash()|nl2br}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>