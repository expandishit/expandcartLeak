"use strict"
var loadJS = function(url, implementationCode, location){
    //url is URL of external file, implementationCode is the code
    //to be called from the file, location is the location to
    //insert the <script> element

    var scriptTag = document.createElement('script');
    scriptTag.src = url;

    scriptTag.onload = implementationCode;
    scriptTag.onreadystatechange = implementationCode;

    location.appendChild(scriptTag);
};
var afterLoadCallback = function(){
    var file = location.pathname.split( "/" ).pop();

    var link = document.createElement( "link" );
    link.href = file.substr( 0, file.lastIndexOf( "." ) ) + ".css";
    link.type = "text/css";
    link.rel = "stylesheet";
    link.media = "screen,print";

    document.getElementsByTagName( "head" )[0].appendChild( link );
}
loadJS('yourcode.js', afterLoadCallback, document.head);