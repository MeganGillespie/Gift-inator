/* Add & Edit List Item Javascript
 * Sourced validURL function code from Stack overflow
 * https://stackoverflow.com/questions/5717093/check-if-a-javascript-string-is-a-url
 */

"use strict";
window.addEventListener("DOMContentLoaded", () => 
{
  function validURL(str) 
  {
    var pattern = new RegExp(
      "^(https?:\\/\\/)?" + // protocol
      "((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|" + // domain name
      "((\\d{1,3}\\.){3}\\d{1,3}))" + // OR ip (v4) address
      "(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*" + // port and path
      "(\\?[;&a-z\\d%_.~+=-]*)?" + // query string
      "(\\#[-a-z\\d_]*)?$", "i" // fragment locator
    );
    return !!pattern.test(str);
  }

  let form = document.getElementsByTagName("form").item(0);
  form.addEventListener("submit", (ev) => 
  {
    //Select Elements Needed
    let errors = form.getElementsByClassName("errorMessage");
    let nameError = errors.item(0);
    let uNameError = errors.item(1);
    let pictureError = errors.item(2);
    let urlError = errors.item(3);
    let quantitiyError = errors.item(4);

    let inputs = document.getElementsByTagName("input");
    const name = inputs.item(0).value;
    const filePath = inputs.item(2).value;
    const url = inputs.item(3).value;
    const quantity = inputs.item(4).value;

    //Reset Form
    nameError.classList.replace("error", "noerror");
    uNameError.classList.replace("error", "noerror");
    pictureError.classList.replace("error", "noerror");
    urlError.classList.replace("error", "noerror");
    quantitiyError.classList.replace("error", "noerror");
    let valid = true;

    //Validate Form Contents

    //Name
    if (name == "") 
    {
      valid = false;
      nameError.classList.replace("noerror", "error");
    }

    //File Path
    let extensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
    if (filePath != "" && !extensions.exec(filePath))
    {
        valid = false;
        pictureError.innerHTML = "Please upload a file that has is a photo. <br>Accepted file extensions are .jpg, .jpeg, .png, and .gif";
        pictureError.classList.replace("noerror", "error");
       
    }

    //URL
    if (!validURL(url) && url != "") 
    {
      valid = false;
      urlError.classList.replace("noerror", "error");
    }

    //Quantity
    const amount = parseFloat(quantity);
    if (isNaN(amount) || amount % 1 != 0 || amount < 0) 
    {
      valid = false;
      quantitiyError.classList.replace("noerror", "error");
    }

    if (!valid) 
    {
      //If Javascript validation finds errors do not send to server to check 
      //and continue with inserting info into the database
      ev.preventDefault();
    }
  });
});