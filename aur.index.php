<!DOCTYPE html>
<html>
<head>
  <title>Awesome user registry</title>
  <script type="text/javascript">
    function foo(username, password) {
        if (username == "admin" && password == "password") {
            window.location = "user.php";
        } else {
            window.location = "index.php";
        }
    }
  </script>
  <style>
    label {display: block;}
  </style>
</head>
<body>
  <h1>Awesome user registry</h1>
  <form name="login_form" onsubmit="foo(this.username_field.value, this.password_field.value); 
return false;">
    <label for="username_field">Username</label>
    <input id="username_field" type="text">
    <label for="password_field">Password</label>
    <input id="password_field" type="password">
    <input id="login_button" type="submit" value="Log in">
  </form>
</body>
</html>
