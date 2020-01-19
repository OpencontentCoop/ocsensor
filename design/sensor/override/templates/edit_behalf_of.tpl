{default attribute_base='ContentObjectAttribute' html_class='full' placeholder=false()}

    {def $current = false()}
    {if $attribute.data_text|ne('')}
        {set $current = fetch(content, object, hash(object_id, $attribute.data_text|int()))}
    {/if}

    <div class="input-group {if $current}hide{/if}" id="behalf-of-search">
        <input placeholder="{'Cerca per codice fiscale'|i18n('sensor/add')}"
               id="behalf-of-search-input"
               class="{$html_class}"
               type="text"
               size="70" />
        <span class="input-group-btn">
            <a id="behalf-of-create-button" href="#" class="btn btn-default"><i class="fa fa-plus"></i> {'Crea utente'|i18n('sensor/add')}</a>
      </span>
    </div>
    <div id="behalf-of-view" class="{if $current|not()}hide{/if}">
        <span>{if $current}{$current.name|wash()}{/if}</span> <i class="fa fa-times"></i>
    </div>
    <div id="behalf-of-create" class="hide">
        <div class="row form-group" style="margin-bottom: 10px">
            <div class="col-md-3">
                <label class="control-label" for="behalf-of-first_name">{'Nome'|i18n('sensor/add')}*</label>
            </div>
            <div class="col-md-9">
                <input placeholder="{'Nome'|i18n('sensor/add')}"
                       class="{$html_class}"
                       data-required="required"
                       name="first_name"
                       id="behalf-of-first_name"
                       type="text"
                       size="70" />
            </div>
        </div>
        <div class="row form-group" style="margin-bottom: 10px">
            <div class="col-md-3">
                <label class="control-label" for="behalf-of-last_name">{'Cognome'|i18n('sensor/add')}*</label>
            </div>
            <div class="col-md-9">
                <input placeholder="{'Cognome'|i18n('sensor/add')}"
                       class="{$html_class}"
                       data-required="required"
                       name="last_name"
                       id="behalf-of-last_name"
                       type="text"
                       size="70" />
            </div>
        </div>
        <div class="row form-group" style="margin-bottom: 10px">
            <div class="col-md-3">
                <label class="control-label" for="behalf-of-fiscal_code">{'Codice fiscale'|i18n('sensor/add')}*</label>
            </div>
            <div class="col-md-9">
                <input placeholder="{'Codice fiscale'|i18n('sensor/add')}"
                       class="{$html_class}"
                       type="text"
                       name="fiscal_code"
                       id="behalf-of-fiscal_code"
                       data-required="required"
                       size="70" />
            </div>
        </div>
        <div class="row form-group" style="margin-bottom: 10px">
            <div class="col-md-3">
                <label class="control-label" for="behalf-of-email">{'Email'|i18n('sensor/add')}</label>
            </div>
            <div class="col-md-9">
                <input placeholder="{'Email'|i18n('sensor/add')}"
                       class="{$html_class}"
                       name="email"
                       id="behalf-of-email"
                       type="text"
                       size="70" />
            </div>
        </div>
        <div class="row text-right">
            <div class="col-md-12">
                <a href="#" id="behalf-of-create-cancel" class="btn btn-sm btn-danger">{'Annulla'|i18n('sensor/add')}</a>
                <a href="#" id="behalf-of-create-add" class="btn btn-sm btn-success">{'Crea'|i18n('sensor/add')}</a>
            </div>
        </div>
    </div>

    <input id="behalf-of" type="hidden" name="{$attribute_base}_ezstring_data_text_{$attribute.id}" value="{$attribute.data_text|wash( xhtml )}" />
    {undef $current}
{/default}

{ezscript_require(array('jquery.opendataTools.js', 'handlebars.js', 'typeahead.bundle.js'))}
{literal}
<script>
    {/literal}$.opendataTools.settings('language', "{ezini('RegionalSettings', 'Locale')}");{literal}
    $(document).ready(function(){
        $('#behalf-of-search-input').val('').on('keyup', function(e){
            var input = $(this);
            input.val(input.val().toUpperCase());
        }).typeahead({
            minLength: 3
        }, {
            limit: 15,
            name: 'behalf-of-search-input',
            source: new Bloodhound({
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                datumTokenizer: Bloodhound.tokenizers.whitespace,
                prefetch:{
                    url: '{/literal}{"/sensor/search-user/"|ezurl(no)}{literal}',
                    cache: false,
                    transform: function (response) {
                        var data = [];
                        $.each(response, function () {
                            data.push(this.fiscal_code);
                        });
                        return data;
                    }
                },
                remote: {
                    url: '{/literal}{"/sensor/search-user"|ezurl(no)}{literal}/?q=%QUERY',
                    wildcard: '%QUERY',
                    transform: function (response) {
                        var data = [];
                        $.each(response, function () {
                            data.push(this.fiscal_code);
                        });
                        return data;
                    }
                }
            })
        }).on('typeahead:select', function(e, suggestion) {
            $.get('{/literal}{"/sensor/search-user/"|ezurl(no)}{literal}/'+suggestion, function (response) {
                if (response.totalCount > 0) {
                    $('#behalf-of-search').addClass('hide');
                    $('#behalf-of-create').addClass('hide');
                    $('#behalf-of-view').removeClass('hide').find('span').text(response.searchHits[0].metadata.name[$.opendataTools.settings('language')]);
                    $('#behalf-of').val(response.searchHits[0].metadata.id);
                }
            });
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
            e.preventDefault();
        });

        $('#behalf-of-create-button').on('click', function (e) {
            $('#behalf-of-search').addClass('hide');
            $('#behalf-of-view').addClass('hide');
            $('#behalf-of-create .form-group').removeClass('has-error');
            $('#behalf-of-create input').val('');
            $('#behalf-of-create').removeClass('hide');
            e.preventDefault();
        });

        $('#behalf-of-create-cancel').on('click', function (e) {
            $('#behalf-of-search').removeClass('hide');
            $('#behalf-of-create').addClass('hide');
            $('#behalf-of-view').addClass('hide').find('span').text('');
            e.preventDefault();
        });

        $('#behalf-of-create-add').on('click', function (e) {
            $('#behalf-of-create .form-group').removeClass('has-error');
            var isValid = true;
            var postData = {};
            $('#behalf-of-create input').each(function () {
                var that = $(this);
                if (!that.data('required') === 'required' && $.trim(that.val()) === ''){
                    that.parents('.form-group').addClass('has-error');
                    isValid = false;
                }else{
                    postData[that.attr('name')] = that.val();
                }
            });
            if (isValid){
                $.post('{/literal}{"/sensor/create-user/"|ezurl(no)}{literal}', postData, function (response) {
                    if (typeof response.error !== 'undefined') {
                        alert(response.error);
                    }else{
                        $('#behalf-of-search').addClass('hide');
                        $('#behalf-of-create').addClass('hide');
                        $('#behalf-of-view').removeClass('hide').find('span').text(response.metadata.name[$.opendataTools.settings('language')]);
                        $('#behalf-of').val(response.metadata.id);
                    }
                });
            }
            e.preventDefault();
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