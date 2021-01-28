<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Exporting CSV</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"
            integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu"
            crossorigin="anonymous">
    <script src={'javascript/jquery-1.10.2.min.js'|ezdesign()}></script>
    <script src={'javascript/bootstrap.min.js'|ezdesign()}></script>
    <script>
        {literal}
        $(document).ready(function(){

            var endpoint = {/literal}"{concat('/sensor/dashboard/(export)')|ezurl(no)}"{literal};

            var setProgressBar = function(data){
                var perc = parseFloat(data.iteration*100/(data.count/data.limit)).toFixed(2);
                if(perc > 100) perc = 100;
                perc += '%';
                $('.progress-bar').css('width', perc).html(perc);
            };

            var iterate = function(data){
                console.log(data);
                if (data.query != null) {
                    $.get(endpoint, data, function (response) {
                        setProgressBar(response);
                        iterate(response);
                    });
                }else{
                    $('.progress').hide();
                    $('h2').html('Il file è pronto!');
                    $('.download').attr( 'href', endpoint+'?download=1&download_id='+data.download_id).show();
                    $('.backtosite').show();
                }
            };

            iterate({/literal}{ldelim}{foreach $variables as $key => $value}'{$key}':{$value}{delimiter},{/delimiter}{/foreach}{rdelim}{literal});
        });
        {/literal}
    </script>

</head>

<body>

<div class="container">

    <div class="col-md-12">

        <h2 class="console">Attendere il caricamento dei dati...</h2>

        <div class="progress">
            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">0%</div>
        </div>

        <div class="text-center">
            <a href="#" class="download btn btn-success btn-lg" style="display: none">Scarica il file csv</a>
            <p class="backtosite" style="margin-top:20px; display: none"><a href="/" class="btn btn-info btn-lg">Torna al sito</a></p>
        </div>

    </div>


</div>


</body>
</html>
