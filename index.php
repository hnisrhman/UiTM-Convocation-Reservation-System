<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>UiTM Convocation System</title>
<style>
.header-content {
  display: flex;
  align-items: center;
}

.logo {
  height: 100px;
  margin-right: 150px;
}

.header-text h1 {
  margin: 0;
  font-size: 24px;
  margin-right: 90px;
}

.header-text p {
  margin: 0;
  font-size: 14px;
  color: #FFFFFF;
  margin-right: 102px;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

header {
    background-color: #002147;
    color: #fff;
    padding: 20px;
    text-align: center;
}

nav {
    margin-top: 10px;
}

nav a {
    color: #ffc107;
    margin: 0 15px;
    text-decoration: none;
    font-weight: bold;
}

nav a:hover {
    color: #fff;
}

h2 {
    color: #002147;
    margin-left: 20px;
}

form {
    background: #fff;
    padding: 20px;
    margin: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

form label {
    display: inline-block;
    width: 160px;
    font-weight: bold;
    margin-bottom: 10px;
}

form input, form select, form button {
    padding: 8px;
    width: 250px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

form button {
    width: auto;
    background-color: #002147;
    color: #fff;
    border: none;
    cursor: pointer;
}

form button:hover {
    background-color: #ffc107;
    color: #002147;
}

table {
    border-collapse: collapse;
    width: 95%;
    margin: 20px auto;
    background: #fff;
    box-shadow: 0 0 8px rgba(0,0,0,0.05);
}

table th, table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

table th {
    background-color: #002147;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.button-link {
    display: inline-block;
    padding: 8px 12px;
    background-color: #002147;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    margin: 5px;
}

.button-link:hover {
    background-color: #ffc107;
    color: #002147;
}

.message {
    padding: 15px;
    margin: 20px;
    background: #e7f3fe;
    border-left: 5px solid #2196F3;
    color: #333;
    border-radius: 4px;
}

img {
    border-radius: 8px;
}

/* Dropdown container */
.dropdown {
    display: inline-block;
    position: relative;
}

/* Dropdown button */
.dropbtn {
    background-color: #002147;
    color: #ffc107;
    padding: 8px 16px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}

/* Dropdown content (hidden by default) */
.dropdown-content {
    display: none;
    position: absolute;
    background-color: #fff;
    min-width: 160px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    z-index: 10;
    margin-top: 10px;
    text-align: left;
}

/* Dropdown links */
.dropdown-content a {
    color: #002147;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    font-weight: bold;
}

.dropdown-content a:hover {
    background-color: #ffc107;
    color: #002147;
}

.background-blur {
  background: url('images/bg_convo.jpg') no-repeat center center fixed;
  background-size: cover;
  filter: blur(1px);
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: -1;
}

</style>

</head>
<body>
    
<header>
    <div class="header-content">
        <img src="images/logo_uitm.png" alt="UiTM Logo" class="logo">
        <div class="header-text">
            <h1>UiTM Convocation Reservation System</h1>
            <p>Student Records and Convocation Division (BRPK)</p>
        </div>
        <div>
            <nav>
                <a href="index.php">Home</a>
                <a href="about.php">About Us</a>
                <div class="dropdown">
                <button onclick="toggleDropdown()" class="dropbtn">Login ▼</button>
                <div id="loginDropdown" class="dropdown-content">
                    <a href="login_user.php">Login as User</a>
                    <a href="login_admin.php">Login as Admin</a>
                </div>
                </div>
                <a href="register.php" class="button-link">Register</a>
            </nav> 
        </div>
    </div>
</header>


<script>
function toggleDropdown() {
    var dropdown = document.getElementById("loginDropdown");
    dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
}

// Close the dropdown if clicked outside
window.onclick = function(event) {
    if (!event.target.matches('.dropbtn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.style.display === "block") {
                openDropdown.style.display = "none";
            }
        }
    }
}
</script>

<div class="background-blur"></div>

<h2 style='text-align:center;'>About UiTM Convocation Attire</h2>
<p style='text-align:center;'>This system allows graduates to book their official UiTM convocation attire including robe, hood, mortar board or bonnet, with optional full package. Reservations must be made before the collection date and payment confirmed online. Attire follows UiTM's official convocation guidelines.</p>
<h3><strong>Package Includes:</strong></h3>
<ul>
<li>Diploma, Degree, Master & PhD Robes</li>
<li>Mortar Board or Bonnet</li>
<li>Hood (based on program code)</li>
</ul>


