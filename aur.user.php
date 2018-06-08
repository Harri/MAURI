<?php

if($_SERVER['REQUEST_METHOD']==='POST') {
  if(isset($_POST['remove'])) {
    removeUser($_POST['id']);
    header('Location: http://'.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI].'/user.php');
    exit;
  }
  else if(isset($_POST['name']) && preg_match('/^\d/', $_POST['age']) === 1) {
    if (isset($_POST['gnder']) && !isset($_POST['gender'])) {
      $_POST['gender'] = $_POST['gnder'];
    }
    else if (isset($_POST['gnder']) && isset($_POST['gender'])) {
      $_POST['gender'] = $_POST['gnder'].$_POST['gender'];
    }
    $id = addUser(
      $_POST['name'], 
      $_POST['age'],
      $_POST['location'],
      $_POST['description'],
      $_POST['gender']
    );
    header('Location: http://'.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI].'/user.php?id='.$id);
    exit;
  }
  else if (isset($_POST['name']) && preg_match('/^\d/', $_POST['age']) === 0) {
    echo getHeader();
    echo 'Age must be a number.';
    echo getAddUserForm();
    $users = listUsers();
    echo renderUserList($users);
    echo getRemoveUserForm();
    exit;
  }
}
else {
  echo getHeader();
  if(isset($_GET['id'])) {
    $user = getUser($_GET['id']);
    echo '<a href="user.php">List All Users</a>';
    echo renderUser($user);
  }
  else if(isset($_GET['reset'])) {
    $users = '';
    writeToFile($users);
  }
  else if(isset($_GET['raw'])) {
    $users = readFromFile();
    echo '<pre>';
    print_r($users);
    echo '</pre>';
  }
  else if(isset($_GET['sort'])) {
    echo getAddUserForm();
    $users = listUsers();
    $users = aasort($users, $_GET['sort']);
    echo renderUserList($users);
    $stats = getStats();
    echo renderStats($stats);
    echo getRemoveUserForm();    
  }
  else {
    echo getAddUserForm();
    $users = listUsers();
    echo renderUserList($users);
    $stats = getStats();
    echo renderStats($stats);    
    echo getRemoveUserForm();
  }
  echo getFooter();
  exit;
}


function aasort(&$array, $key) {
  $sorter=array();
  $ret=array();
  reset($array);
  foreach ($array as $ii => $va) {
    $sorter[$ii]=$va[$key];
  }
  asort($sorter);
  foreach ($sorter as $ii => $va) {
    $ret[$ii]=$array[$ii];
  }
  return $ret;
}

function addUser($name='', $age='', $location='', $description='', $gender='') {
  $user['name'] = $name;
  $user['age'] = $age;
  $user['location'] = substr($location, 0, 10);
  $user['description'] = $description;
  $user['disabled'] = 0;
  $user['gender'] = $gender;

  $users = readFromFile();
  $users[] = $user;

  writeToFile($users);

  return count($users)-1;
}

function removeUser($id) {
  $users = readFromFile();
  $users[$id]['disabled'] = 1;
  writeToFile($users);
}

function listUsers() {
  $users = readFromFile();
  $filtered = array_filter(
    $users,
    function($user) { return $user['disabled'] === 0; }
  );
  return $filtered;
}

function getUser($id) {
  $users = readFromFile();
  $user = $users[$id];
  $user['id'] = $id;
  return $user;
}

function getStats() {
  $users = readFromFile();
  $stats['male'] = getUsersInAgeGroupsByGender($users, 'male');
  $stats['female'] = getUsersInAgeGroupsByGender($users, 'female');
  $stats['other'] = getUsersInAgeGroupsByGender($users, 'other');
  return $stats;
}

function getUsersInAgeGroupsByGender($users, $gender) {
  $ageGroups = array(17, 55, 65);
  $underaged = array();
  $neutral = array();
  $closeToRetirement = array();
  $retirement = array();

  foreach ($users as $user) {
    $age = $user['age'];
    $userGender = $user['gender'];
    if ($userGender === $gender && is_numeric($age)) {
      if ($age <= $ageGroups[0]) {
        $underaged[] = $user;
      }
      if ($age >= $ageGroups[0] && $age <= $ageGroups[1]) {
        $neutral[] = $user;
      }
      if ($age >= $ageGroups[1] && $age <= $ageGroups[2]) {
        $closeToRetirement[] = $user;
      }
      if ($age >= $ageGroups[2]) {
        $retirement[] = $user;
      }
    }
  }
  $underagedCount = count($underaged);
  $neutralCount = count($neutral);
  $closeToRetirementCount = count($closeToRetirement);
  $retirementCount = count($retirement);
  $stats = array(
    $underagedCount,
    $neutralCount,
    $closeToRetirementCount,
    $retirementCount
  );
  return $stats;
}

