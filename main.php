<?php // index.php ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Welcome to Work Pause</title>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@1,400;1,600&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      font-family: 'Josefin Sans', sans-serif;
      background: linear-gradient(135deg, #ffd1dc, #a7c7e7, #f4f7fb);
      background-size: 400% 400%;
      animation: animatedBG 15s ease infinite;
      color: #333;
      overflow: hidden;
    }

    @keyframes animatedBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .bubbles {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      z-index: 0;
      overflow: hidden;
    }

    .bubble {
      position: absolute;
      bottom: -150px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      animation: float 10s infinite ease-in;
    }

    .bubble:nth-child(1) { width: 90px; height: 90px; left: 15%; animation-delay: 0s; }
    .bubble:nth-child(2) { width: 120px; height: 120px; left: 35%; animation-delay: 2s; }
    .bubble:nth-child(3) { width: 60px; height: 60px; left: 60%; animation-delay: 4s; }
    .bubble:nth-child(4) { width: 100px; height: 100px; left: 80%; animation-delay: 1s; }
    .bubble:nth-child(5) { width: 80px; height: 80px; left: 50%; animation-delay: 3s; }

    @keyframes float {
      0% { transform: translateY(0); opacity: 0.4; }
      100% { transform: translateY(-1200px); opacity: 0; }
    }

    .container {
      position: relative;
      z-index: 2;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .glass-box {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(15px);
      border-radius: 25px;
      padding: 50px;
      text-align: center;
      box-shadow: 0 8px 32px rgba(0,0,0,0.3);
      max-width: 650px;
      width: 90%;
      animation: fadeIn 2s ease;
    }

    .glass-box h1 {
      font-family: 'Playfair Display', serif;
      font-size: 3.5em;
      font-style: italic;
      margin-bottom: 20px;
      text-shadow: 1px 1px 8px rgba(0,0,0,0.2);
    }

    .glass-box p {
      font-size: 1.3em;
      font-style: italic;
      line-height: 1.7;
      margin-bottom: 35px;
    }

    .cta-button {
      background: linear-gradient(90deg, #ff758c, #ff7eb3);
      padding: 15px 42px;
      border: none;
      color: #fff;
      font-size: 1.2em;
      font-style: italic;
      font-weight: 600;
      border-radius: 40px;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.4s ease;
      box-shadow: 0 0 15px rgba(255, 118, 165, 0.4);
    }

    .cta-button:hover {
      transform: scale(1.05);
      box-shadow: 0 0 25px rgba(255, 118, 165, 0.8);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to   { opacity: 1; transform: scale(1); }
    }

    @media (max-width: 600px) {
      .glass-box h1 { font-size: 2.2em; }
      .glass-box p { font-size: 1em; }
    }
  </style>
</head>
<body>

  <div class="bubbles">
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
  </div>

  <div class="container">
    <div class="glass-box">
      <h1>Welcome to Work Pause</h1>
      <p>
        <?php
        echo "Empowering your balance between work and life.";
        echo "<br>";
        echo "Today is <strong>" . date("l, F j, Y") . "</strong>";
        ?>
      </p>
      <a class="cta-button" href="login.php">Enter</a>
    </div>
  </div>

</body>
</html>