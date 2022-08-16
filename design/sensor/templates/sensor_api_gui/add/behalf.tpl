<div class="post-subject hide">
    <label class="form-group has-float-label">
        <select id="behalf-of-channel" name="channel" class="form-control" tabindex="1">
            <option></option>
            {foreach sensor_channels() as $channel}
                <option value="{$channel|wash()}">
                    {$channel|wash()}
                </option>
            {/foreach}
        </select>
        <span>Scegli il canale</span>
    </label>
</div>

<label class="form-group has-float-label">
    <span>{sensor_translate('Search or create user')}</span>
</label>
<div id="behalf-of-search">
    <div class="input-group">
        <input placeholder="{sensor_translate('Search registered user')}"
               id="behalf-of-search-input"
               class="form-control"
               type="text"
               size="70" />
        <span class="input-group-btn">
            <a id="behalf-of-create-button" href="#" class="btn btn-default"><i class="fa fa-plus"></i> {sensor_translate('Create user')}</a>
        </span>
    </div>
    <div class="checkbox hide" style="width: 100%;">
        <label>
            <input type="checkbox" id="behalf-of-anonymous" data-userid="{ezini('UserSettings', 'AnonymousUserID')}"> {sensor_translate('There is no information about the reporter')}
        </label>
    </div>
</div>
<div id="behalf-of-view" class="hide">
    <p class="lead"><span></span> <i class="fa fa-times"></i></p>
</div>
<div id="behalf-of-create" class="hide"></div>

<input id="behalf-of" type="hidden" name="author" value="" />

{literal}
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
