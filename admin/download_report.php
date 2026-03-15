<?php

require_once "../dompdf/autoload.inc.php";
require_once "../config/database.php";

use Dompdf\Dompdf;
use Dompdf\Options;

/* ===================== DATA ===================== */

$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_supervisors = $pdo->query("SELECT COUNT(*) FROM users WHERE role='supervisor'")->fetchColumn();

$total_projects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$approved_projects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='approved'")->fetchColumn();
$pending_projects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='pending'")->fetchColumn();
$rejected_projects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='rejected'")->fetchColumn();

$total_defense = $pdo->query("SELECT COUNT(*) FROM defense_schedule")->fetchColumn();

$date = date("F d, Y");

/* ===================== LOGO PATH ===================== */

$logo_path = realpath(__DIR__ . '/../assets/logo.jpg');

if ($logo_path) {
    $logo_path = 'file:///' . str_replace('\\', '/', $logo_path);
}

/* ===================== HTML REPORT ===================== */

$html = "
<html>
<head>

<style>

body{
font-family: DejaVu Sans, sans-serif;
margin:40px;
}

.header{
text-align:center;
margin-bottom:30px;
}

.logo{
width:80px;
margin-bottom:10px;
}

.title{
font-size:26px;
font-weight:bold;
}

.subtitle{
font-size:15px;
color:#555;
margin-bottom:10px;
}

.date{
font-size:12px;
color:#777;
}

.section{
margin-top:30px;
}

h2{
color:#880E4F;
}

table{
width:100%;
border-collapse:collapse;
margin-top:15px;
}

table th{
background:#880E4F;
color:white;
padding:10px;
border:1px solid #ccc;
}

table td{
padding:10px;
border:1px solid #ccc;
}

.summary{
margin-top:10px;
line-height:1.6;
}

.footer{
position:fixed;
bottom:0;
left:0;
right:0;
text-align:center;
font-size:11px;
color:#777;
border-top:1px solid #ddd;
padding-top:5px;
}

</style>

</head>

<body>

<div class='header'>

<img src='$logo_path' class='logo'>

<div class='title'>
ABAARSO TECH UNIVERSITY
</div>

<div class='subtitle'>
Final Year Project Management System
</div>

<div class='date'>
Academic Report | Generated on $date
</div>

</div>


<div class='section'>

<h2>User Statistics</h2>

<table>

<tr>
<th>Category</th>
<th>Total</th>
</tr>

<tr>
<td>Total Users</td>
<td>$total_users</td>
</tr>

<tr>
<td>Students</td>
<td>$total_students</td>
</tr>

<tr>
<td>Supervisors</td>
<td>$total_supervisors</td>
</tr>

</table>

</div>


<div class='section'>

<h2>Project Statistics</h2>

<table>

<tr>
<th>Status</th>
<th>Total</th>
</tr>

<tr>
<td>Total Projects</td>
<td>$total_projects</td>
</tr>

<tr>
<td>Approved Projects</td>
<td>$approved_projects</td>
</tr>

<tr>
<td>Pending Projects</td>
<td>$pending_projects</td>
</tr>

<tr>
<td>Rejected Projects</td>
<td>$rejected_projects</td>
</tr>

</table>

</div>


<div class='section'>

<h2>Defense Schedule</h2>

<table>

<tr>
<th>Description</th>
<th>Total</th>
</tr>

<tr>
<td>Scheduled Defense Presentations</td>
<td>$total_defense</td>
</tr>

</table>

</div>


<div class='section'>

<h2>System Summary</h2>

<p class='summary'>
This report provides an overview of the Final Year Project Management System
used by Abaarso Tech University. The system manages students, supervisors,
and project evaluations while monitoring project progress, approval status,
and defense scheduling.
</p>

</div>


<div class='footer'>
Abaarso Tech University | Final Year Project System | $date
</div>

</body>
</html>
";

/* ===================== DOMPDF SETTINGS ===================== */

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream("Abaarso_Tech_University_FYP_Report.pdf", ["Attachment" => true]);

?>