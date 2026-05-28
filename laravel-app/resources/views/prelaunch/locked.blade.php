<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Coming Soon | NaijaBuilders</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #172033;
            --muted: #5f697a;
            --line: #d9dee8;
            --brand: #0e7a52;
            --accent: #f4b63f;
            --paper: #ffffff;
            --wash: #f3f6f9;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 32px 20px;
            background:
                linear-gradient(135deg, rgba(14, 122, 82, 0.12), transparent 38%),
                linear-gradient(315deg, rgba(244, 182, 63, 0.16), transparent 42%),
                var(--wash);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }

        main {
            width: min(100%, 620px);
            padding: 36px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--paper);
            box-shadow: 0 20px 60px rgba(23, 32, 51, 0.12);
        }

        .brand {
            margin: 0 0 28px;
            color: var(--brand);
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0;
        }

        h1 {
            margin: 0;
            font-size: clamp(36px, 7vw, 58px);
            line-height: 1;
            letter-spacing: 0;
        }

        p {
            margin: 18px 0 0;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.55;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 28px;
            color: var(--ink);
            font-size: 14px;
            font-weight: 700;
        }

        .status::before {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--accent);
            content: "";
        }
    </style>
</head>
<body>
    <main>
        <p class="brand">NaijaBuilders</p>
        <h1>Coming Soon</h1>
        <p>The platform is being tested privately before launch. Public access is closed for now.</p>
        <span class="status">Prelaunch testing in progress</span>
    </main>
</body>
</html>
