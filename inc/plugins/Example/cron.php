<?php
$scheduler->call(function() {
    echo "Hello World";
    ob_flush();
})->at('*/30 * * * *'); ## Every 30 minutes