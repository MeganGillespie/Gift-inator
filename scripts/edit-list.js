/* Edit List Javascript */
"use strict";
window.addEventListener("DOMContentLoaded", () => {

  let form = document.getElementsByTagName("form").item(0);
  form.addEventListener("submit", (ev) => {

    //Select Elements Needed
    let errors = form.getElementsByClassName("errorMessage");
    let inputs = document.getElementsByTagName("input");
    let title = inputs.item(0);
    let newPassword = inputs.item(1);

    //Reset Form
    for (let i = 0; i < errors.length; i++)
      errors.item(i).classList.replace("error", "noerror");
    let valid = true;

    //Validate Form Contents

    //Title
    if (title == "") {
      valid = false;
      errors.item(0).classList.replace("noerror", "error");
    }

    //Password
    if (newPassword != "" && newPassword.length < 8) {
      valid = false;
      errors.item(2).classList.replace("noerror", "error");
    }

    if (!valid) {
      //if Javascript validation finds errors do not send to server to check
      //and continue with inserting info into the database
      ev.preventDefault();
    }
  });
});
