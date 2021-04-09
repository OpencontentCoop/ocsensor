<table class="table table-striped">
    <tr>
        <th></th>
        {foreach $scopes as $scope => $scope_name}
            <th>{$scope_name|wash()}</th>
        {/foreach}
    </tr>
    {foreach $stats as $stat}
        <tr>
            <th>{$stat.name|wash()}</th>
            {foreach $scopes as $scope => $scope_name}
            <td>
                <input type="checkbox"
                       {if $current_accesses[$scope]|contains($stat.identifier)}checked{/if}
                       data-attribute="stat-access-{$scope|wash()}-{$stat.identifier|wash()}"
                       data-toggleconfig>
            </td>
            {/foreach}
        </tr>
    {/foreach}
</table>