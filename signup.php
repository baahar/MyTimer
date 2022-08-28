<div class="container" style="max-width: 600px;">
  <div class="main">
    <div class="grid">
      <div>
        <button class="delete-button" onclick="document.getElementById('modal-signup').style.display='none'" style="position:relative;float:right;margin-top: 20px;margin-right:20px;"></button>
      </div>
      <div class="sign-up-form" style="border:0px;">
          <div class="form-row">
              <label>Username</label><br>
              <input type="text" spellcheck="false" id="signup_username" placeholder="enter your email address ..">
          </div>    
          <div class="form-row">
              <label>Password</label><br>
              <input type="password" spellcheck="false" id="signup_password" placeholder="enter your password ..">
          </div>
          <div class="form-row">
              <label>Confirm Password</label><br>
              <input type="password" spellcheck="false" id="signup_confirm_password" placeholder="re-enter your password ..">
          </div>
          <div id="signup_message" style="padding:0 1rem;color:e94c4c;"></div>
          <div class="form-row">
              <button onclick="signup()">Sign up</button>
          </div>
      </div>
    </div>  
    <div style="display:flex;justify-content:center;color:#efefef;">
      <p>Already have an account? Log in <a href="#" onclick="showLoginModal()">here</a>.</p>
    </div>  
  </div>
</div>
 
