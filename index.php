<?php

function create_new_aur($name=False) {

	if ($name) {
		$name = preg_replace("/[^a-z0-9]/", '', $name);
		$id = $name;
	}
	else {
		$id = strtolower(substr(md5(rand()),0,5));
	}
	$id = 'aur-'.$id;
	$dir = mkdir($id);
	$user = copy('aur.user.php', $id.'/user.php');
	$index = copy('aur.index.php', $id.'/index.php');
	$users = copy('aur.users.txt', $id.'/users.txt');

	if (!$user || !$index || !$users) {
		rmdir($id);
		$id = False;
	}
	elseif (!$dir) {
		$id = False;
	}

	return $id;
}

function list_recent_instances($limit=20) {
	$dirs = glob('aur*' , GLOB_ONLYDIR);
	usort($dirs, create_function('$a,$b', 'return filemtime($a) - filemtime($b);'));
	$dirs = array_reverse($dirs);
	$dirs = array_slice($dirs, 0, $limit);
	return $dirs;
}

function delete_aur($name) {
	$name = explode('aur-', $name);
	$name = preg_replace("/[^A-Za-z0-9]/", '', $name[1]);
	rename('aur-'.$name, 'del-aur-'.$name);
}

$id = False;
if (isset($_POST['create'])) {
	$name  = False;
	if (isset($_POST['customname'])) {
		$name = $_POST['customname'];
	}
	$id = create_new_aur($name);
}

$limit = 21;
$dirs = list_recent_instances($limit);
if (isset($_GET['limit'])) {
	$limit = $_GET['limit'];
}

if (isset($_POST['removename'])) {
	delete_aur($_POST['removename']);
}

$header = '<!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <title>MAURI - Mother of Awesome User Registry Instances</title>
    <style>
      label {display:block;}
      .radios label {display: inline;}
      td, th {padding:0.25em 1em;}
      th {text-align: left;}
      td, th {max-width: 15em; overflow: auto;}
    </style>
  </head>
  <body>
  <h1>MAURI - Mother of Awesome User Registry Instances</h1>';

$footer = '  </body>
  </html>';

$form_add = '<form action="index.php" method="POST">
    <fieldset>
      <legend>Create new AUR</legend>
      <p>Optionally you may choose a name for your AUR instance. If Name field is left empty, random name is generated.</p>
      <input type="hidden" name="create" id="create" value="1">
      <input type="text" name="customname" id="customname" placeholder="My first AUR">
      <button type="submit">Create</button>
    </fieldset>
  </form>';

$form_del = '<form action="index.php" method="POST">
    <fieldset>
      <legend>Delete existing AUR instance</legend>
      <input type="hidden" name="del" id="del" value="1">
      <label for="removename">Name</label>
      <input type="text" name="removename" id="removename" placeholder="My first AUR" reguired="required">
      <button type="submit">Delete</button>
    </fieldset>
  </form>';

echo $header;

if ($id) {
	echo '<p>Grab your fresh AUR from here: <a href="'.$id.'" id="newaur">'.$id.'</a></p>';
}

echo $form_add;

echo '<h2>20 latest AUR instances</h2>';
echo '<ul>';
foreach ($dirs as $i => $dir) {
	echo '<li><a href="'.$dir.'">'.$dir.'</a></li>';
}
echo '</ul>';

echo $form_del;

echo $footer;
