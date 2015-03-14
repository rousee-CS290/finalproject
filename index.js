var httpRequest = new XMLHttpRequest();

function create_request(username, password, type) {
    var page = 1;
    var overall = 0;
    var type_par = "";
    if (type === 1){
        type_par = "&create=create_new"
    } else {
        type_par = "&login=login_user"
    }
    var parameters = "username=" + username + "&password=" + password + type_par;

    function alertContents() {
        if (httpRequest.readyState === 4) {
          if (httpRequest.status === 200) {
            var text = httpRequest.responseText;
            console.log("response: " + text);
            switch (text[0]){
                case "0":
                    login_load();
                    break;
                case "1":
                    document.getElementById('username_message').innerHTML = "Someone is using that name already. Please try a different username; it must be unique.<br>";
                    break;
                case "2":
                    document.getElementById('username_message').innerHTML = "I can't find that username...did you type it right? It is case sensitive.<br>"       
                    break;
                case "3":
                    document.getElementById('password_message').innerHTML = "The password you entered is incorrect.<br>" 
                    break;
                default:
                    console.log("ERROR");
                    alert("Login Error ... ");
                    break;
            }

        } else {
            alert('There was a problem with the server.');
            }
        }
    }

    function sendReq() {
        if(!httpRequest){
            throw 'Unable to create httpRequest';
        }
        
        httpRequest.open('POST', 'ajaxlogin.php', true);
        httpRequest.responseType = 'text';
        httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        httpRequest.setRequestHeader("Content-length", parameters.length);
        httpRequest.setRequestHeader("Connection", "close");
        httpRequest.onreadystatechange = alertContents;
        httpRequest.send(parameters);
    }

    sendReq();
}

window.onload = function(){
    try {
        document.getElementById('login').innerHTML = ""+
        "<h1>This is a Login Page!</h1>" +
        "<h3>For returning users:</h3>" +
        "<p>Welcome Back! Fill in your username and password below and hit the Login Button.</p>" +
        "<h3>For new users:</h3>" +
        "<p>Hello there and welcome! I think you will like it here. Sign up is easy, just enter a unique username in the box, enter a password you would like to use and hit the Create New Account Button.</p>" +
        "<form method = 'POST'>" +
          "<p><code>Username: </code><input type=\"text\" name=\"username\"></p>" +
          "<div id='username_message'></div>" +
          "<p><code>Password: </code><input type=\"password\" name=\"password\"></p>" +
          "<div id='password_message'></div>" +
          "<p><input type=\"button\" value=\"Current User Login\" onclick=\"login()\" class=\"grnbtn\">" +
          "<p><em> OR </em></p>" +
          "<input type=\"button\" value=\"Create New Account\" onclick=\"create()\" class=\"blubtn\"></p>" +
        "</form>";
    } catch (e) {

    }

    try {
        document.getElementById('content').innerHTML = ""+
        "<h1>You're Still Logged In!</h1>" +
        "<form action=\"./logout.php\"" +
          "method = \"post\">" +
          "<p><input type=\"submit\" value=\"Logout\" name=\"logout\" class=\"redbtn\"></p>" +
        "</form>";
    } catch (e) {
        
    }

}

function login() {
    clear_messages();
    var u = document.getElementsByName('username')[0].value;
    var p = document.getElementsByName('password')[0].value;
    if (u.length === 0){
        alert("You must enter a username.")
    } else if (p.length === 0){
        alert("You must enter a password.")
    } else {
        create_request(u, p, 0);
    }
}

function create() {
    clear_messages();
    var u = document.getElementsByName('username')[0].value;
    var p = document.getElementsByName('password')[0].value;
    if (u.length === 0){
        alert("You must enter a username.")
    } else if (p.length === 0){
        alert("You must enter a password.")
    } else {
        create_request(u, p, 1);
    }
}

function login_load() {
    window.location = window.location;
    try {
        document.getElementById('content').innerHTML = ""+
        "<h1>You've Logged In!</h1>" +
        "<form action=\"./logout.php\"" +
          "method = \"post\">" +
          "<p><input type=\"submit\" value=\"Logout\" name=\"logout\" class=\"redbtn\"></p>" +
        "</form>";
    } catch (e) {
        
    }

    window.location = "./video.php";

}

function clear_messages() {
    document.getElementById('username_message').innerHTML = "";
    document.getElementById('password_message').innerHTML = "";
} 