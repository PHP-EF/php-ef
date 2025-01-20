<?php
  return '
  <div class="container">
    <div class="row">
    '.$phpef->config->get('Styling')['html']['homepage'].'
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
          <span class="fa-solid fa-code-compare"></span> PHP-EF: v'.$phpef->getVersion()[0].' | <span class="fa-solid fa-database"></span> Database: v'.$phpef->dbHelper->getDatabaseVersion().'
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