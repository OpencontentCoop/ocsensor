<div class="panel panel-default">
    <div class="panel-heading">Intervallo di tempo</div>
    <div class="panel-body" style="max-height: 200px;overflow-y: scroll">
        <div class="radio">
            <label>
                <input type="radio" name="_interval" value="monthly" checked="checked" class="interval" /> Mensile
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="_interval" value="quarterly" class="interval" /> Trimestrale
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="_interval" value="half-yearly" class="interval" /> Semestrale
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="_interval" value="yearly" class="interval" /> Annuale
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