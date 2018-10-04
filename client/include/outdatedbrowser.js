/*
Include this script to output a warning if this browser is outdated. 
How do we tell if it's outdated? In this case, it's done by checking if the script feature type="module" is supported in ES6(ECMAScript 6), a modern standardization of Javascript.
This works by putting the attribute "nomodule" on this script import, and if modules are supported, this will be ignored. Otherwise this will run.
At the time of making this, Chrome 67, Firefox 60, Edge 16, and Safari 11 are considered examples of modern browsers, although any browser that can support ES6 syntax would work.
*/
var div = document.createElement("div");
div.setAttribute("id", "site-error");
div.setAttribute("role", "alert");
div.innerHTML = "Your browser does not support some of this site's features. Please upgrade to a newer browser (e.g. Chrome 67, Firefox 60, Edge 16) to use this site properly!";

document.getElementById("MainContent").appendChild(div); //append the outdated browser message to the main conent