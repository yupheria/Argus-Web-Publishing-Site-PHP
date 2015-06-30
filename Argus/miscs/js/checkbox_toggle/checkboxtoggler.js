/**
 * Filename: checkboxtoggler.js
 * Description: toggles checkboxes checked/unchecked
 * Date Added: December 13,2007
 * Author: www.daniweb.com
 * MOdified By: argus team
 */
var formblock;
var forminputs;
var checked = false;

function prepare() 
{
    formblock= document.getElementById('form_id');
    forminputs = formblock.getElementsByTagName('input');
}

function toggleCheckBoxes(name)
{
    // determine the toggle status of the page
    if(this.checked == false)
    {
        // check all the check boxes
        select_all(name, 1);
        this.checked = true;
    }
    else
    {
        // uncheck all the check boxes
        select_all(name,0);
        this.checked = false;
    }
}

function select_all(name, value) {
for (i = 0; i < forminputs.length; i++) {
// regex here to check name attribute
var regex = new RegExp(name, "i");
if (regex.test(forminputs[i].getAttribute('name'))) {
if (value == '1') {
forminputs[i].checked = true;
} else {
forminputs[i].checked = false;
}
}
}
}

if (window.addEventListener) {
window.addEventListener("load", prepare, false);
} else if (window.attachEvent) {
window.attachEvent("onload", prepare)
} else if (document.getElementById) {
window.onload = prepare;
}