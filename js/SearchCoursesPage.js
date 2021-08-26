function toggleCourseToDegree(courseID) {
    $.ajax({
        url: "/UniShare/toggleCourseToDegree",
        type: "POST",
        data: {courseID: courseID},
        dataType: "json",
        success: function (res) {
            const status = res["data"]["Status"];
            if (status === 'Inserted') {
                document.getElementById(courseID).innerText = "REMOVE from degree";
                document.getElementById(courseID).classList.remove("button-style-3");
                document.getElementById(courseID).classList.add("button-style-2");
            }
            if (status === 'Deleted') {
                document.getElementById(courseID).innerText = "ADD to degree";
                document.getElementById(courseID).classList.remove("button-style-2");
                document.getElementById(courseID).classList.add("button-style-3");
            }
        },
        error: function (xrs, res) {
            console.log(res);
            if (xrs.status === 500) {
                alert('Add a degree to put this course into! Profile->Degrees then go to settings->active degree');
            }
        }
    });
}
