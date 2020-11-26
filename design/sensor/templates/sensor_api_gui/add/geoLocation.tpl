<div class="clearfix" style="position: relative">
    <div class="input-group">
        <input name="address[address]" tabindex="4" class="form-control input-lg" size="20" type="text" id="input-address"/>
        <span class="input-group-btn">
            <button tabindex="4" class="btn btn-default" type="button" id="input-address-button"
                    value="{'Find address'|i18n('extension/ezgmaplocation/datatype')}">
                <i class="fa fa-search fa-2x"></i>
            </button>
            <button tabindex="5" class="btn btn-default hidden-xs" type="button" id="mylocation-button"
                    value="{'Rileva la mia posizione'|i18n('sensor/add')}"
                    title="{'Gets your current position if your browser support GeoLocation and you grant this website access to it! Most accurate if you have a built in gps in your Internet device! Also note that you might still have to type in address manually!'|i18n('extension/ezgmaplocation/datatype')}">
                <i class="fa fa-compass fa-2x"></i>
            </button>
            <button tabindex="-1" class="btn btn-default visible-xs-inline-block" type="button"
                    id="sensor_show_map_button">
                <i class="fa fa-map fa-2x"></i>
            </button>
        </span>
    </div>
    <div class="list-group" id="input-results" style="position: absolute;z-index: 1;width: 100%;"></div>
    <input type="hidden" name="address[latitude]" value="" id="latitude" />
    <input type="hidden" name="address[longitude]" value="" id="longitude" />
    <textarea class="ezcca-sensor_post_meta" style="display: none" name="meta"></textarea>
</div>