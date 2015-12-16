jQuery(document).ready(function() {

	jQuery(".tabs-primary").append(jQuery("#pdf-btn"));

});

function toggle(elementId) {
  var ele = document.getElementById(elementId);
  if(ele.style.display == "block") {
        ele.style.display = "none";
    }
  else {
    ele.style.display = "block";
  }
} 
