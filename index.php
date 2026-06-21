<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The Library - May you find your book</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background-color: #0d131a;
            background-image: url('sdg.png'); 
            
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .landing-title {
            color: var(--border-gold, #c19a5b);
            font-size: 3rem;
            letter-spacing: 2px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
        }

        .landing-subtitle {
            color: var(--text-muted, #88929b);
            font-size: 1.2rem;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <h1 class="landing-title">MAY YOU FIND YOUR BOOK IN THIS PLACE.</h1>
    
    <p style="color : white ";class="landing-subtitle">Welcome to the Library. Step forward, Guest.</p>
    
    <a href="login.php" class="btn" style="text-decoration: none; padding: 15px 30px; font-size: 1.1rem; border: 1px solid var(--border-gold, #c19a5b); color: var(--border-gold, #c19a5b); background: rgba(0,0,0,0.5);">
        ENTER DASHBOARD
    </a>

</body>
</html>