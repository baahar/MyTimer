<?php 

  require_once 'backend/DBHandler.php';

  session_start();

  if (!isset($_SESSION['userid'])) {
    // unregistered user
    $_SESSION['userid'] = -1;
    $_SESSION['eggtimer_start'] = 25;
  }

  if(isset($_GET['logout'])) {
    $_SESSION['userid'] = -1;
    $_SESSION['eggtimer_start'] = 25;
    header("Location: index.php");
  }
?>

<html>
  <head>
    <title>TuskTimer - slightly better than tally marks</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Comfortaa">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
    <link rel="stylesheet" href="frontend/css/design.css" >
  </head>

  <body>
    
    <div class="container">
      <div class="elephant-clock" onclick="showInfoModal()"></div>
      <?php require __DIR__ . '/header.php'; ?>
      <div class="main">
        <div class="tabs">
          <div class="left-tab" id="first-tab" onclick="getTasks(0)">active</div>
          <div class="right-tab" id="second-tab" onclick="getTasks(1)">completed</div>
        </div>
        <div id="grid" class="grid">
           
        </div>
        <div class="plus_button_row">
          <button class="plus_button" onclick="addEmptyTask()"></button>
        </div>

        <?php 
          if ($_SESSION['userid'] == -1) {
            echo '<div style="display:flex;justify-content:center;color:#efefef;">';
                echo '<p>If you want to save your data, sign up <a href="#" onclick="showSignupModal()">here</a>. It\'s free.</p>';
            echo '</div>';
          }
        ?>
      </div>


      <!-- Modal Content -->
      <div id="modal-signup" class="modal">
          <div class="modal-container">
            <?php require __DIR__ . '/header.php'; ?>
            <?php require __DIR__ . '/signup.php'; ?>
          </div>
      </div>

      <div id="modal-login" class="modal">
          <div class="modal-container">
            <?php require __DIR__ . '/header.php'; ?>
            <?php require __DIR__ . '/login.php'; ?>
          </div>
      </div>

      <div id="modal-settings" class="modal">
          <div class="modal-container">
            <?php require __DIR__ . '/header.php'; ?>
            <?php require __DIR__ . '/settings.php'; ?>
          </div>
      </div>

      <div id="modal-info" class="modal">
          <div class="modal-container">
            <?php require __DIR__ . '/header.php'; ?>
            <?php require __DIR__ . '/info.php'; ?>
          </div>
      </div>
    </div>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script id="worker-code" type="dont/run"> setInterval(function() { postMessage(''); }, 1000); </script>
   
    <script>

      const music = new Audio('./frontend/audio/beepbeep.mp3');
     /* window.onload = function() {
        music.play().then(function() {
         }).catch(function(){
         })
      }*/

      var active_tab = 0;
      var counter = 0;
      var localtasks = new Map();
      var workers = new Map(); // our source of time

      const uid = <?php echo $_SESSION['userid']; ?>;
      if (uid == -1) addEmptyTask(); 

      getTasks(0);
      

      var body = document.querySelector("body");
      body.addEventListener("keydown",function(e){
        var isInput = ~["TEXTAREA", "INPUT"].indexOf(e.target.tagName);
        if(e.key === " " && !isInput){
          stopAllClocks();
          e.preventDefault(); // prevent scroll
        } 
      });

      function addLocalCard(id, cid, name, hours, minutes, seconds) {
          
        card = "";
        card += '<div id="card_'+ cid + '" cid="' + id + '" class="card">';
          card += '<div class="row-top">';
            card += '<div class="card-flex">';
              card += '<input type="text" id="name_' + cid + '" spellcheck="false" onClick="this.setSelectionRange(0, this.value.length)" onkeypress="cardnameClicked(event)" onfocusout="updateTaskName(\'' + cid + '\')" style="text-align:left;" value="' + name + '">';  
            card += '</div>';
            card += '<button class="delete-button" onclick="deleteTask(\'' + cid + '\')"></button>'

            if (active_tab == 0) { 
              card += '<button class="done-button" onclick="updateTaskStatus(\'' + cid + '\', 1)"></button>'; 
            } else { 
              card += '<button class="refresh-button" onclick="updateTaskStatus(\'' + cid + '\', 0)"></button>'; 
            }
          card += '</div>';

          card += '<div class="horizontalline"></div>';

          card += '<div class="row-bottom">';
            card += '<div class="card-flex">';
              card += '<div class="row" style="color:#efefef;font-size:1.2rem;">';
                card += '<div class="column"> h </div>';
                card += '<div class="pseudo-column"></div>';
                card += '<div class="column"> m </div>';
                card += '<div class="pseudo-column"></div>';
                card += '<div class="column"> s </div>';
              card += '</div>';
              
              card += '<div class="row" style="font-family: Montserrat;">';
                hours = (hours < 10) ? "0" + hours : hours;
                minutes = (minutes < 10) ? "0" + minutes : minutes;
                seconds = (seconds < 10) ? "0" + seconds : seconds;

                card += '<div id="hours_' + cid + '" style="font-size:3.5rem;" class="column"> ' + hours + ' </div>';
                card += '<div class="pseudo-column"> : </div>';
                card += '<div id="minutes_' + cid + '" style="font-size:3.5rem;" class="column"> ' + minutes + ' </div>';
                card += '<div class="pseudo-column"> : </div>';
                card += '<div id="seconds_' + cid + '" style="font-size:3.5rem;" class="column">' + seconds + '</div>';
              card += '</div>';
            card += '</div>';
  
            card += '<div class="verticalline"></div>';
  
            card += '<div class="card-fix">';
              card += '<div class="row" style="height:40px;margin-top:25px;"><div class="chronometer"></div></div>';
              card += '<div class="row" style="height:40px;">';
                card += '<button class="play-button" id="chronometer_btn_' + cid + '" value="start" onclick="startStopClock(\'' + cid + '\', 0)"></button>';
              card += '</div>'; 
            card += '</div>';

            card += '<div class="verticalline"></div>';

            card += '<div class="card-fix" style="width:15%;">';
              card += '<div class="row" style="height:40px;margin-top:25px;">';
                card += '<div class="eggtimer"></div>';
                card += '<input type="number" id="mininput_' + cid + '" onchange="resetMinOutput(\'' + cid + '\')" onKeyDown="return false" min="1" max="999" value="' + document.getElementById("eggtimer_minute_setting").value + '">';

              card += '</div>';
              card += '<div class="row"><label style="font-size:0.7rem;margin-top:-17px;margin-left:32px;">minutes</label></div>';
    
              card += '<div class="row" style="height:40px;"><button class="play-button" id="eggtimer_btn_' + cid + '" value="start" onclick="startStopClock(\'' + cid + '\', 1)"></button></div>';
              card += '<div class="row">';
                card += '<label id="minoutput_' + cid + '" style="font-family: Montserrat;font-size:1rem;color:#black;display:none;border:1px solid black;border-width: 1px 0 1px 0;">00:00</label>';
              card += '</div>';
            card += '</div>';
          card += '</div>';
        card += '</div>';

        $("#grid").append(card);      
      }

      
      function addCard(id, name, hours, minutes, seconds) {
        if (uid != -1) {
          const data = {
            userid: uid,
            name: name,
            status: active_tab,
            action: 'create-task'
          }
          $.post('server.php', data, function(response) {
              if (response.error) {
                  alert(response.error);
              } else {
                 $("#grid").append(response.html);
              }
          }, 'json');
        } else { 

          cid = "c" + counter;
          localtasks.set(cid, {name: name, minutes: minutes, status: active_tab});
          counter++;
          addLocalCard(id, cid, name, hours, minutes, seconds);
        }
        
      }

      

      function getTasks(tabindex) {
        // stop all workers if there are any
        deleteAllWorkers();

        let action = 'get-active-tasks';
        if (tabindex == 0) { // active tasks
          active_tab = 0;
          document.getElementById("first-tab").style.background = "rgb(255, 255, 255, 0.6)";
          document.getElementById("first-tab").style.boxShadow = "-2px 4px";

          document.getElementById("second-tab").style.background = "transparent";
          document.getElementById("second-tab").style.borderLeft = "";
          document.getElementById("second-tab").style.borderBottom = "";
          document.getElementById("second-tab").style.boxShadow = "";
        } else if (tabindex == 1) { // completed tasks
          active_tab = 1;
          action = 'get-completed-tasks';
          document.getElementById("first-tab").style.background = "transparent";
          document.getElementById("first-tab").style.borderRight = "";
          document.getElementById("first-tab").style.borderBottom = "";
          document.getElementById("first-tab").style.boxShadow = "";

          document.getElementById("second-tab").style.background = "rgb(255, 255, 255, 0.6)";
          document.getElementById("second-tab").style.boxShadow = "2px 4px";
        }

        if (uid != -1) {
          const data = {
            userid: uid,
            action: action
          }
          $.post('server.php', data, function(response) {
              $("#grid").html(response.html);
          }, 'json');
        } else { 
          $("#grid").html("");
          for (let [key,value] of localtasks) {
            localtask = value;
            if (localtask['status'] == active_tab) {
              const h = Math.trunc(localtask['minutes']/60);
              addLocalCard(-1, key, localtask['name'], h, (localtask['minutes']-(60*h)),0);
            }
          }
        }
      }


      function deleteTask(id) {
        let confirmAction = confirm("Are you sure you want to delete this task? All related data will be deleted.");
        if (confirmAction) {
          const element = document.getElementById("card_" + id);
          cid = element.getAttribute("cid");
          if (uid != -1) {
            const data = {
              taskid: cid,
              action: 'delete-task'
            }
            $.post('server.php', data, function(response) {
                if (response.error) {
                    alert(response.error);
                } else {
                    element.remove();
                }
            }, 'json');
          } else {
            const element = document.getElementById("card_" + id);
            element.remove();
            localtasks.delete(id);
          }
        }
      }

      

      function startStopClock(id, eggtimer) {  // 0 for chronometer, 1 for eggtimer
        if (document.getElementById("minoutput_" + id).style.display == "none") {
          resetMinOutput(id);
        }

        if (event.srcElement.value == "start") {
          // to get permission for notifications the first time user clicks on start
          Notification.requestPermission();

          // we need to play the sound once via direct user interaction - otherwise Safari will block auto-play later
          if (eggtimer && document.getElementById("eggtimer_sound").value == "1") music.play(); 
          
          event.srcElement.value = "stop";
          event.srcElement.classList.remove("play-button");
          event.srcElement.classList.add("stop-button");

          if (eggtimer) {
            document.getElementById("mininput_" + id).disabled = true;
            document.getElementById("chronometer_btn_" + id).disabled = true;
            document.getElementById("minoutput_" + id).style.display = "inline";
          } else {
            document.getElementById("mininput_" + id).disabled = true;
            document.getElementById("eggtimer_btn_" + id).disabled = true;
            document.getElementById("minoutput_" + id).style.display = "none";
          }
          addWorker(id, eggtimer);

        } else { // stop clicked
          deleteWorker(id);
          event.srcElement.value = "start";
          event.srcElement.classList.remove("stop-button");
          event.srcElement.classList.add("play-button");
          enableAll(id);
        }
      }

      function runClock(id, eggtimer) {
        var h = parseInt(document.getElementById("hours_" + id).innerText);
        var m = parseInt(document.getElementById("minutes_" + id).innerText);
        var s = parseInt(document.getElementById("seconds_" + id).innerText);

        if (s == 59) {
          s = 0; 
          if (m == 59) {
            m = 0;
            h++;
          } else {
            m++;
          }
        } else {
          s++;
        }

        h = (h < 10) ? "0" + h : h;
        m = (m < 10) ? "0" + m : m;
        s = (s < 10) ? "0" + s : s;
    
        document.getElementById("hours_" + id).innerText = h;
        document.getElementById("minutes_" + id).innerText = m;
        document.getElementById("seconds_" + id).innerText = s;

        if (s == 0) updateTaskMinutes(id); // new minute

        if (eggtimer == 1) {
          const t = document.getElementById("minoutput_" + id).innerText;
          const i = t.indexOf(':');
          m = parseInt(t.substring(0,i));
          s = parseInt(t.substring(i+1,t.length));

          if (s == 0) {
            s = 59;
            m--;
          } else{
            s--;
          } 
          m = (m < 10) ? "0" + m : m;
          s = (s < 10) ? "0" + s : s;
          document.getElementById("minoutput_" + id).innerText = m + ":" + s;

          if (s == 0 && m == 0) { 
            stopEggtimer(id);
          }
        }
       
      }

      
      function stopEggtimer(id) {
        deleteWorker(id);

        const el = document.getElementById("eggtimer_btn_" + id);
        el.value = "start";
        el.classList.remove("stop-button");
        el.classList.add("play-button");
        
        enableAll(id);
        document.getElementById("minoutput_" + id).style.display = "none";

        music.play();
        Notification.requestPermission(function (permission) {
          if (permission === "granted") {
                showNotification();
          }
        });
      }

      
      function updateTaskMinutes(id) {
        const h = parseInt(document.getElementById("hours_" + id).innerText);
        const m = parseInt(document.getElementById("minutes_" + id).innerText);
        if (uid != -1) {
          const data = {
            taskid: id,
            userid: uid,
            totalminutes: (h*60 + m),
            action: 'update-task-minutes'
          }
          $.post('server.php', data, function(response) {
              if (response.error) {
                  alert(response.error)
              } else {
                 // do nothing
              }
          }, 'json')
        } else { // save locally
          localtasks.get(id)['minutes'] = (h*60 + m);
        }
      }
      

      function updateTaskName(id) {
        const n = document.getElementById("name_" + id).value;
        if (uid != -1) {
          // existing task -> update
          const data = {
            taskid: id,
            name: n,
            action: 'update-task-name'
          }
          $.post('server.php', data, function(response) {
              if (response.error) {
                  alert(response.error)
              } else {
                 // do nothing
              }
          }, 'json')
        } else { // save locally
          localtasks.get(id)['name'] = n;
        }
      }

      function updateTaskStatus(id, status) {
        let question = "Are you sure you want to mark this task as complete?";
        if (status == 0) question = "Are you sure you want to mark this task as active?"
        let confirmAction = confirm(question);
        if (confirmAction) {
          if (uid != -1) {
            // existing task -> update
            const data = {
              taskid: id,
              status: status,
              action: 'update-task-status'
            }
            $.post('server.php', data, function(response) {
                if (response.error) {
                    alert(response.error)
                } else {
                  const element = document.getElementById("card_" + id);
                  element.remove();
                }
            }, 'json')
          } else {
            localtasks.get(id)['status'] = status;
            const element = document.getElementById("card_" + id);
            element.remove();
          }
        }
      }

      function updatePassword() {
        if (uid != -1) {
          const password = document.getElementById("settings_password").value;
          const password_repeat = document.getElementById("settings_confirm_password").value;

          if (password == password_repeat) {
              const data = {
                userid: uid,
                password: password,
                action: 'update-password'
              }
              $.post('server.php', data, function(response) {
                if (response.error) {
                  document.getElementById("settings_message").innerHTML = response.error;
                } else {
                  document.getElementById("settings_message").innerHTML = response.success;
                }
              }, 'json');

          } else {
            document.getElementById("settings_message").innerHTML = "Passwords do not match.";
          }
        }
      }

      function addEmptyTask() {
        addCard(-1, "..enter task name here", 0, 0, 0);
      }


      /********************/
      /* HELPER FUNCTIONS */
      /********************/

      // GUI related
      function updateEggtimerMinuteSetting() {
        let element = document.getElementById("eggtimer_minute_setting");
        if (uid != -1) {
            const data = {
              userid: uid,
              eggtimer_start: element.value,
              action: 'update-eggtimer-minute-setting'
            }
            $.post('server.php', data, function(response) {
                if (response.error) alert(response.error)
            }, 'json');
          }
      }

      function toggleSoundOnOff() {
        let element = document.getElementById("eggtimer_sound");
        if (element.value == "1") {
          element.innerHTML = "switch on";
          element.value = "0";
        } else {
          element.innerHTML = "switch off";
          element.value = "1";
        }
      }

      function cardnameClicked(event) {
       if (event.keyCode == 13) {
          event.srcElement.blur();
        }
      } 

      function resetMinOutput(id) {
        var v = document.getElementById("mininput_" + id).value;
        v = (v < 10) ? "0" + v : v;
        document.getElementById("minoutput_" + id).innerText = v + ":00";
      }

      function enableAll(id) {
        document.getElementById("mininput_" + id).disabled = false;
        document.getElementById("chronometer_btn_" + id).disabled = false;
        document.getElementById("eggtimer_btn_" + id).disabled = false;
      }

      function showNotification() {
         console.log("show");
         var title = "Tusktimer";
         var body = "Your egg is ready!";
         var notification = new Notification("Your egg is ready!");
         notification.onclick = () => { 
                notification.close();
                window.parent.focus();
         }
      }

      function clearViewOfModals() {
        document.getElementById('modal-login').style.display='none';
        document.getElementById('modal-signup').style.display='none';
        document.getElementById('modal-settings').style.display= 'none';
        document.getElementById('modal-info').style.display= 'none';
      }
      
      function showSignupModal() {
        window.scrollTo(0,0);
        document.getElementById('modal-login').style.display='none';
        document.getElementById('modal-signup').style.display= 'flex';
        document.getElementById('modal-settings').style.display= 'none';
        document.getElementById('modal-info').style.display= 'none';
      }

      function showLoginModal() {
        window.scrollTo(0,0);
        document.getElementById('modal-login').style.display= 'flex';
        document.getElementById('modal-signup').style.display='none';
        document.getElementById('modal-settings').style.display= 'none';
        document.getElementById('modal-info').style.display= 'none';
      }

      function showSettingsModal() {
        window.scrollTo(0,0);
        document.getElementById('modal-login').style.display='none';
        document.getElementById('modal-signup').style.display='none';
        document.getElementById('modal-settings').style.display= 'flex';
        document.getElementById('modal-info').style.display= 'none';
      }

      function showInfoModal() {
        window.scrollTo(0,0);
        document.getElementById('modal-login').style.display='none';
        document.getElementById('modal-signup').style.display='none';
        document.getElementById('modal-settings').style.display= 'none';
        document.getElementById('modal-info').style.display= 'flex';
      }

      function showReportModal() {
        alert("coming soon ...")
      }

      
      
      // Time source related
      function addWorker(id, eggtimer) {
        let blob = new Blob([document.querySelector('#worker-code').textContent]);
        let worker = new Worker(window.URL.createObjectURL(blob));
        worker.onmessage = function() {
          runClock(id, eggtimer);
        }
        workers.set(id, worker);
      }

      function deleteWorker(id) {
        workers.get(id).terminate();
        workers.delete(id);
      }

      function deleteAllWorkers() {
        for (let worker of workers.values()) {
          worker.terminate();
        }
        workers.clear();
      }

      function stopAllClocks() {
        for (let [key,value] of workers) {
          let id = key;

          document.getElementById("chronometer_btn_" + id).value = "start";
          document.getElementById("chronometer_btn_" + id).classList.remove("stop-button");
          document.getElementById("chronometer_btn_" + id).classList.add("play-button");
          document.getElementById("chronometer_btn_" + id).disabled = false;
          
          document.getElementById("eggtimer_btn_" + id).value = "start";
          document.getElementById("eggtimer_btn_" + id).classList.remove("stop-button");
          document.getElementById("eggtimer_btn_" + id).classList.add("play-button");
          document.getElementById("eggtimer_btn_" + id).disabled = false;
          document.getElementById("mininput_" + id).disabled = false;

          workers.get(id).terminate();
        }
        workers.clear();
      }


    // sign up and login related
    function validateEmail(email) {
        var re = /\S+@\S+\.\S+/;
        return re.test(email);
    }

    function signup() {
      const username = document.getElementById("signup_username").value;
      if (validateEmail(username)) {
        const password = document.getElementById("signup_password").value;
        const password_repeat = document.getElementById("signup_confirm_password").value;

        if (password == password_repeat) {
            const data = {
              username: username,
              password: password,
              tasks: Object.values(Object.fromEntries(localtasks)),
              action: 'signup'
            }
            $.post('server.php', data, function(response) {
              if (response.error) {
                document.getElementById("signup_message").innerHTML = response.error;
              } else {
                document.getElementById("signup_message").innerHTML = response.success;
                window.location = 'index.php';
              }
            }, 'json');

        } else {
          document.getElementById("signup_message").innerHTML = "Passwords do not match.";
        }
      } else {
        document.getElementById("signup_message").innerHTML = "Invalid email address.";
      }
    }

    function validateEmail(email) {
        var re = /\S+@\S+\.\S+/;
        return re.test(email);
    }

    function login() {
      const username = document.getElementById("login_username").value;
      if (validateEmail(username)) {
          const password = document.getElementById("login_password").value;
          const data = {
            username: username,
            password: password,
            tasks: Object.values(Object.fromEntries(localtasks)),
            action: 'login'
          }
          $.post('server.php', data, function(response) {
              if (response.error) {
                document.getElementById("login_message").innerHTML = response.error;
              } else {
                document.getElementById("login_message").innerHTML = response.success;
                window.location = 'index.php';
              }
          }, 'json');
      } else {
        document.getElementById("login_message").innerHTML = "Invalid email address.";
      }
    }

    </script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-6MRMTJXPZS"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-6MRMTJXPZS');
    </script>


  </body>

  
</html>