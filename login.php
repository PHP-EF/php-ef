<?php
  require_once(__DIR__.'/inc/inc.php');

  if (isset($_REQUEST['redirect_uri'])) {
    $RedirectUri = $_REQUEST['redirect_uri'];
  } else {
    $RedirectUri = '/';
  }
?>

<style>
* { box-sizing: border-box; margin: 0; padding:0; }

html {
  background: #95a5a6;
  font-family: 'Helvetica Neue', Arial, Sans-Serif;
  
  .login-wrap {
    position: relative;
    margin: 0 auto;
    background: #ecf0f1;
    width: 350px;
    border-radius: 5px;
    box-shadow: 3px 3px 10px #333;
    padding: 15px;
    -ms-transform: translateY(50%);
    transform: translateY(50%);
    
    h2 {
      text-align: center;
      font-weight: 200;
      font-size: 2em;
      margin-top: 10px;
      color: #34495e;
    }
    
    .form {
      padding-top: 20px;
      
      input[type="text"],
      input[type="password"],
      button {
        width: 80%;
        margin-left: 10%;
        margin-bottom: 25px;
        height: 40px;
        border-radius: 5px;
        outline: 0;
        -moz-outline-style: none;
      }
      
      input[type="text"],
      input[type="password"] {
        border: 1px solid #bbb;
        padding: 0 0 0 10px;
        font-size: 14px;
        &:focus {
          border: 1px solid #3498db;
        }
      }
      
      a {
        text-align: center;
        font-size: 10px;
        color: #3498db;
        
        p{
          padding-bottom: 10px;
        }
        
      }
      
      button {
        background: #e74c3c;
        border:none;
        color: white;
        font-size: 18px;
        font-weight: 200;
        cursor: pointer;
        transition: box-shadow .4s ease;
        
        &:hover {
          box-shadow: 1px 1px 5px #555;  
        }
          
        &:active {
            box-shadow: 1px 1px 7px #222;  
        }
        
      }
      
    }
    
    &:after{
    content:'';
    position:absolute;
    top: 0;
    left: 0;
    right: 0;    
    background:-webkit-linear-gradient(left,               
        #27ae60 0%, #27ae60 20%, 
        #8e44ad 20%, #8e44ad 40%,
        #3498db 40%, #3498db 60%,
        #e74c3c 60%, #e74c3c 80%,
        #f1c40f 80%, #f1c40f 100%
        );
       background:-moz-linear-gradient(left,               
        #27ae60 0%, #27ae60 20%, 
        #8e44ad 20%, #8e44ad 40%,
        #3498db 40%, #3498db 60%,
        #e74c3c 60%, #e74c3c 80%,
        #f1c40f 80%, #f1c40f 100%
        );
      height: 5px;
      border-radius: 5px 5px 0 0;
  }
    
  }
  
}
</style>

<div class="login-wrap">
  <h2>Login</h2>
  
  <div class="form">
    <input type="text" placeholder="Username" name="un" id="un"/>
    <input type="password" placeholder="Password" name="pw" id="pw"/>
    <button id="login"> Sign in </button>
  </div>
</div>

<script>
function login() {
  $.post( "/api?function=login", {
      un: $('#un').val(),
      pw: $('#pw').val()
  }).done(function( data, status ) {
      if (data['Status'] == 'Error') {
          toast("Authentication Error","",data['Message'],"danger","30000");
      } else if (data['Status'] == 'Success') {
          toast("Success!","","Successfully logged in.","success","30000");
          location = "<?php echo $RedirectUri; ?>"
      }
  }).fail(function( data, status ) {
      toast("Authentication Error","","Unknown Authentication Error","danger","30000");
  }).always(function() {

  });
}

$('#login').click(function() {
    login();
})
$('#un,#pw').keypress(function(event) {
  if (event.which == 13) {
      login();
  }
});
</script>