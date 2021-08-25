$( document ).ready(function(){
  $.ajax ( {
      url: "/UniShare/review/get",
      type: 'GET',
      data:{courseID:  document.getElementById('courseID').value },
      dataType: "json",
      success: function ( res ){
        console.log(res);

        var text = res['data']['result']['text'];
        var difficulty = res['data']['result']['difficulty'];
        var environment = res['data']['result']['environment'];
        var fulfilling = res['data']['result']['fulfilling'];
        var grading = res['data']['result']['grading'];
        var litterature = res['data']['result']['litterature'];
        var overall = res['data']['result']['overall'];

        document.getElementById("text").value = text;
        document.getElementById("difficulty").value = difficulty;
        document.getElementById("environment").value = environment;
        document.getElementById("fulfilling").value = fulfilling;
        document.getElementById("grading").value = grading;
        document.getElementById("litterature").value = litterature;
        document.getElementById("overall").value = overall;
      },
      error: function (xhr, res ){
        console.log(res);
        if(xhr.status == 404){
          alert('No matching project ID!');
        }
      }
  });
});