{if sensor_settings('HidePrivacyChoice')}
    <input type="hidden" name="is_private" value="1" />
{else}
    <style>
        {literal}
        label.btn span {
            font-size: 1.5em ;
        }
        label input[type="radio"] ~ i.fa.fa-circle-o{
            display: inline;
        }
        label input[type="radio"] ~ i.fa.fa-check-circle-o{
            display: none;
        }
        label input[type="radio"]:checked ~ i.fa.fa-circle-o{
            display: none;
        }
        label input[type="radio"]:checked ~ i.fa.fa-check-circle-o{
            display: inline;
        }
        div[data-toggle="buttons"] label {
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 11px;
            font-weight: normal;
            line-height: 2em;
            text-align: left;
            white-space: nowrap;
            vertical-align: top;
            cursor: pointer;
            background-color: none;
            border: 0 solid
            #c8c8c8;
            border-radius: 3px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            -o-user-select: none;
            user-select: none;
        }

        div[data-toggle="buttons"] label:active, div[data-toggle="buttons"] label.active {
            -webkit-box-shadow: none;
            box-shadow: none;
        }
        {/literal}
    </style>
    <p>
        <strong style="margin-bottom: 10px">Consenti la pubblicazione di questa segnalazione::</strong>
    </p>
    <div class="btn-group" data-toggle="buttons">
        <label class="btn"><input type="radio" name="is_private" value="1"><i class="fa fa-circle-o fa-2x"></i><i class="fa fa-check-circle-o fa-2x"></i><span> Sì</span></label>
        <label class="btn"><input type="radio" name="is_private" value="0"><i class="fa fa-circle-o fa-2x"></i><i class="fa fa-check-circle-o fa-2x"></i><span> No</span></label>
    </div>
{/if}