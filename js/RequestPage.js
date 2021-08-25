function removeRequest(ID){
  $.ajax({
     url: "./request/delete",
     type: "POST",
     data:{requestID: ID},
     dataType: "json",
     success:function(res){
       console.log(res);
       var canRemove = res["data"]["Status"];
       if(canRemove){
         var ID = res["data"]["ID"];
         document.getElementById(ID).remove();
       }
     },
     error:function(res){
       console.log(res);
     }
   });
}
