/*
Include this script to output a warning if this browser is outdated. 
How do we tell if it's outdated? In this case, it's done by checking for a feature that is only supported in ES6(ECMAScript 6), a modern standardization of Javascript.
At the time of making this, Chrome 67, Firefox 60, Edge 16, and Safari 11 are considered examples of modern browsers, although any browser that can support ES6 syntax would work.
*/
function check() {
    "use strict";

    if (typeof Symbol == "undefined") return false;
    try {
        eval("class Foo {}");
        eval("var bar = (x) => x+1");
    } catch (e) { return false; }

    return true;
}

if (check()) {
    // The engine supports ES6 features you want to use
} else {
    // The engine doesn't support those ES6 features
    // Use the boring ES5 :(
    var div = document.createElement("div");
    div.setAttribute("id", "site-error");
    div.setAttribute("role", "alert");
    div.innerHTML = "Your browser does not support some of this site's features. Please upgrade to a newer browser to use this site properly!";

    document.getElementById("MainContent").appendChild(div); //append the outdated browser message to the main conent
}