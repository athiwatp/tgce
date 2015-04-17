<?php
  error_reporting(E_ALL);
  // request oil prices service from PTT
  $service_wsdl = "http://www.pttplc.com/webservice/pttinfo.asmx?WSDL";
  $soap_client = new SoapClient($service_wsdl);
  $params = array(
    'Language' => 'en'
  );
  $response = $soap_client->CurrentOilPrice($params);
  $oil_prices = simplexml_load_string(
    $response->CurrentOilPriceResult
    )->DataAccess;

?>
<!DOCTYPE html>
<html>
  <head>
    <title>TGCE - Transportation Gas Cost Estimator</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <link rel="stylesheet" href="lib/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/app.css">
    <script type="text/javascript" src="lib/jquery.min.js"></script>
    <script src="lib/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCZDdfdkZN2z46oh7IX-3UXMN0Hnzb0kl8&sensor=true">
    </script>
    <script type="text/javascript" src="js/app.js"></script>
  </head>
  <body>
    <div id="tools-panel">
      <form role="form">
        <h5>TGCE - Transportation Gas Cost Estimator</h5>
        <div class="form-group">
          <label for="start_pos">
            <img src="http://maps.google.com/mapfiles/ms/icons/green-dot.png" style="width: 16px; height: 16px;"/>
            Start
          </label>
          <input type="text" class="form-control input-sm" id="start_pos" placeholder="" disabled>
        </div>
        <div class="form-group">
          <label for="stop_pos">
            <img src="http://maps.google.com/mapfiles/ms/icons/red-dot.png" style="width: 16px; height: 16px;"/>
            Stop
          </label>
          <input type="text" class="form-control input-sm" id="stop_pos" placeholder="" disabled>
        </div>
        <div class="form-group">
          <label for="route_distance">Distance</label>
          <input type="text" class="form-control input-sm" id="route_distance" data-distance="0" placeholder="" disabled>
        </div>
        <div class="form-group">
          <label for="usage_rate">Average Gas Usage rate
            (<a href="#" data-toggle="tooltip" data-placement="left"
            title="This value is a number, How far (in Km) does your car can
            reach when use 1 liter of Gas. (default is 10 Km)">?</a>)
          </label>
          <div class="input-group">
            <input type="text" class="form-control input-sm" id="usage_rate" placeholder="" value="10">
            <span class="input-group-addon">Km/liter</span>
          </div>
        </div>
        <div class="form-group">
          <label for="gas_price">
            Gas prices (update <?php echo date('d M Y', time()); ?>)
            (<a href="#" data-toggle="tooltip" data-placement="left"
            title="Gas prices update day by day source from PTT http://www.pttplc.com/webservice/pttinfo.asmx">?</a>)
          </label>
          <select class="form-control" id="gas_prices">
          <?php
            foreach($oil_prices as $price) {
              if(empty($price->PRICE)) {
                continue;
              }
              echo "<option value=\"{$price->PRICE}\" data-gas-product=\"{$price->PRODUCT}\">{$price->PRODUCT} ({$price->PRICE} THB)</option>\n";
            }
          ?>
          </select>
        </div>
        <button type="button" class="btn btn-primary btn-default btn-block" id="cal_btn">Calculate</button>
        <div class="form-group">
          <div class="bg-warning" id="group-members">
            <p>
              <h5>Project on Github</h5>
              <a href="https://github.com/khasathan/tgce">https://github.com/khasathan/tgce</a>
            </p>
          </div>
        </div>
      </form>
    </div>
    <div id="map-canvas"></div>
    <div id="help">
      <button id="help-btn" class="btn btn-info btn-lg" title="Help">
        <span class="glyphicon glyphicon-question-sign"></span>
      </button>
    </div>
    <!-- result modal -->
    <div id="result-modal" class="modal fade" style="display: none;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><span class="glyphicon glyphicon-dashboard"></span> Estimated cost</h4>
          </div>
          <div class="modal-body">
            <div id="result-content">
              <div class="bg-info" style="padding: 2px;">
                <h2>
                  <span class="glyphicon glyphicon-tint"></span>
                  <strong>Cost</strong>: ~<span id="rs-cost">00.00</span> THB
                </h2>
              </div>
              <p>&nbsp;</p>
              <form class="form-horizontal" role="form">
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Distance
                  </label>
                  <div class="col-sm-7">
                    ~<span id="rs-distance"></span> Km
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Gas product
                  </label>
                  <div class="col-sm-7">
                    <span id="rs-gas-product"></span>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Gas price
                  </label>
                  <div class="col-sm-7">
                    <span id="rs-gas-price"></span> THB/Liter
                  </div>
                </div>
              </form>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- user help modal -->
    <div id="help-modal" class="modal fade" style="display: none;">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><span class="glyphicon glyphicon-question-sign"></span> User Help</h4>
          </div>
          <div class="modal-body">
            <div id="help-content" style="max-height: 450px; overflow-y: auto;">
              <h3><span class="label label-primary">1</span> Drag start and stop marker to find distance</h3>
              <p>
                Drag start marker <img src="http://maps.google.com/mapfiles/ms/icons/green-dot.png"/>
                and stop marker <img src="http://maps.google.com/mapfiles/ms/icons/red-dot.png"/>
                to anywhere that you want, the system will get latitude, longtitude and compute distance
                between them automatically.
              </p>
              <p>
                <img src="images/help/no1.png" class="img-thumbnail">
              </p>
              <h3><span class="label label-primary">2</span> Specific Gas usage rate of your car</h3>
              <p>
                The <strong>Gas usage rate</strong> number is a number, How does your car can reach
                when use 1 liter of gas. (default 10 Km)
              </p>
              <p>
                <img src="images/help/no2.png" class="img-thumbnail">
              </p>
              <h3><span class="label label-primary">3</span> Calculate and see your result</h3>
              <p>
                If you provide all of everything the system want such as distance, gas usage rate etc.
                You can click <strong>Calculate</strong> button, the system will show you a result.
              </p>
              <p>
                <img src="images/help/no3.png" class="img-thumbnail">
              </p>
            </div>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
  </body>
</html>
