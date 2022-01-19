<label class="form-group has-float-label">
    <textarea maxlength="{ezini('SensorConfig','TextMaxLength','ocsensor.ini')}"
              id="description"
              name="description"
              required
              class="form-control"
              data-limit="{ezini('SensorConfig','TextMaxLength','ocsensor.ini')}"
              placeholder="{sensor_translate('Details (maximum %count characters)', '', hash('%count', ezini('SensorConfig','TextMaxLength','ocsensor.ini')))}"
              tabindex="3"
              cols="70" rows="11"></textarea>
    <span>{sensor_translate('Describe the details of the issue')}</span>
</label>
<p id="description-counter" class="text-muted post-help">
    {sensor_translate('You have %count characters out of %total', '', hash(
        '%count', concat('<span>', ezini('SensorConfig','TextMaxLength','ocsensor.ini'), '</span>'),
        '%total', ezini('SensorConfig','TextMaxLength','ocsensor.ini')
    ))}
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
