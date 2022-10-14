//Create Account Javascript
"use strict";
window.addEventListener("DOMContentLoaded", () => 
{
    //Stole this function from tne lab
    const emailIsValid = (string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(string);

    let form = document.getElementsByTagName("form").item(0);
    form.addEventListener("submit", (ev) =>{
        //Select Elements Needed
        let errors = form.getElementsByClassName("errorMessage");
        let inputs = document.getElementsByTagName("input");
        const email = inputs.item(0).value;
        const username = inputs.item(1).value;
        const password1 = inputs.item(2).value;
        const password2 = inputs.item(3).value;
       
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

        //Password
        if (password1.length < 8)
        {
            valid = false;
            errors.item(3).classList.replace("noerror", "error");
        }

        //Password 2
        if (password1 != password2)
        {
            valid = false;
            errors.item(4).classList.replace("noerror", "error");
        }

        if(!valid)
        {
            ev.preventDefault();
        }
    });
});