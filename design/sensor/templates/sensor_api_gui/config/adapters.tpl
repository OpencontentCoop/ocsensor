<div id="inefficiency"></div>
{literal}
    <script>
      $(document).ready(function () {
        let load = function (){
          $('#inefficiency').opendataForm({}, {
            connector: 'inefficiency',
            onSuccess: function () {
              load();
            }
          });
        }
        load();
      });
    </script>
{/literal}