document.getElementById("hamburger").addEventListener("click",function(){
  if (this.className == "bars icon"){
    this.className = "close icon";
  } else {
    this.className = "bars icon";
  }
  document.getElementById("mobile_menu").classList.toggle("expanded");
});

function updateNav(page){
  for (let nav_item of document.getElementsByClassName("nav_" + page + " item")) {
    nav_item.className = "nav_" + page + " active item";
  }
}
