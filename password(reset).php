<fieldset id="forgot-password-reset">
    <div class="form-item column">
        <label for="passwd">Password</label>
        <input 
          type="password" 
          name="password" 
          id="passwd" 
          value="" 
          required
        />
        <span 
          class="<?php echo isset($errors['password']) ? 'error' : 'noerror'; ?>"
        >Invalid password. Password must be 8 characters or more.</span>
    </div>
    <div class="form-item column">
        <label for="check-passwd">Re-enter Password</label>
        <input 
          type="password" 
          name="check-password" 
          id="check-passwd" 
          value="" 
          required
        />
        <span 
          class="<?php echo isset($errors['paswdCheck']) ? 'error' : 'noerror'; ?>"
        >Passwords do not match.</span>
    </div>
</fieldset>

<fieldset id="reset-password">
    <div class="form-item column">
        <label for="current-passwd">Current Account Password</label>
        <input 
          type="password" 
          name="current-password" 
          id="current-passwd" 
          value="" 
          required
        />
        <span 
          class="<?php echo isset($errors['current-password']) ? 'error' : 'noerror'; ?>"
        >Invalid password.</span>
    </div>
</fieldset>