{if is_set($prechecked)|not()}
    {def $prechecked = 'monthly'}
{/if}
<div class="panel panel-default">
    <div class="panel-heading">Intervallo di tempo</div>
    <div class="panel-body" style="max-height: 200px;overflow-y: scroll">
        <div class="radio">
            <label>
                <input type="radio" name="_interval" value="monthly" {if $prechecked|eq("monthly")} checked="checked"{/if} class="interval" /> Mensile
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="_interval" value="quarterly" {if $prechecked|eq("quarterly")} checked="checked"{/if}class="interval" /> Trimestrale
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="_interval" value="half-yearly" {if $prechecked|eq("half-yearly")} checked="checked"{/if}class="interval" /> Semestrale
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="_interval" value="yearly" {if $prechecked|eq("yearly")} checked="checked"{/if}class="interval" /> Annuale
            </label>
        </div>

        {ezscript_require( array( 'ezjsc::jquery' ) )}
        {literal}
        <script type="text/javascript">
            $(function () {
                $(document).on( 'change', 'input.interval', function (e) {
                    $('#chart-filters').trigger('sensor:charts:filterchange');
                });
            });
        </script>

    </div>
</div>