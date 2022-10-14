"use strict";
window.addEventListener("DOMContentLoaded", () => {
  //Add confirm to all delete buttons
  let deleteButtons = document.getElementsByClassName("delete");
  for (let i = 0; i < deleteButtons.length; i++) 
  {
    deleteButtons.item(i).addEventListener("click", (event) => {
      if (!confirm("Are you sure you want to delete?")) event.preventDefault();
    });
  }

  //Add copy to clipboard button to link button
  let linkbutton = document.getElementsByClassName("link").item(0);
  if (linkbutton != null) 
  {
    linkbutton.addEventListener("click", () => {
      let copyText = document.getElementsByName("url").item(0);
      copyText.classList.remove("hidden");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      document.execCommand("copy");
      window.alert("The public URL has been coppied to your clipboard.");
      copyText.classList.add("hidden");
    });
  }

  //Make view item pages open in a new tab
  let viewButtons = document.getElementsByClassName("viewItem");
  for(let i = 0; i < viewButtons.length; i++)
  {
    viewButtons.item(i).addEventListener("click", (event) => {
      let popup = document.getElementById(viewButtons.item(i).value);
      let closeButton = popup.getElementsByClassName("close").item(0);
      
      popup.style.display = "block";
      console.log("open");

      closeButton.addEventListener("click", () => {
        popup.style.display = "none";
        console.log("close: click");

      }, { once: true });

      window.addEventListener("click", (event) => {
        if (event.target == popup) {
          popup.style.display = "none";
          console.log("close: window");
        }
      }, { once: true });

      event.preventDefault();
    });
  }
});