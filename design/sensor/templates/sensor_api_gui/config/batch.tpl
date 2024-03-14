<section>
    <table class="table table-striped" cellspacing="0">
        <thead>
        <tr>
            <th>Type</th>
            <th>User</th>
            <th>Requested</th>
            <th>Status</th>
            <th>Progress</th>
            <th>Duration</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {foreach $operations as $import}
            <tr>
                <td>
                    {$import.handler_name}
                    <div style="font-size:.7em; max-width: 300px; overflow: auto">{$import.options|nl2br}</div>
                </td>
                <td>{$import.user.login}</td>
                <td>{$import.requested_time|l10n( 'shortdatetime' )}</td>
                <td>
                    {$import.status_string|wash()}
                </td>
                <td>
                    {$import.percentage}%<br />
                    <small>{$import.progression_notes|wash()}</small>
                </td>
                <td>{$import.process_time_formated.hour}h {$import.process_time_formated.minute}min {$import.process_time_formated.second}sec</td>
                <td>
                    {if or($import.status|eq(2),$import.status|eq(4),$import.status|eq(5))}
                        <a class="btn btn-danger" href="{concat('/sensor/config/batch_rerun/?rerun=', $import.id)|ezurl(no)}"><i class="fa fa-refresh"></i></a>
                    {/if}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {include name=navigator uri='design:navigator/google.tpl'
             page_uri='/sensor/config/batch'
             item_count=$operation_count
             view_parameters=$view_parameters
             item_limit=$limit}
</section>
