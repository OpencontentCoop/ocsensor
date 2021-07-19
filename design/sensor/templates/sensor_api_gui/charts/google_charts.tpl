<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

{literal}
<script>
    google.charts.load('current', {'packages':['corechart'], 'language': 'it'});
</script>
{/literal}

{include uri=concat('design:sensor_api_gui/charts/google_charts/',$current.identifier, '.tpl') stat=$current}