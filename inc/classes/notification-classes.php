<?php
class Notifications {
    use News,
    SMTP;

    private $db;
    private $core;
    private $api;
  
    public function __construct($core,$db,$api) {
      $this->db = $db;
      $this->core = $core;
      $this->api = $api;
      $this->initializeNewsDatabase();
      $this->initializeSMTPConfig();
    }
}