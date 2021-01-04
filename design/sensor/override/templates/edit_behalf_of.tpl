{default attribute_base='ContentObjectAttribute' html_class='full' placeholder=false()}

    {def $current = false()}
    {if $attribute.data_text|ne('')}
        {set $current = fetch(content, object, hash(object_id, $attribute.data_text|int()))}
    {/if}

    <div class="{if $current}hide{/if}" id="behalf-of-search">
        <div class="input-group">
            <input placeholder="{'Cerca tra gli utenti registrati'|i18n('sensor/add')}"
                   id="behalf-of-search-input"
                   class="{$html_class}"
                   type="text"
                   size="70" />
            <span class="input-group-btn">
                <a id="behalf-of-create-button" href="#" class="btn btn-default"><i class="fa fa-plus"></i> {'Crea utente'|i18n('sensor/add')}</a>
            </span>
        </div>
        <div class="checkbox" style="width: 100%;">
            <label>
                <input type="checkbox" id="behalf-of-anonymous" data-userid="{ezini('UserSettings', 'AnonymousUserID')}"> Non si dispongono informazioni sul segnalatore
            </label>
        </div>
    </div>
    <div id="behalf-of-view" class="{if $current|not()}hide{/if}">
        <span>{if $current}{$current.name|wash()}{/if}</span> <i class="fa fa-times"></i>
    </div>
    <div id="behalf-of-create" class="hide"></div>

    <input id="behalf-of" type="hidden" name="{$attribute_base}_ezstring_data_text_{$attribute.id}" value="{$attribute.data_text|wash( xhtml )}" />
    {undef $current}
{/default}

{ezscript_require(array(
  'jquery.opendataTools.js',
  'handlebars.min.js',
  'typeahead.bundle.js',
  'ezjsc::jqueryUI',
  'ezjsc::jqueryio',
  'jquery.opendataTools.js',
  'jsrender.js',
  'alpaca.js',
  concat('https://www.google.com/recaptcha/api.js?hl=', fetch( 'content', 'locale' ).country_code|downcase),
  'fields/Recaptcha.js',
  'jquery.opendatabrowse.js',
  'jquery.opendataform.js'
))}
{ezcss_load(array(
  'alpaca.min.css',
  'alpaca-custom.css'
))}
{literal}
<script>
    {/literal}$.opendataTools.settings('language', "{ezini('RegionalSettings', 'Locale')}");{literal}
    $(document).ready(function(){
        $('#behalf-of-search-input').val('').typeahead({
            minLength: 3,
            hint: false
        }, {
            limit: 15,
            name: 'behalf-of-search-input',
            source: new Bloodhound({
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                datumTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: '/api/sensor_gui/users?q=%QUERY',
                    wildcard: '%QUERY',
                    transform: function (response) {
                        console.log(response);
                        var data = [];
                        $.each(response.items, function () {
                            data.push(this);
                        });
                        return data;
                    }
                }
            }),
            templates: {
                suggestion: Handlebars.compile('<div><strong>{{name}}</strong> – {{email}} {{fiscal_code}}</div>')
            }
        }).on('typeahead:select', function(e, suggestion) {
            $('#behalf-of-search').addClass('hide');
            $('#behalf-of-create').addClass('hide');
            $('#behalf-of-view').removeClass('hide').find('span').text(suggestion.name);
            $('#behalf-of').val(suggestion.id);
            e.preventDefault();
        }).on('keydown', function(e){
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        });

        $('#behalf-of-view i').css('cursor', 'pointer').on('click', function (e) {
            $('#behalf-of-search').removeClass('hide');
            $('#behalf-of-create').addClass('hide');
            $('#behalf-of-view').addClass('hide').find('span').text('');
            $('#behalf-of').val('');
            $('#behalf-of-search-input').val('');
            $('#behalf-of-anonymous').attr('checked', false);
            e.preventDefault();
        });

        $('#behalf-of-create-button').on('click', function (e) {
            $('#behalf-of-search').addClass('hide');
            $('#behalf-of-view').addClass('hide');
            $('#behalf-of-create').removeClass('hide').opendataFormCreate({
                class: 'user',
                parent: {/literal}{ezini("UserSettings", "DefaultUserPlacement")}{literal}
            },{
                connector: 'create-user',
                onSuccess: function (response) {
                    $('#behalf-of-search').addClass('hide');
                    $('#behalf-of-create').addClass('hide');
                    $('#behalf-of-view').removeClass('hide').find('span').text(response.content.metadata.name[$.opendataTools.settings('language')]);
                    $('#behalf-of').val(response.content.metadata.id);
                },
                alpaca: {
                    'options': {
                        'form': {
                            'buttons': {
                                'submit': {
                                    'value': 'Crea',
                                    'styles': 'btn btn-sm btn-success pull-right'
                                },
                                'reset': {
                                    'click': function () {
                                        $('#behalf-of-search').removeClass('hide');
                                        $('#behalf-of-create').addClass('hide');
                                        $('#behalf-of-view').addClass('hide').find('span').text('');
                                        $('#behalf-of').val('');
                                        $('#behalf-of-search-input').val('');
                                    },
                                    'value': 'Annulla',
                                    'styles': 'btn btn-sm btn-danger pull-left'
                                }
                            }
                        }
                    }
                }
            });
            e.preventDefault();
        });
        $('#behalf-of-anonymous').attr('checked', false).on('change', function (){
            var userName = 'Utente anonimo';
            var userId = $(this).data('userid');
            if ($(this).is(':checked')) {
                $('#behalf-of-search').addClass('hide');
                $('#behalf-of-create').addClass('hide');
                $('#behalf-of-view').removeClass('hide').find('span').text(userName);
                $('#behalf-of').val(userId);
            }else{
                $('#behalf-of-search').removeClass('hide');
                $('#behalf-of-create').addClass('hide');
                $('#behalf-of-view').addClass('hide').find('span').text('');
                $('#behalf-of').val('');
                $('#behalf-of-search-input').val('');
            }
        });
    });
</script>
<style>
    span.twitter-typeahead{
        width:100%;
        vertical-align: middle;
    }
    .tt-menu{
        text-align: left;
        width:100%;
        background-color: #fff;
        border: 1px solid #ccc;
        border-top: none;
    }
    .tt-suggestion{
        padding:.5em;
    }
    .tt-cursor{
        background-color: #eee;
    }
</style>
{/literal}