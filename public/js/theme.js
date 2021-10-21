// things that i want to do everywhere, or that i want to be available everywhere

// website onload; things to do
// window.onload = function(){
//   document.getElementById("ThemePreload").classList.add("hidden");
//   document.getElementsByTagName("BODY")[0].classList.remove("no-overflow");
// }

var darkLightCookie = getCookie("darklightswitch");
if (darkLightCookie) {
  setDarkStyle('uk-light', 'LightSwitch', 'DarkSwitch');
} else {
  unsetDarkStyle('uk-light', 'DarkSwitch', 'LightSwitch');
}


document.querySelectorAll('.darklight-switch').forEach(item => {
  item.addEventListener('mousedown', event => {
    event.preventDefault();
    var darkLightCookie = getCookie("darklightswitch");
    if (darkLightCookie != "" && darkLightCookie != null) {
      unsetCookie('darklightswitch');
      unsetDarkStyle('uk-light', 'DarkSwitch', 'LightSwitch');
    } else {
      setCookie('darklightswitch', 'dark', 7);
      setDarkStyle('uk-light', 'LightSwitch', 'DarkSwitch');
    }
  })
})

function successFunction() {
  alert('It went well!')
};
function failFunction() {
  alert('It didnt go well...')
};

// listen for click event in the main, & if target of click has data-link attr, do the shop filtering
document.querySelector('main').addEventListener('click', function(event) {
  if (event.target.hasAttribute('data-link'))
    quickLoadTest('#SiteHeader', 'data-link', successFunction, failFunction);
});

// listen for click event in the main, & if target of click has data-link attr, do the shop filtering
// document.querySelector('main').addEventListener('click', function(event) {
// 	if (event.target.hasAttribute('data-link'))
// 		quickLoad('#PrimaryHeader', '.filters-container', 'quickloader', 'data-link', "ContentSection", '.index-archive', myFunction);
// });