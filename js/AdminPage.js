window.onload = function () {  
  //Go to courses tab by default.
  openTab(event, 'Courses');
}

function openTab(evt, tabName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("profile-tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
}

function denyRequest(ID){
  $.ajax({
     url: "/UniShare/admin/course/deny",
     type: "POST",
     data:{requestID: ID},
     dataType: "json",
     success:function(res){
       console.log(res);
       var canRemove = res["data"]["Status"];
       if(canRemove){
         var ID = res["data"]["ID"];
         document.getElementById("request-" + ID).remove();
         alert('Denied');
       }
     },
     error:function(res){
       console.log(res);
     }
   });
}

function approveRequest(ID){
  $.ajax({
     url: "/UniShare/admin/course/approve",
     type: "POST",
     data:{requestID: ID},
     dataType: "json",
     success:function(res){
       console.log(res);
       var canRemove = res["data"]["Status"];
       if(canRemove){
         var ID = res["data"]["ID"];
         document.getElementById("request-" + ID).remove();
         alert('Approved');
       }
     },
     error:function(res){
       console.log(res);
     }
   });
}