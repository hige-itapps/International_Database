<?php
/*This file serves as the project's RESTful API. */
if (array_key_exists('create_profile', $_GET)) {
    echo "Creating Profile!";
}
else{
    echo json_encode("No function called");
}
?>