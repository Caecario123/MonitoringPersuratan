<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show PDF</title>
</head>
<body>
    <embed src="{{ $file }}" type="{{ $ext }}" width="100%" height="600px" />
    <!-- Atau menggunakan tag <iframe> -->
    <!-- <iframe src="{{ $file }}" style="width:100%; height:600px;" frameborder="0"></iframe> -->
</body>
</html>
