<?php
// Small Core PHP demo page for CI/CD verification.
$now = gmdate('Y-m-d H:i:s') . ' UTC';
$revision = 'not available';
// Do not execute shell commands from HTTP requests. Deployment is handled separately.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PHP CI/CD Demo | GitHub + EC2</title>
  <style>
    :root { color-scheme: dark; font-family: system-ui, -apple-system, Segoe UI, sans-serif; }
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: radial-gradient(circle at top left, #1d293b 0, #0b101a 55%); color: #f4f7fc; }
    .card { max-width: 680px; width: 100%; padding: 42px; border-radius: 24px; background: #172131; border: 1px solid #334155; box-shadow: 0 22px 80px #0006; }
    .pill { display: inline-flex; gap: 8px; align-items: center; font-size: 13px; padding: 8px 12px; border-radius: 999px; background: #1c4633; color: #b5f3c5; }
    .dot { background: #4ade80; width: 8px; height: 8px; border-radius: 50%; }
    h1 { font-size: clamp(2rem, 5vw, 3.1rem); letter-spacing: -0.04em; margin: 24px 0 12px; line-height: 1.1; }
    p { color: #b5c3d6; line-height: 1.6; }
    .steps { display: flex; flex-wrap: wrap; gap: 10px; margin: 26px 0; }
    .steps span { background: #243247; border: 1px solid #364760; border-radius: 9px; padding: 9px 13px; font-size: 13px; }
    .meta { border-top: 1px solid #324056; margin-top: 28px; padding-top: 20px; font-size: 14px; color: #91a3bc; }
    strong { color: white; }
  </style>
</head>
<body>
  <main class="card">
    <div class="pill"><span class="dot"></span> PHP application running</div>
    <h1>Automated Deployment Demo</h1>
    <p>This Core PHP website is deployed to an Amazon Linux EC2 instance using GitHub Actions, SSH, Apache, and a server-side <strong>deploy.sh</strong> script.</p>
    <div class="steps"><span>GitHub main</span><span>→ GitHub Actions Runner</span><span>→ SSH</span><span>→ AWS EC2</span></div>
    <p>Edit this heading, commit the change, and push to <strong>main</strong>. If the pipeline succeeds, refresh this page to verify automatic deployment.</p>
    <div class="meta">Rendered at <?= htmlspecialchars($now, ENT_QUOTES, 'UTF-8') ?></div>
  </main>
</body>
</html>
