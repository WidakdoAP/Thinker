<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="Landing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Thinker</title>
</head>
<body>
    <header class="navbar">
      <div class="navbar-left">
        <img src="../asset/3.png" class="navbar-logo">
        <img src="../asset/2.png" class="navbar-logo">
      </div>
    </header>
    
    <div class="container">
        <nav class="sidebar">
        <ul>
          <li><i class="fas fa-house"></i><a href="../dashboard/Dashboard.php">Home</a></li>
          <li><i class="fas fa-circle-user"></i><a href="../user/User.php">User</a></li>
          <li><i class="fas fa-question-circle"></i><a href="../question/Question.php">Question</a></li>
          <li><i class="fas fa-key"></i><a href="../keyword/Keyword.php">Keyword</a></li>
          <li><i class="fas fa-triangle-exclamation"></i><a href="../report/Report.php">Report</a><li>
        </ul>
        </nav>

        <main class="content">
          <div class="content-left">
            <h1>Hello World</h1>
            <p>Every Programmer has a place.</p><br>
            <div class="button-group">
                <button type="button" onclick="window.location.href='../login/Login.php'">Login</button>
                <button type="button" onclick="window.location.href='../register/Register.php'">Register</button>
            </div>
          </div>

          <div class="content-right">
            <img src="../asset/favicon.ico" class="content-logo">
          </div> 
        </main>
    </div>
</body>
</html>