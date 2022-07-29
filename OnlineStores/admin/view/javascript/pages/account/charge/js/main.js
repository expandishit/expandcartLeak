$(function() {
  // Only for example - catch form data when the form is submitted
  $("#example-form").submit(function(e) {
    // Stop form submission
    e.preventDefault();

    // Read form data
    var data = $("#example-form :input").serializeArray();

    var displayText = "";

    // For each input field...
    for (var i = 0; i < data.length; i++) {
      var fieldData = data[i];
      var name = fieldData["name"];
      var value = fieldData["value"];

      displayText += name + " : " + value + "\n";
    }

    alert(displayText);
  });
});
