<?php
  $examplePlugin = new examplePlugin();
  if ($examplePlugin->auth->checkAccess($examplePlugin->auth->checkAccess('Plugins','Example')['ACL-READ'] ?? null) == false) {
    $ib->api->setAPIResponse('Error','Unauthorized',401);
    return false;
  }
  return <<<EOF
  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <center>
              <h4>Example Page</h4>
              <p>Some description.</p>
            </center>
          </div>
        </div>
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="container">
              <div class="row justify-content-center">

                <p>Some Content</p>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <br>
  </section>
EOF;