<label class="form-group has-float-label">
    <textarea maxlength="{ezini('SensorConfig','TextMaxLength','ocsensor.ini')}"
              id="description"
              name="description"
              required
              class="form-control"
              data-limit="{ezini('SensorConfig','TextMaxLength','ocsensor.ini')}"
              placeholder="Dettagli della segnalazione (massimo {ezini('SensorConfig','TextMaxLength','ocsensor.ini')} caratteri)"
              tabindex="3"
              cols="70" rows="11"></textarea>
    <span>Specifica i dettagli della segnalazione</span>
</label>
<p id="description-counter" class="text-muted post-help">
    Hai a disposizione <span>{ezini('SensorConfig','TextMaxLength','ocsensor.ini')}</span> caratteri su {ezini('SensorConfig','TextMaxLength','ocsensor.ini')}
</p>
<script type="text/javascript">
    {literal}
    $(document).ready(function () {
        $('#description').keyup(function () {
            var left = $(this).data('limit') - $(this).val().length;
            if (left < 0) {
                left = 0;
            }
            $('#description-counter span').text(left);
        }).trigger('keyup');
    });
    {/literal}
</script>