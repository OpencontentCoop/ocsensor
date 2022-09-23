<div class="container">
    <section class="hgroup">
        <div id="SelectPreset" class="pull-right"></div>
        <h1>{sensor_translate('My subscriptions')}</h1>
    </section>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{sensor_translate('Proposal')}</th>
                    <th>{sensor_translate('Subscribed at')}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $subscriptions as $subscription}
                    <tr>
                        <td>
                            <a href="{concat('sensor/posts/', $subscription.postId)|ezurl(no)}">
                                {fetch(content, object, hash(object_id, $subscription.postId)).name|wash()}
                            </a>
                        </td>
                        <td>{$subscription.createdTimestamp|l10n( datetime )}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>