<div class="row">
    <div class="col-xs-12 col-md-12">
      <div class="well">
        <div class="comment_name"> <small>{'RISPOSTA DEL RESPONSABILE'|i18n('sensor/messages')}</small></div>
        <div class="comment_date"><i class="fa-time"></i>
            {$message.published|sensor_datetime('format', 'shortdatetime')}
        </div>
        <div class="the_comment">
            <p>{$message.text|nl2br|autolink()}</p>
        </div>
      </div>
    </div>
</div>