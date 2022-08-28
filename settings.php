<div class="container" style="max-width: 600px;">
  <div class="main">
    <div class="grid">
    <div>
      <button class="delete-button" onclick="document.getElementById('modal-settings').style.display='none'" style="position:relative;float:right;margin-top: 20px;margin-right:20px;"></button>
    </div>

    <div class="sign-up-form" style="border:0px;">
        <div class="horizontalline"></div>
        <div class="form-row">
            <label style="font-weight:bold;">Eggtimer</label><br>
        </div>
            <div class="form-row" style="margin-left:20px">
                <label style="display: inline-block;width:130px;">Sound on/off:</label><button id="eggtimer_sound" style="width: 100px;text-align: center; margin-left: 20px;height:20px;padding:5px;" value="1" onclick="toggleSoundOnOff()">switch off</button>
            </div>   
            <div class="form-row" style="margin-left:20px">
                <label style="display: inline-block;width:130px;">Starting point:</label><input id="eggtimer_minute_setting" type="number" onKeyDown="return false" min="1" max="999" value="<?=$_SESSION['eggtimer_start']?>" style="margin-left: 15px;" onchange="updateEggtimerMinuteSetting()"><label style="margin-left: 8px;">minutes</label>
            </div>    
        
        <?php 
            if (isset($_SESSION['userid']) && $_SESSION['userid'] != -1) {
                echo '<div class="horizontalline" style="margin: 1rem"></div>';

                echo '<div class="form-row">';
                    echo '<label style="font-weight:bold;">Password</label><br>';
                echo '</div>';
                    echo '<div class="form-row" style="margin-left:20px;margin-top:-15px;">';
                        echo '<label style="display: inline-block;width:200px;">New password:</label><input id="settings_password" type="password" spellcheck="false" placeholder="enter your password .." style="width:260px;margin-left:20px;">';
                        echo '<br>';
                        echo '<label style="display: inline-block;width:200px;">Confirm new password:</label><input id="settings_confirm_password" type="password" spellcheck="false"  placeholder="re-enter your password .." style="width:260px;margin-left:20px;">';
                    echo '</div>';
                    echo '<div class="form-row" style="margin-left:20px">';
                        echo '<button onclick="updatePassword()">Change password</button>';
                        echo '<div id="settings_message" style="padding:0.5rem 1rem;color:e94c4c;"></div>';
                    echo '</div>';                
                        
            } 
        ?> 


        
       
    </div>
  </div>   
</div>

</div>