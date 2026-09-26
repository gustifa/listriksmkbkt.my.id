html
<!DOCTYPE html>
<html>
<head>
    <title>ID Card</title>
    <style>
        body { margin: 0; padding: 0; font-family: sans-serif; }
        .container { width: 100%; height: 100%; position: relative; }
        .left { float: left; width: 35%; text-align: center; padding-top: 10px; }
        .right { float: left; width: 65%; padding-top: 15px; }
        .qr { width: 80px; height: 80px; }
        .header { font-size: 10px; color: blue; font-weight: bold; margin-bottom: 5px; }
        .name { font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .nis { font-size: 14px; font-weight: bold; color: #555; }
        .class { background: #333; color: #fff; padding: 2px 5px; font-size: 8px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(100)->generate($student->nis)) }}" class="qr">
        </div>
        <div class="right">
            <div class="header">KARTU PELAJAR</div>
            <div class="name">{{ $student->name }}</div>
            <div class="nis">{{ $student->nis }}</div>
            <span class="class">{{ $student->classroom->name ?? '-' }}</span>
        </div>
    </div>
</body>
</html>
