<table class="table table-striped">
    {if $root.can_edit}
        <tr>
            <th>Impostazioni generali</th>
            <td class="text-center">
                <a class="btn btn-default" href="{concat('/content/edit/', $root.contentobject_id, '/f')|ezurl(no)}">Modifica</a>
            </td>
        </tr>
    {/if}
    {if $post_container_node.can_edit}
        <tr>
            <th>Informazioni Sensor</th>
            <td class="text-center">
                <a class="btn btn-default" href="{concat('/content/edit/', $post_container_node.contentobject_id, '/f')|ezurl(no)}">Modifica</a>
            </td>
        </tr>
    {/if}
    <tr>
        <th>
            {'Riferimento per il cittadino'|i18n('sensor/config')}:
            {def $default_approvers = sensor_default_approvers()}
            {if count($default_approvers)|gt(0)}
                {foreach $default_approvers as $approver}{include uri='design:content/view/sensor_person.tpl' sensor_person=$approver}{delimiter}, {/delimiter}{/foreach}
            {/if}
            <br /><small>Con questa opzione si individua l'operatore che prende in carico in prima battuta le segnalazioni</small>
        </th>
        <td class="text-center">
            <form class="form-inline" style="display: inline" action="{'sensor/config/operators'|ezurl(no)}" method="post">
                <button class="btn btn-default" name="SelectDefaultApprover" type="submit">Cambia</button>
            </form>
        </td>
    </tr>
    <tr{if $moderation_is_enabled} class="warning"{/if}>
        <th>
            Imposta come privata ogni nuova segnalazione inserita
            <br /><small>Se l'opzione è attivata le nuove segnalazioni non sono pubblicamente visibili</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $moderation_is_enabled}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="Moderation"{else}disabled{/if}>
        </td>
    </tr>
    <tr>
        <th>
            Nascondi al segnalatore il consenso di pubblicazione
            <br /><small>Se l'opzione è attivata non viene richiesto al segnalatore il consenso di rendere pubblica la segnalazione: gli operatori non potranno in alcun modo renderla pubblica</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.HidePrivacyChoice}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="HidePrivacyChoice"{else}disabled{/if}>
        </td>
    </tr>
    <tr>
        <th>
            Nascondi al segnalatore la scelta della tipologia di segnalazione
            <br /><small>Se l'opzione è attivata non viene richiesto al segnalatore di scegliere la tipologia di segnalazione</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.HideTypeChoice}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="HideTypeChoice"{else}disabled{/if}>
        </td>
    </tr>
    <tr>
        <th>
            Visualizza l'interfaccia di inserimento ajax (sperimentale)
            <br /><small>Viene esposta al segnalatore la nuova interfaccia di inserimento</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.ShowSmartGui}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="ShowSmartGui"{else}disabled{/if}>
        </td>
    </tr>
    <tr>
        <th>
            Nascondi al pubblico la timeline dettagliata
            <br /><small>Se l'opzione è attivata verranno mostrati nella cronologia soltanto gli eventi di presa in carico e chiusura</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.HideTimelineDetails}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="HideTimelineDetails"{else}disabled{/if}>
        </td>
    </tr>
    <tr>
        <th>
            Nascondi al pubblico il nome degli operatori
            <br /><small>Se l'opzione è attivata i nomi degli operatori saranno sostituiti con una stringa generica <em>Operatore</em></small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.HideOperatorNames}checked{/if} data-toggleconfig {if $root.can_edit}data-attribute="HideOperatorNames"{else}disabled{/if}>
        </td>
    </tr>
    <tr>
        <th>
            Assegnazione automatica in base alla categoria
            <br /><small>Se l'opzione è attivata, quando viene associata una categoria, la segnalazione verrà assegnata al gruppo e agli operatori configurati nella categoria</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.CategoryAutomaticAssign}checked{/if} disabled data-toggleconfig>
        </td>
    </tr>
    {if $sensor_settings.CategoryAutomaticAssign}
        <tr>
            <th>
                Assegnazione casuale all'operatore in base alla categoria
                <br /><small>Se l'opzione è attivata, in assenza di un'indicazione esplicita di operatore di categoria, ne viene scelto uno casualmente dal gruppo di riferimento</small>
            </th>
            <td class="text-center">
                <input type="checkbox" {if $sensor_settings.CategoryAutomaticAssignToRandomOperator}checked{/if} disabled data-toggleconfig>
            </td>
        </tr>
    {/if}
    <tr>
        <th>Il segnalatore può riaprire una segnalazione chiusa</th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.AuthorCanReopen}checked{/if} disabled data-toggleconfig>
        </td>
    </tr>
    <tr>
        <th>Il riferimento può riaprire una segnalazione chiusa</th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.ApproverCanReopen}checked{/if} disabled data-toggleconfig>
        </td>
    </tr>
    {*
    <tr>
      <th>Impedisci al segnalatore di selezionare la zona</th>
      <td class="text-center">
        <input type="checkbox" {if ezini( 'SensorConfig', 'ReadOnlySelectArea', 'ocsensor.ini' )|eq('enabled')}checked{/if} disabled data-toggleconfig>
      </td>
    </tr>
    *}
    <tr>
        <th>Quando viene terminato un intervento, reimposta sempre come riferimento {foreach $default_approvers as $approver}{include uri='design:content/view/sensor_person.tpl' sensor_person=$approver}{delimiter}, {/delimiter}{/foreach}</th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.ForceUrpApproverOnFix}checked{/if} disabled data-toggleconfig>
        </td>
    </tr>
    <tr>
        <th>
            Sono permessi i commenti pubblici alle segnalazioni
            <br /><small>Se l'opzione è attivata, gli utenti autenticati possono inserire commenti alle segnalazioni pubbliche</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.CommentsAllowed}checked{/if} disabled data-toggleconfig>
        </td>
    </tr>
    <tr>
        <th>
            Destinatari esclusivi delle note private
            <br /><small>Se l'opzione è attivata, è possibile selezionare quali operatori possono leggere ciascuna nota</small>
        </th>
        <td class="text-center">
            <input type="checkbox" {if $sensor_settings.UseDirectPrivateMessage}checked{/if} disabled data-toggleconfig>
        </td>
    </tr>
</table>