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

      button.login {
        background: #00BD4D;
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

      button.sso {
        background: #7f4bd6;
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
    /* background:-webkit-linear-gradient(left,
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
        ); */
      background:-webkit-linear-gradient(left, #FDDD00 0%, #34C33D 25%, #00BD4D 50%, #00CD93 75%, #00E1E6 100%);
      background:-moz-linear-gradient(left, #FDDD00 0%, #34C33D 25%, #00BD4D 50%, #00CD93 75%, #00E1E6 100%);
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
    <button id="login" class="login"> Sign in </button>
    <?php if ($ib->config->get('SAML','enabled')) {
      echo '<button id="sso" class="sso"> Single Sign On </button>';
    }?>
    <?php
      $ib->hooks->executeHook('login_page_buttons');
    ?>
  </div>
</div>

<script>

<?php
  $ib->hooks->executeHook('login_page_js');
?>

function login() {
  queryAPI("POST", "/api/auth/login", {
      un: $('#un').val(),
      pw: $('#pw').val()
  }).done(function( data, status ) {
    if (data['result'] == 'Error') {
      toast("Authentication Error","",data['message'],"danger","30000");
    } else if (data['result'] == 'Expired') {
      toast("Password expired","","You must reset your password before logging in.","danger","30000");
      var un = $('#un').val()
      $('.login-wrap').html('');
      $('.login-wrap').html(`<h2>Password Reset</h2>
      <div class="form">
        <input type="text" value="`+un+`" name="un" id="un"/>
        <input type="password" placeholder="Current Password" name="cpw" id="cpw"/>
        <input type="password" placeholder="New Password" name="pw" id="pw"/>
        <input type="password" placeholder="New Password Again" name="pw2" id="pw2"/>
        <button id="reset" class="btn btn-success reset"> Reset </button>
      </div>`);
      $('#reset').click(function() {
        reset();
      });
      $('#un,#cpw,#pw,#pw2').keypress(function(event) {
        if (event.which == 13) {
          reset();
        }
      });
      $('#pw, #pw2').on('change', function() {
        validatePW();
      });
    } else if (data['result'] == 'Success') {
        toast("Success!","","Successfully logged in.","success","30000");
        location = "<?php echo $RedirectUri; ?>"
    }
  }).fail(function( data, status ) {
      toast("Authentication Error","","Unknown Authentication Error","danger","30000");
  })
}

function reset() {
  queryAPI("POST", "/api/auth/password/expired", {
      un: $('#un').val(),
      cpw: $('#cpw').val(),
      pw: $('#pw').val()
  }).done(function( data, status ) {
    if (data['result'] == 'Success') {
      toast("Success!","","Successfully reset password.","success","30000");
      $('.login-wrap').html('');
      $('.login-wrap').html(`<h2>Login</h2>
      <div class="form">
        <input type="text" placeholder="Username" name="un" id="un"/>
        <input type="password" placeholder="Password" name="pw" id="pw"/>
        <button id="login" class="login"> Sign in </button>
        <?php if ($ib->config->get('SAML','enabled')) {
          echo '<button id="sso" class="sso"> Single Sign On </button>';
        }?>
      </div>`);
      $('#login').click(function() {
        login();
      })
      $('#sso').click(function() {
        location = "/api/auth/sso";
      })
      $('#un,#pw').keypress(function(event) {
        if (event.which == 13) {
            login();
        }
      });
    } else if (data['result'] == 'Error') {
      toast("Error","",data['message'],"danger","30000");
    }
  }).fail(function( data, status ) {
    toast("Authentication Error","","Unknown Authentication Error","danger","30000");
  })
}
function validatePW() {
  var password = $('#pw').val();
  var confirmPassword = $('#pw2').val();

  if (password !== confirmPassword) {
    if (password !== "" && confirmPassword !== "") {
      toast("Warning","","The entered passwords do not match","danger","3000");
      $('#reset').attr('disabled',true);
      $('#pw').css('color','red').css('border-color','red');
      $('#pw2').css('color','red').css('border-color','red');
    }
  } else {
    $('#reset').attr('disabled',false);
    $('#pw').css('color','green').css('border-color','green');
    $('#pw2').css('color','green').css('border-color','green');
  }
}
$('#login').click(function() {
    login();
})
$('#sso').click(function() {
    location = "/api/auth/sso";
})
$('#un,#pw').keypress(function(event) {
  if (event.which == 13) {
      login();
  }
});
</script>