function renderStats($stats) {
  $tableHeader = '<table>
    <tr>
      <th></th>
      <th>-17 years</th>
      <th>18-55 years</th>
      <th>56-65 years</th>
      <th>66- years</th>
    </tr>';
  $table = '';
  foreach($stats as $key => $group) {
    $table .= '<tr>
      <th>'.$key.'</th>
      <td>'.$group[0].'</td>
      <td>'.$group[1].'</td>
      <td>'.$group[2].'</td>
      <td>'.$group[3].'</td>
    </tr>';
  }
  $tableFooter = '</table><hr>';
  $table = $tableHeader.$table.$tableFooter;
  return $table;
}

function getAddUserForm() {
  $form = '<form action="user.php" method="POST">
      <fieldset>
        <legend>Add user</legend>
        <label for="name">Name</label>
        <input name="name" fid="name" required="required">
        <label for="age">Age</label>
        <input name="age" id="age" required="required">
        <label for="location">Location</label>
        <input name="location" id="location" required="required">
        <label for="description">Description</label>
        <input name="description" id="description">
        <div class="radios">
          <label for="male">Male</label>
          <input type="radio" id="male" name="gnder" value="male">
          <label for="female">Female</label>
          <input type="radio" id="female" name="gender" value="female">
          <label for="other">Other</label>
          <input type="radio" id="other" name="gender" value="other">
        </div>
        <button type="submit">Create User</button>
      </fieldset>
    </form>';
  return $form;
}

function getRemoveUserForm() {
  $form = '<form action="user.php" method="POST">
      <fieldset>
        <legend>Remove user</legend>
        <label for="id">Id</label>
        <input name="id" id="id" required="required">
        <input type="hidden" name="remove" id="remove" value="1">
        <button type="submit">Remove User</button>
      </fieldset>
    </form>';
  return $form;
}

function writeToFile($users) {
  $users = serialize($users);
  $bytesWritten = file_put_contents('users.txt', $users);
  return $bytesWritten;
}

function readFromFile() {
  $users = unserialize(file_get_contents('users.txt'));
  return $users;
}

function getHeader() {
  $header = '<!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Awesome user registry</title>
    <style>
      label {display:block;}
      .radios label {display: inline;}
      td, th {padding:0.25em 1em;}
      th {text-align: left;}
      td, th {max-width: 15em; overflow: auto;}
    </style>
  </head>
  <body>
    <h1>Awesome user registry</h1>';
  return $header;
}

function getFooter() {
  $footer = '<hr>
    <h2>Documentation</h2>
    <p>Users can be created, removed and listed individually. Removed users are not shown.</p>
    <p>All user info is mandatory.</p>
    <p>Users can be managed through web GUI or by perfoming HTTP requests as followed:</p>
    <ul>
      <li><code>GET</code> <code>user.php</code> returns full user list</li>
      <li><code>GET</code> <code>user.php</code> with query parameter <code>id</code> returns requested user</li>
      <li><code>POST</code> <code>user.php</code> with <code>remove</code> and <code>id</code> parameter (<code>remove</code> does not need any value) will delete requested user.</li>
      <li><code>POST</code> <code>user.php</code> with <code>name</code>, <code>age</code>, <code>location</code> and <code>description</code> parameters will create a new user.</li>
    </ul>
  </body>
  </html>';
  return $footer;
}

function renderUser($user) {
  $userList = '<ul>';
  $userList .= '<li>Id: <span id="id">'.$user['id'].'</span></li>';
  $userList .= '<li>Name: '.$user['name'].'</li>';
  $userList .= '<li>Age: '.$user['age'];
  $userList .= '<li>Location: '.$user['location'].'</li>';
  $userList .= '<li>Description: '.$user['description'].'</li>';
  $userList .= '<li>Gender: '.$user['gender'].'</li>';
  $userList .= '</ul>';
  return $userList;
}

function renderUserList($users) {
  $usersList = '<hr><table>
    <tr>
      <th><a href="user.php">Id</a></th>
      <th><a href="user.php?sort=name">Name</a></th>
      <th><a href="user.php?sort=age">Age</a></th>
      <th><a href="user.php?sort=location">Location</a></th>
      <th><a href="user.php?sort=description">Description</a></th>
      <th><a href="user.php?sort=gender">Gender</a></th>
    </tr>';
  $falseIndex = 0;
  foreach($users as $key=>$user) {
    $usersList .= '<tr>
      <td>'.$key.'</td>
      <td><a href="user.php?id='.$falseIndex.'">'.htmlspecialchars($user['name']).'</a></td>
      <td>'.htmlspecialchars($user['age']).'</td>
      <td>'.htmlspecialchars($user['location']).'</td>
      <td>'.htmlspecialchars($user['description']).'</td>
      <td>'.$user['gender'].'</td>
      </tr>';
    $falseIndex++;
  }
  $usersList .= '</table><hr>';
  return $usersList;
}