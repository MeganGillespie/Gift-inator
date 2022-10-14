//Edit Account Javascript
"use strict";
window.addEventListener("DOMContentLoaded", () => 
{
    //Stole this function from the lab
    const emailIsValid = (string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(string);

    let form = document.getElementsByTagName("form").item(0);
    form.addEventListener("submit", (ev) =>{
        //Select Elements Needed
        let errors = form.getElementsByClassName("errorMessage");
        let inputs = document.getElementsByTagName("input");
        const email = inputs.item(0).value;
        const newPassword = inputs.item(2).value;
       
        //Reset Form
        for(let i = 0; i < errors.length; i++)
        {
            errors.item(i).classList.remove("error");
            errors.item(i).classList.add("noerror");
        }

        let valid = true;

        //Validate Form Contents 

        //Email
        if(!emailIsValid(email))
        {
            valid = false;
            errors.item(0).classList.replace("noerror", "error");
        }

        //New Password
        if (newPassword.length < 8)
        {
            valid = false;
            errors.item(2).classList.replace("noerror", "error");
        }

        if(!valid)
        {
            ev.preventDefault();
        }
    });
});