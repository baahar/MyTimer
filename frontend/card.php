<div id="card_<?=$task->id?>" cid="<?=$task->id?>" class="card">

  <div class="row-top">
    <div class="card-flex">
      <input type="text" id="name_<?=$task->id?>" spellcheck="false" onClick="this.setSelectionRange(0, this.value.length)" onkeypress="cardnameClicked(event)" onfocusout="updateTaskName(<?=$task->id?>)" style="text-align:left;" value="<?=$task->name?>">  
    </div>
    <button class="delete-button" onclick="deleteTask(<?=$task->id?>)"></button>

    <?php 
      if ($task->status == 0) { 
        echo '<button class="done-button" onclick="updateTaskStatus(' . $task->id . ', 1)"></button>'; 
      } else { 
        echo '<button class="refresh-button" onclick="updateTaskStatus(' . $task->id . ', 0)"></button>'; 
      }
    ?>
  </div>

  <div class="horizontalline"></div>
  
  <div class="row-bottom">
     <div class="card-flex">
        <div class="row" style="color:#efefef;">
          <div class="column"> h </div>
          <div class="pseudo-column"></div>
          <div class="column"> m </div>
          <div class="pseudo-column"></div>
          <div class="column"> s </div>
        </div>
        <div class="row" style="font-family: Montserrat;">
          <?php 
            $h = ($task->tasktime->hours < 10) ? "0" . $task->tasktime->hours : $task->tasktime->hours;
            $m = ($task->tasktime->minutes < 10) ? "0" . $task->tasktime->minutes : $task->tasktime->minutes;
            $s = ($task->tasktime->seconds < 10) ? "0" . $task->tasktime->seconds : $task->tasktime->seconds;
          ?>
          <div id="hours_<?=$task->id?>" style="font-size:3.5rem;" class="column"> <?=$h?> </div>
          <div class="pseudo-column"> : </div>
          <div id="minutes_<?=$task->id?>" style="font-size:3.5rem;" class="column"> <?=$m?> </div>
          <div class="pseudo-column"> : </div>
          <div id="seconds_<?=$task->id?>" style="font-size:3.5rem;" class="column"> <?=$s?> </div>
      </div>
    </div>
    
    <div class="verticalline"></div>
    
    <div class="card-fix">
      <div class="row" style="height:40px;margin-top:25px;"><div class="chronometer"></div></div>
      <div class="row" style="height:40px;">
        <button class="play-button" id="chronometer_btn_<?=$task->id?>" value="start" onclick="startStopClock(<?=$task->id?>, 0)"></button>
      </div>
    </div>

    <div class="verticalline"></div>

    <div class="card-fix" style="width: 15%;">
      <div class="row" style="height:40px;margin-top:25px;">
        <div class="eggtimer"></div>
        <input type="number" id="mininput_<?=$task->id?>" onchange="resetMinOutput(<?=$task->id?>)" onKeyDown="return false" min="1" max="999" value="<?=$_SESSION['eggtimer_start']?>">
      </div>
      <div class="row"><label style="font-size:0.7rem;margin-top:-17px;margin-left:32px;">minutes</label></div>
      
      <div class="row" style="height:40px;"><button class="play-button" id="eggtimer_btn_<?=$task->id?>" value="start" onclick="startStopClock(<?=$task->id?>, 1)"></button></div>
      <div class="row">
        <label id="minoutput_<?=$task->id?>" style="font-family: Montserrat;font-size:1rem;color:#black;display:none;border:1px solid black;border-width: 1px 0 1px 0;">00:00</label>
      </div>
    </div>
  </div>

</div>