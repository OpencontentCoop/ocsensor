<aside class="widget" id="current-post-participants">
    <h4>{'Soggetti coinvolti'|i18n('sensor/post')}</h4>
    <dl class="dl">

        <dt>{$post.author.roleName|wash}:</dt>
        <dd>
            <ul class="list-unstyled">
                <li><small>{$post.author.name|wash()}</small></li>
            </ul>
        </dd>

        {if count($post.approvers.participants)|gt(0)}
            {foreach $post.approvers.participants as $participant}
                <dt>{$participant.roleName|wash}:</dt>
                {break}
            {/foreach}
            <dd><ul class="list-unstyled">
            {foreach $post.approvers.participants as $participant}
                <li>
                    <small>{$participant.name|wash()}</small>
                </li>
            {/foreach}
            </ul></dd>
        {/if}

        {if count($post.owners.participants)|gt(0)}
            {foreach $post.owners.participants as $participant}
                <dt>{$participant.roleName|wash}:</dt>
                {break}
            {/foreach}
            <dd><ul class="list-unstyled">
            {foreach $post.owners.participants as $participant}
                <li>
                    <small>{$participant.name|wash()}</small>
                </li>
            {/foreach}
            </ul></dd>
        {/if}

        {if count($post.observers.participants)|gt(0)}
            {foreach $post.observers.participants as $participant}
                <dt>{$participant.roleName|wash}:</dt>
                {break}
            {/foreach}
            <dd><ul class="list-unstyled">
            {foreach $post.observers.participants as $participant}
                <li>
                    <small>{$participant.name|wash()}</small>
                </li>
            {/foreach}
            </ul></dd>
        {/if}
    </dl>
</aside>