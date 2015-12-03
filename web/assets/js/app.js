hashforms = document.getElementsByClassName('formhash');

var i;
for (i = 0; i < hashforms.length; i++) {
    var hashform = hashforms[i];

    if (hashform.addEventListener){
        hashform.addEventListener("submit", function(evt) {
            evt.preventDefault();
            formhash(hashform, "InputPassword");
        }, false);
    } else if(hashform.attachEvent){
        hashform.attachEvent("onsubmit", function(evt) {
            evt.preventDefault();
            formhash(hashform, "InputPassword");
        }, false);
    }
}

function formhash(form, password_field_id) {
  password = document.getElementById(password_field_id);

  var p = document.createElement("input");
  p.name = password.name;
  p.type = "hidden"
  p.value = CryptoJS.MD5(password.value);

  form.appendChild(p);

  password.name  = "";
  password.value = "";

  form.submit();
}