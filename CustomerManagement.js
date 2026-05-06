function blockUser(id) {
    if(confirm("Kabayan, sigurado ka bang i-blo-block ang customer na ito?")) {
        // Maaari kang gumamit ng fetch API dito para i-update ang DB
        console.log("Blocking user with ID: " + id);
        alert("User successfully blocked!");
    }
}

function deleteUser(id) {
    if(confirm("BABALA: Ang pag-delete ay permanent. Ipagpatuloy?")) {
        // Redirect sa processing script
        window.location.href = "process_customer.php?delete_id=" + id;
    }
}