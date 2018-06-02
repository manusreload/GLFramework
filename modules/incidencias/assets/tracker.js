$(document).ready(function() {

    var store = getNavigation();
    var data = {
        url: window.location.href,
        time: new Date().getTime()
    };
    store.push(data);
    if(store.length > 32) {
        store.splice(0, 1);
    }

    localStorage.setItem("nav", JSON.stringify(store));
});

function getNavigation() {
    var store = localStorage.getItem("nav");
    if(store != null) {
        store = JSON.parse(store);
    } else {
        store = []
    }

    return store;
}