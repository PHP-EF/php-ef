<?php
  $examplePlugin = new examplePlugin();
  if ($examplePlugin->auth->checkAccess("EXAMPLE-AUTH") == false) {
    die();
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