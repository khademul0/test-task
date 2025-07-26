jQuery(document).ready(function () {
    $('#datatables').DataTable();

    $("#create-form").on('submit', function (event) {
        event.preventDefault();

        // Show loader popup
        $(".loader-overlay").css("display", "flex");

        // Create FormData to handle file inputs
        var formData = new FormData(this);

        const site_url ="http://localhost/task-project/";

        $.ajax({
            url: site_url + 'admin/inc/action.php',
 // ✅ Replace with your actual backend handler if different
            method: 'POST',              // ✅ Fixed typo from 'mathod' to 'method'
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                // Hide loader on success
                $(".loader-overlay").hide();

                // Handle response
                console.log(response);

                // Optional: reset the form
                $("#create-form")[0].reset();
            },
            error: function (xhr, status, error) {
                $(".loader-overlay").hide(); // Hide loader on error
                console.error("AJAX Error:", error);
                alert("Submission failed. Please try again.");
            }
        });
    });
});
