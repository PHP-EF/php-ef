<?php
  $dashboard = $phpef->dashboard;
  return '
  <div class="container">
    <div class="row">
        '.$dashboard->render(['customHTML','customWidget']).'
    </div>
  </div>
</body>
</html>
';