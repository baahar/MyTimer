<?php

include 'classes/User.php';
include 'classes/Task.php';
include 'classes/TaskTime.php';

$db = null;
 
try {
  $servername = "localhost";
  $dbname = "";
  $username = "";
  $password = "";


  $db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  // set the PDO error mode to exception
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
  echo "Database connection failed: " . $e->getMessage();
}
    
function isDBSet() {
  global $db; 
  if ($db == null) return false;
  return true;
}


// ***************** //
// ***** USERS ***** //
// ***************** //

// returns the id of the new user if successful, -1 otherwise
function createUser($username, $password) {
  if (isDBSet()) {
    global $db; 
    $query = $db->prepare('INSERT INTO users SET username=:username, password=:password');
    $result = $query->execute([
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ]);
    if ($result) return $db->lastInsertId();   
  }
  return -1; 
}

// returns a User object if found, null otherwise
function getUserByName($username) {
  if (isDBSet()) {
    global $db;
    $user = $db->query("SELECT * FROM users WHERE username='$username'")->fetch(PDO::FETCH_ASSOC);
    if ($user) return new User($user['id'], $user['username'], $user['password'], $user['eggtimer_start']);   
  }
  return null;
}

// returns a User object if found, null otherwise
function getUserById($id) {
  if (isDBSet()) {
    global $db;
    $user = $db->query("SELECT * FROM users WHERE id='$id'")->fetch(PDO::FETCH_ASSOC);
    if ($user) return new User($user['id'], $user['username'], $user['password'], $user['eggtimer_start']);
  }
  return null;
}

// returns a User object if authorized, null otherwise
function authorizeUser($username, $password) {
  if (isDBSet()) {
      global $db;
      $user = getUserByName($username);
      if ($user != null && password_verify($password, $user->password)) return $user;
  }
  return null; 
}

function updateUserPassword($userid, $password) {
  if (isDBSet()) {
      global $db;
      $query = $db->prepare('UPDATE users SET password=:password WHERE id=:id');
      return $query->execute([
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'id' => $userid  
      ]);
  }
  return false;
}

function updateUserEggtimerStart($userid, $eggtimer_start) {
 if (isDBSet()) {
      global $db;
      $query = $db->prepare('UPDATE users SET eggtimer_start=:eggtimer_start WHERE id=:id');
      return $query->execute([
        'eggtimer_start' => $eggtimer_start,
        'id' => $userid  
      ]);
  }
  return false;
}


// ***************** //
// ***** TASKS ***** //
// ***************** //

// returns a Task object if found, null otherwise
function getTaskById($taskid) {
  if (isDBSet()) {
    global $db;
    $result = $db->query('SELECT t.id, t.userid, t.name, t.status, COALESCE(SUM(tl.minutes),0) AS \'totalminutes\'' .
                        ' FROM tasks t' .
                        ' LEFT JOIN tasklog tl' .
                        ' ON t.id = tl.taskid' .
                        ' GROUP BY t.id')->fetch(PDO::FETCH_ASSOC);
    if ($result) return Task::withTotalTasktime($result['id'], $result['userid'], $result['name'], $result['status'], new TaskTime($result['totalminutes']));
  }
  return null;
}


// returns a Task object array
function getAllUserTasks($userid, $status) {
  $tasks = [];
  if (isDBSet()) {
    global $db;
    $result = $db->query('SELECT t.id, t.userid, t.name, COALESCE(SUM(tl.minutes),0) AS \'totalminutes\'' .
                        ' FROM tasks t' .
                        ' LEFT JOIN tasklog tl' .
                        ' ON t.id = tl.taskid' .
                        ' WHERE t.userid=' . $userid . " AND t.status=" . $status .
                        ' GROUP BY t.id');
    
    foreach ($result as $r) {
      array_push($tasks, Task::withTotalTasktime($r['id'], $r['userid'], $r['name'], $status, new TaskTime($r['totalminutes'])));
    }
  }
  return $tasks;
}


// returns the created task as a Task object or null 
function createTask($userid, $name, $status, $minutes) {
    if (isDBSet()) {
      global $db;
      $query = $db->prepare('INSERT INTO tasks SET name=:name, userid=:userid, status=:status');
      $insert = $query->execute([
          'name' => $name,
          'userid' => $userid,
          'status' => $status
      ]);
      if ($insert) {
        $taskid = $db->lastInsertId();
        if ($minutes > 0 && createTaskLog($taskid, $userid, 0, $minutes)) {
          return Task::withTotalTasktime($taskid, $userid, $name, $status, new TaskTime($minutes));
        } else {
          return new Task($taskid,$userid, $name, $status);
        }
      }
    }
    return null;
}
   

function deleteTask($taskid) {
  if (isDBSet()) {
    global $db;
    return $db->exec('DELETE FROM tasks WHERE id=' . $taskid);
  }
  return false;
}

function updateTaskName($taskid, $name) {
  if (isDBSet()) {
      global $db;
      $query = $db->prepare('UPDATE tasks SET name=:name WHERE id=:id');
      return $query->execute([
        'name' => $name,
        'id' => $taskid  
      ]);
  }
  return false;
}

function updateTaskStatus($taskid, $status) {
  if (isDBSet()) {
      global $db;
      $query = $db->prepare('UPDATE tasks SET status=:status WHERE id=:id');
      return $query->execute([
        'status' => $status,
        'id' => $taskid  
      ]);
  }
  return false;
}


// updates the minute count of a task
function updateTaskMinutes($taskid, $userid, $totalminutes) {
  if (isDBSet()) {
      global $db;
      $task = getTaskById($taskid);
      if ($task == null) {
        return createTask($userid, $name, $status, $totalminutes);
      } else {
        return updateTaskLog($taskid, $userid, $totalminutes);
      } 
  }
  return false;
}


// ******************** //
// ***** TASK LOG ***** //
// ******************** //

function createTaskLog($taskid, $userid, $prev_minutes, $minutes) {
  if (isDBSet()) {
    global $db;
    $query = $db->prepare('INSERT INTO tasklog SET taskid=:taskid, userid=:userid, datum=:datum, prev_minutes=:prev_minutes, minutes=:minutes');
    return $query->execute([
        'taskid' => $taskid,
        'userid' => $userid,
        'datum' => date('Y-m-d'),
        'prev_minutes' => $prev_minutes,
        'minutes' => $minutes
    ]);
  }
  return false;
}


function updateTasklog($taskid, $userid, $totalminutes) {
    if (isDBSet()) {
      global $db;
      $result = $db->query('SELECT * FROM tasklog WHERE taskid=' . $taskid . ' ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);

      if (!$result) {
        // first ever task log for this task  
        createTaskLog($taskid, $userid, 0, $totalminutes);        
      } else {
        $diff = $totalminutes - ($result['prev_minutes'] + $result['minutes']);
        if ($diff > 0) {
          if ($result['datum'] != date('Y-m-d')) {
            // create task log for today
            return createTaskLog($taskid, $userid, ($result['prev_minutes'] + $result['minutes']), $diff); 
          } else {
            // update minutes for today's task log
            return updateTaskLogMinutes($result['id'], ($totalminutes - $result['prev_minutes']));
          }
        } else {
          return false;
        }
      }  
    } 
    return false;
}

function updateTaskLogMinutes($tasklogid, $minutes) {
  if (isDBSet()) {
    global $db;
    $query = $db->prepare('UPDATE tasklog SET minutes=:minutes WHERE id=:tasklogid');
      return $query->execute([
          'tasklogid' => $tasklogid,
          'minutes' => $minutes
      ]);
  } 
  return false;
}


function closeConnection() {
  $db = null;
}

?>