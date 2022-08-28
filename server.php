<?php

require_once 'backend/DBHandler.php';

session_start();

function post($name) {
  return htmlspecialchars($_POST[$name]);
}

$action = post('action');
$response = [];

switch ($action) {

  case 'create-task':
    $task = createTask(post('userid'), post('name'), post('status'), 0);
    if ($task != null) {
        $response['success'] = 'Task added.';
        ob_start();
        require __DIR__ . '/frontend/card.php';
        $response['html'] = ob_get_clean();
    } else {
        $response['error'] = 'Could not create task.';
    }
    break;

  case 'delete-task':
    $return = deleteTask(post('taskid'));
    if ($return == false) {
      $response['error'] = 'Could not delete task.';
    } else {
      $response['success'] = 'Task deleted.';
    }
    break;

  case 'update-task-minutes': 
    $return = updateTaskMinutes(post('taskid'), post('userid'), post('totalminutes'));
    if ($return === false) {
      $response['error'] = 'Could not update task information. Refresh your site.';
    } else {
      $response['success'] = 'Task information has been updated.';  
    }
    break;

  case 'update-task-name': 
    $return = updateTaskName(post('taskid'), post('name'));
    if ($return === false) {
      $response['error'] = 'Could not save the name.';
    } else {
      $response['success'] = 'Task name has been updated.';  
    }
    break;

  case 'update-task-status': 
    $return = updateTaskStatus(post('taskid'), post('status'));
    if ($return === false) {
      $response['error'] = 'Could not save the status.';
    } else {
      $response['success'] = 'Task status has been updated.';  
    }
    break;

  case 'get-active-tasks':
    $tasks = [];
    $tasks = getAllUserTasks(post('userid'), 0);
    ob_start();
    foreach ($tasks as $task) { 
      require __DIR__ . '/frontend/card.php';
    } 
    $response['html'] = ob_get_clean();
    break;


  case 'get-completed-tasks':
    $tasks = [];
    $tasks = getAllUserTasks(post('userid'), 1);
    ob_start();
    foreach ($tasks as $task) { 
      require __DIR__ . '/frontend/card.php';
    } 
    $response['html'] = ob_get_clean();
    break;

  case 'signup':
    $user = getUserByName(post('username'));
    if ($user == null) {
      $userid = createUser(post('username'), post('password'));
      if ($userid != -1) {
        // if there were any localtasks before sign up - create them
        if (isset($_POST['tasks'])) {
          foreach ($_POST['tasks'] as $item) {
            createTask($userid, htmlspecialchars($item['name']), htmlspecialchars($item['status']), htmlspecialchars($item['minutes']));
          }
        }
        $response['success'] = "User has been registered.";
        $_SESSION['userid'] = $userid;
        $_SESSION['eggtimer_start'] = 25;
      } else {
        $response['error'] = "An unknown error occured. Try again later.";
      }
    } else {
      $response['error'] = "This email address is already registered.";
    }        
    break;


  case 'login':
    $user = getUserByName(post('username'));
    if ($user != null) {
      $user = authorizeUser(post('username'), post('password'));
      if ($user != null) {
        // if there were any localtasks before login - create them
        if (isset($_POST['tasks'])) {
          foreach ($_POST['tasks'] as $item) {
            createTask($user->id, htmlspecialchars($item['name']), htmlspecialchars($item['status']), htmlspecialchars($item['minutes']));
          }
        }
        $response['success'] = "User has logged in.";
        $_SESSION['userid'] = $user->id;
        $_SESSION['eggtimer_start'] = $user->eggtimer_start;
      } else {
        $response['error'] = "Wrong password.";
      }
    } else {
      $response['error'] = "This email address is not registered.";
    }
    break;


  case 'update-eggtimer-minute-setting':
    $return = updateUserEggtimerStart(post('userid'), post('eggtimer_start'));
    if ($return === false) {
      $response['error'] = 'Could not update eggtimer start value.';
    } else {
      $_SESSION['eggtimer_start'] = post('eggtimer_start');
      $response['success'] = 'Eggtimer start value has been updated.';  
    }
    break;

  case 'update-password':
    $return = updateUserPassword(post('userid'), post('password'));
    if ($return === false) {
      $response['error'] = 'Could not update password.';
    } else {
      $response['success'] = 'Password has been updated.';  
    }
    break;
}

echo json_encode($response);

