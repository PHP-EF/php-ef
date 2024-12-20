<?php
  return '
  <div class="container">
    <div class="row">
    '.$ib->config->get('Styling')['html']['homepage'].'
    </div>
    <hr>

    <footer class="text-center">
      <div class="mb-2">
        <small>
          Designed and Supported <i class="fa fa-code" style="color:red"></i> by - <a target="_blank" rel="noopener noreferrer" href="https://github.com/TehMuffinMoo">
            Mat Cox
          </a>
        </small>
      </div>
      <div class="mb-2">
        <small>
          Running Version: '.$ib->getVersion()[0].'
          </a>
        </small>
      </div>
      <div>
      </div>
    </footer>
  </div>
</body>
</html>
';