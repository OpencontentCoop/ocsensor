<p>
    <strong>Per utenti avanzati</strong> è possibile utilizzare la grammatica <a target="_blank" href="https://jmespath.org/">JMESPath</a>
    su una <a href="#" data-toggle="modal" data-target="#jsonExamplePostModal">rappresentazione JSON più estesa</a> della segnalazione.<br>
    Ad esempio il placeholder <code>{literal}{{responses[-1].text}}{/literal}</code> viene popolato con il testo dell'ultima risposta inserita.
</p>
<div class="modal fade" id="jsonExamplePostModal" tabindex="-1" role="dialog" aria-labelledby="jsonExamplePostModal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <pre><code class="json">{$post|json_encode}</code></pre>
        </div>
    </div>
</div>